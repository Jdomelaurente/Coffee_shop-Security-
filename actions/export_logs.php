<?php
session_start();
require_once '../includes/log_functions.php';

// Security check
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
    header("Location: index.php");
    exit();
}

// Get filters
$filters = [];
if (isset($_GET['module']) && !empty($_GET['module'])) {
    $filters['module'] = $_GET['module'];
}
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $filters['date_from'] = $_GET['date'] . ' 00:00:00';
    $filters['date_to'] = $_GET['date'] . ' 23:59:59';
}

// Export logs
exportLogsToCSV($filters);
