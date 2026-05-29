<?php
require_once dirname(__FILE__) . '/includes/db.php';
try {
    $stmt = $conn->query("SHOW search_path");
    $path = $stmt->fetchColumn();
    echo "Search Path: $path\n";
    $stmt = $conn->query("SELECT current_database()");
    $db = $stmt->fetchColumn();
    echo "Current Database: $db\n";
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    echo "Tables Found:\n";
    while ($row = $stmt->fetchColumn()) {
        echo "- $row\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
