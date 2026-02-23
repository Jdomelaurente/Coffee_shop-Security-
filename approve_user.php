<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';
require_once 'log_functions.php';

// Security Check: Only allow logged-in admins
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

try {
    $id = $_POST['id'] ?? '';
    $action = $_POST['action'] ?? ''; // 'approve' or 'reject'

    if (empty($id) || !in_array($action, ['approve', 'reject'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        // Log activity
        logActivity("Approved user registration (UID: $id)", "Admin Action");
        
        echo json_encode(['status' => 'success', 'message' => 'User approved successfully!']);
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id AND status = 'pending'");
        $stmt->execute(['id' => $id]);
        
        // Log activity
        logActivity("Rejected and deleted pending user (UID: $id)", "Admin Action");
        
        echo json_encode(['status' => 'success', 'message' => 'User registration rejected and removed.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
