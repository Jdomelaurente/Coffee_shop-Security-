<?php
// log_functions.php
require_once 'db.php';

/**
 * Helper function to get user name from database by ID or Username
 */
function getUserNameFromDatabase($identifier) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id_number = :id OR username = :uname LIMIT 1");
        $stmt->execute(['id' => $identifier, 'uname' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        }
    } catch (Exception $e) {
        error_log("Failed to fetch user name: " . $e->getMessage());
    }
    return '';
}

/**
 * Log user activity
 */
function logActivity($action, $module, $details = []) {
    global $conn;
    try {
        // ID should be id_number from session
        $user_id = $_SESSION['username'] ?? 'system';
        
        // Try to get user_name from session or DB
        if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
            $user_name = $_SESSION['user_name'];
        } elseif (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            $db_name = getUserNameFromDatabase($_SESSION['username']);
            $user_name = !empty($db_name) ? $db_name : $_SESSION['username'];
        } else {
            $user_name = 'System';
        }
        
        $user_role = $_SESSION['role'] ?? 'system';
        
        // Get IP address
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
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
    } catch (Exception $e) {
        error_log("logActivity failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch logs with optional filtering and pagination
 */
function getActivityLogs($filters = [], $limit = 10, $offset = 0) {
    global $conn;
    try {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['module'])) {
            $where[] = "module = :module";
            $params['module'] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[] = "action LIKE :action";
            $params['action'] = "%{$filters['action']}%";
        }
        // action_keyword: a comma-separated set of LIKE patterns joined with OR
        if (!empty($filters['action_keyword'])) {
            $keywords = array_filter(array_map('trim', explode(',', $filters['action_keyword'])));
            if ($keywords) {
                $kClauses = [];
                foreach ($keywords as $i => $kw) {
                    $key = "akw_$i";
                    $kClauses[] = "action LIKE :$key";
                    $params[$key] = "%$kw%";
                }
                $where[] = '(' . implode(' OR ', $kClauses) . ')';
            }
        }
        if (!empty($filters['search'])) {
            $searchTerms = array_filter(explode(' ', trim($filters['search'])));
            $searchClauses = [];
            foreach ($searchTerms as $i => $term) {
                $key = "search_$i";
                $searchClauses[] = "(user_name LIKE :$key OR action LIKE :$key OR user_id LIKE :$key OR CAST(details AS CHAR) LIKE :$key OR module LIKE :$key)";
                $params[$key] = "%$term%";
            }
            if (!empty($searchClauses)) {
                $where[] = "(" . implode(' AND ', $searchClauses) . ")";
            }
        }
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        // user_role array filter (e.g. admins restricted from seeing superadmin logs)
        if (!empty($filters['user_role']) && is_array($filters['user_role'])) {
            $rolePlaceholders = [];
            foreach ($filters['user_role'] as $i => $role) {
                $key = "ur_$i";
                $rolePlaceholders[] = ":$key";
                $params[$key] = $role;
            }
            $where[] = 'user_role IN (' . implode(',', $rolePlaceholders) . ')';
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Count total
        $count_sql = "SELECT COUNT(*) FROM activity_logs WHERE $where_sql";
        $c_stmt = $conn->prepare($count_sql);
        $c_stmt->execute($params);
        $total = $c_stmt->fetchColumn();
        
        // Fetch data
        $sql = "SELECT id, user_id, user_name, user_role, action, module, ip_address, user_agent, details, created_at
                FROM activity_logs 
                WHERE $where_sql 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    } catch (Exception $e) {
        error_log("getActivityLogs failed: " . $e->getMessage());
        return ['data' => [], 'total' => 0];
    }
}


/**
 * Parse user agent to extract platform, browser, and icon
 */
function parseUserAgent($ua) {
    $platform = 'Unknown';
    $browser = 'Unknown';
    $icon = 'fas fa-question-circle';
    
    if (empty($ua)) return ['platform' => '-', 'browser' => '-', 'icon' => $icon];
    
    // Platform
    if (preg_match('/windows/i', $ua)) { $platform = 'Windows'; $icon = 'fab fa-windows'; }
    elseif (preg_match('/mac|os x/i', $ua)) { $platform = 'macOS'; $icon = 'fab fa-apple'; }
    elseif (preg_match('/linux/i', $ua)) { $platform = 'Linux'; $icon = 'fab fa-linux'; }
    elseif (preg_match('/android/i', $ua)) { $platform = 'Android'; $icon = 'fab fa-android'; }
    elseif (preg_match('/iphone|ipad/i', $ua)) { $platform = 'iOS'; $icon = 'fas fa-mobile-alt'; }
    
    // Browser
    if (preg_match('/chrome/i', $ua) && !preg_match('/edge/i', $ua)) { $browser = 'Chrome'; }
    elseif (preg_match('/firefox/i', $ua)) { $browser = 'Firefox'; }
    elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) { $browser = 'Safari'; }
    elseif (preg_match('/msie|trident/i', $ua)) { $browser = 'IE'; }
    elseif (preg_match('/edge/i', $ua)) { $browser = 'Edge'; }
    
    return [
        'platform' => $platform,
        'browser' => $browser,
        'icon' => $icon
    ];
}

/**
 * Helper for module badges
 */
function moduleBadge($module) {
    $m = strtolower($module);
    if (str_contains($m, 'auth') || str_contains($m, 'login')) return 'badge-transaction';
    if (str_contains($m, 'invent')) return 'badge-inventory';
    if (str_contains($m, 'user')) return 'badge-user';
    if (str_contains($m, 'sale')) return 'badge-transaction';
    return 'badge-admin';
}

/**
 * Export logs to CSV
 */
function exportLogsToCSV($filters = []) {
    global $conn;
    try {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['module'])) {
            $where[] = "module = :module";
            $params['module'] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[] = "action LIKE :action";
            $params['action'] = "%{$filters['action']}%";
        }
        if (!empty($filters['search'])) {
            $searchTerms = array_filter(explode(' ', trim($filters['search'])));
            $searchClauses = [];
            foreach ($searchTerms as $i => $term) {
                $key = "search_$i";
                $searchClauses[] = "(user_name LIKE :$key OR action LIKE :$key OR user_id LIKE :$key OR CAST(details AS CHAR) LIKE :$key OR module LIKE :$key)";
                $params[$key] = "%$term%";
            }
            if (!empty($searchClauses)) {
                $where[] = "(" . implode(' AND ', $searchClauses) . ")";
            }
        }
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $where_sql = implode(' AND ', $where);
        $sql = "SELECT created_at, user_name, user_role, action, module, ip_address, user_agent, details
                FROM activity_logs 
                WHERE $where_sql 
                ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $filename = "Activity_Logs_" . date('Y-m-d_H-i-s') . ".csv";
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        
        $output = fopen('php://output', 'w');
        // BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Timestamp', 'Full Name', 'Role', 'Action', 'Module', 'IP Address', 'User Agent', 'Details']);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['user_name'],
                strtoupper($log['user_role']),
                $log['action'],
                $log['module'],
                $log['ip_address'],
                $log['user_agent'],
                $log['details']
            ]);
        }
        fclose($output);
        exit();
    } catch (Exception $e) {
        error_log("exportLogsToCSV failed: " . $e->getMessage());
        header("Location: ../superadmin_dash.php?error=export_failed");
        exit();
    }
}
?>
