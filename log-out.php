<?php
session_start();
require_once 'log_functions.php';

// Capture user data BEFORE destroying session
$user_data = [
    'username' => $_SESSION['username'] ?? 'unknown',
    'user_name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Unknown User',
    'role' => $_SESSION['role'] ?? 'unknown'
];

// Log the logout activity using captured data
if (!empty($user_data['username'])) {
    // Temporarily set session variables for logging
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['user_name'] = $user_data['user_name'];
    $_SESSION['role'] = $user_data['role'];
    
    logActivity(
        'User logged out',
        'Authentication',
        [
            'username' => $user_data['username'],
            'user_name' => $user_data['user_name']
        ]
    );
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to index.php
header("Location: index.php");
exit;
?>