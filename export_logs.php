<?php
session_start();
require_once 'log_functions.php';

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get filters
$filters = [];
if (isset($_GET['module']) && !empty($_GET['module'])) {
    $filters['module'] = $_GET['module'];
}
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $filters['date_from'] = $_GET['date'] . ' 00:00:00';
    $filters['date_to'] = $_GET['date'] . ' 23:59:59';
}

// Export logs
exportLogsToCSV($filters);