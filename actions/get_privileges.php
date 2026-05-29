<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$admin_id = $_GET['admin_id'] ?? null;

if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Missing admin ID.']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM admin_privileges WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $privs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$privs) {
        // Return defaults if not found
        $privs = [
            'can_add_user' => false,
            'can_block_user' => false,
            'can_view_users' => false
        ];
    }

    echo json_encode(['success' => true, 'privileges' => $privs]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
