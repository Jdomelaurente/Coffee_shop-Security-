<?php
require_once 'includes/db.php';
try {
    $stmt = $conn->query("SELECT id, username, first_name, last_name, role FROM users ORDER BY id");
    echo "Current Users:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
