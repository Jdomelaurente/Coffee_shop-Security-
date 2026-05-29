<?php
require_once 'includes/db.php';
try {
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS requires_password_change BOOLEAN DEFAULT FALSE");
    echo "Database table 'users' updated with requires_password_change column.\n";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>
