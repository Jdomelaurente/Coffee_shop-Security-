<?php
require_once '../includes/db.php';

try {
    // Add last_login column
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE");
    // Add last_login column
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL");
    // Add last_logout column
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_logout TIMESTAMP NULL");
    echo "Columns added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
