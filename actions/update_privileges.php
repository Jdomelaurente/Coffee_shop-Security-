<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/log_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$admin_id = $_POST['admin_id'] ?? null;
$can_add_user = isset($_POST['can_add_user']) ? 'TRUE' : 'FALSE';
$can_block_user = isset($_POST['can_block_user']) ? 'TRUE' : 'FALSE';
$can_view_users = isset($_POST['can_view_users']) ? 'TRUE' : 'FALSE';


if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Missing admin ID.']);
    exit();
}

try {
    // Check if user is actually an admin
    $stmt = $conn->prepare("SELECT role, first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $user = $stmt->fetch();

    if (!$user || $user['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'User is not an admin.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO admin_privileges (admin_id, can_add_user, can_block_user, can_view_users) 
                            VALUES (?, $can_add_user, $can_block_user, $can_view_users)
                            ON CONFLICT (admin_id) DO UPDATE 
                            SET can_add_user = EXCLUDED.can_add_user, 
                                can_block_user = EXCLUDED.can_block_user, 
                                can_view_users = EXCLUDED.can_view_users");
    $stmt->execute([$admin_id]);

    $adminName = $user['first_name'] . ' ' . $user['last_name'];
    logActivity(
        "Security: Updated admin permissions for $adminName",
        "Privileges",
        [
            'admin_id' => $admin_id,
            'target_name' => $adminName,
            'add_users' => ($can_add_user === 'TRUE' ? 'Enabled' : 'Disabled'),
            'block_users' => ($can_block_user === 'TRUE' ? 'Enabled' : 'Disabled'),
            'view_users' => ($can_view_users === 'TRUE' ? 'Enabled' : 'Disabled')
        ]
    );

    echo json_encode(['success' => true, 'message' => 'Privileges updated successfully.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
