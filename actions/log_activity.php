<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/log_functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$action = $input['action'] ?? 'Unknown Action';
$module = $input['module'] ?? 'System';
$details = $input['details'] ?? [];

if (logActivity($action, $module, $details)) {
    echo json_encode(['status' => 'success', 'message' => 'Activity logged']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to log activity']);
}
