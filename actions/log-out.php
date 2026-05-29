<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/log_functions.php';

// Capture user data BEFORE destroying session
$user_id  = $_SESSION['user_id'] ?? null;
$user_data = [
    'username' => $_SESSION['username'] ?? 'unknown',
    'user_name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Unknown User',
    'role' => $_SESSION['role'] ?? 'unknown'
];

// Record last_logout timestamp
if ($user_id) {
    try {
        $stmt = $conn->prepare("UPDATE users SET last_logout = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user_id]);

        // Close the specific session history record
        if (isset($_SESSION['current_session_id'])) {
            $sess_upd = $conn->prepare("UPDATE user_sessions SET logout_at = NOW(), is_active = FALSE WHERE id = :sid");
            $sess_upd->execute(['sid' => $_SESSION['current_session_id']]);
        }
    } catch (Exception $e) { /* silently fail */ }
}

// Log the logout activity using captured data
if (!empty($user_data['username'])) {
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['user_name'] = $user_data['user_name'];
    $_SESSION['role'] = $user_data['role'];
    
    logActivity(
        'User Logged Out',
        'Authentication',
        [
            'username' => $user_data['username'],
            'user_name' => $user_data['user_name'],
            'role' => $user_data['role']
        ]
    );
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to index.php
header("Location: ../index.php");
exit;
?>