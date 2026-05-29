<?php
require_once 'includes/db.php';
try {
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token VARCHAR(255) DEFAULT NULL");
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP DEFAULT NULL");
    echo "Database table 'users' updated successfully with verification columns.\n";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>
