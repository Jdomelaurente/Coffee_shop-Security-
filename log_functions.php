<?php
// log_functions.php
require_once 'db.php';

/**
 * Helper function to get user name from database
 * @param string $user_id The user ID number
 * @return string The user's full name or empty string if not found
 */
function getUserNameFromDatabase($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id_number = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return trim($user['first_name'] . ' ' . $user['last_name']);
        }
    } catch (Exception $e) {
        error_log("Failed to fetch user name: " . $e->getMessage());
    }
    return '';
}

/**
 * Log user activity
 * @param string $action The action performed
 * @param string $module The module where action occurred
 * @param array $details Additional details (optional)
 * @return bool Success or failure
 */
function logActivity($action, $module, $details = []) {
    global $conn;
    
    try {
        // Get current user info from session with better handling
        $user_id = $_SESSION['username'] ?? 'system';
        
        // Try to get user_name from various sources
        if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
            $user_name = $_SESSION['user_name'];
        } elseif (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            // If we only have username, try to fetch from database
            $user_name = getUserNameFromDatabase($_SESSION['username']);
            if (empty($user_name)) {
                $user_name = $_SESSION['username']; // Fallback to username
            }
        } else {
            $user_name = 'System';
        }
        
        $user_role = $_SESSION['role'] ?? 'system';
        // Normalize: 'staff' is treated as 'user'
        if (strtolower($user_role) === 'staff') {
            $user_role = 'user';
        }
        
        // Get IP address (handles proxy forwarding)
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Add timestamp to details if not present
        if (!isset($details['timestamp'])) {
            $details['timestamp'] = date('Y-m-d H:i:s');
        }
        
        // Convert details to JSON
        $details_json = json_encode($details, JSON_UNESCAPED_UNICODE);
        
        $sql = "INSERT INTO activity_logs (
                    user_id, user_name, user_role, action, module, 
                    ip_address, user_agent, details, created_at
                ) VALUES (
                    :user_id, :user_name, :user_role, :action, :module,
                    :ip_address, :user_agent, :details, NOW()
                )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'user_name' => $user_name,
            'user_role' => $user_role,
            'action' => $action,
            'module' => $module,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'details' => $details_json
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Logging failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get activity logs with filtering
 * @param array $filters Filter criteria
 * @param int $limit Number of records to return
 * @param int $offset Offset for pagination
 * @return array Logs and total count
 */
function getActivityLogs($filters = [], $limit = 50, $offset = 0) {
    global $conn;
    
    try {
        $where_conditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $where_conditions[] = "user_id LIKE :user_id";
            $params['user_id'] = '%' . $filters['user_id'] . '%';
        }
        
        if (!empty($filters['module'])) {
            $where_conditions[] = "module = :module";
            $params['module'] = $filters['module'];
        }
        
        if (!empty($filters['action'])) {
            $where_conditions[] = "action ILIKE :action";
            $params['action'] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['user_name'])) {
            $where_conditions[] = "user_name ILIKE :user_name";
            $params['user_name'] = '%' . $filters['user_name'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $where_clause = empty($where_conditions) ? "" : "WHERE " . implode(" AND ", $where_conditions);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM activity_logs $where_clause";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();
        
        // Get paginated results
        $sql = "SELECT * FROM activity_logs 
                $where_clause 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON details for each log
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details_array'] = json_decode($log['details'], true);
            }
        }
        
        return [
            'success' => true,
            'data' => $logs,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ];
        
    } catch (PDOException $e) {
        error_log("Failed to fetch logs: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch logs',
            'data' => [],
            'total' => 0,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}

/**
 * Export logs to CSV
 * @param array $filters Filter criteria
 */
function exportLogsToCSV($filters = []) {
    global $conn;
    
    try {
        $logs = getActivityLogs($filters, 10000, 0); // Get up to 10000 logs
        
        if (!$logs['success']) {
            return false;
        }
        
        // Set filename with timestamp
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, ['Timestamp', 'User ID', 'User Name', 'Role', 'Action', 'Module', 'IP Address', 'Details']);
        
        // Add data
        foreach ($logs['data'] as $log) {
            $details = json_decode($log['details'], true);
            $details_str = is_array($details) ? json_encode($details) : ($log['details'] ?? '');
            
            fputcsv($output, [
                $log['created_at'] ?? '',
                $log['user_id'] ?? '',
                $log['user_name'] ?? '',
                $log['user_role'] ?? '',
                $log['action'] ?? '',
                $log['module'] ?? '',
                $log['ip_address'] ?? '',
                $details_str
            ]);
        }
        
        fclose($output);
        exit();
        
    } catch (Exception $e) {
        error_log("Export failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get summary statistics for logs
 * @return array Statistics
 */
function getLogStats() {
    global $conn;
    
    try {
        $stats = [];
        
        // Total logs count
        $stmt = $conn->query("SELECT COUNT(*) FROM activity_logs");
        $stats['total_logs'] = $stmt->fetchColumn();
        
        // Logs by module
        $stmt = $conn->query("
            SELECT module, COUNT(*) as count 
            FROM activity_logs 
            GROUP BY module 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $stats['by_module'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Today's logs
        $stmt = $conn->query("
            SELECT COUNT(*) 
            FROM activity_logs 
            WHERE DATE(created_at) = CURRENT_DATE
        ");
        $stats['today_logs'] = $stmt->fetchColumn();
        
        // Recent activity (last 24 hours)
        $stmt = $conn->query("
            SELECT COUNT(*) 
            FROM activity_logs 
            WHERE created_at >= NOW() - INTERVAL '24 hours'
        ");
        $stats['last_24h'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("Failed to get log stats: " . $e->getMessage());
        return [];
    }
}

/**
 * Clear old logs
 * @param int $days_old Delete logs older than this many days
 * @return int|bool Number of deleted rows or false on failure
 */
function clearOldLogs($days_old = 30) {
    global $conn;
    
    try {
        $sql = "DELETE FROM activity_logs WHERE created_at < NOW() - INTERVAL '$days_old days'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
        
    } catch (PDOException $e) {
        error_log("Failed to clear old logs: " . $e->getMessage());
        return false;
    }
}
?>