<?php
require_once 'includes/db.php';
try {
    $stmt = $conn->prepare("UPDATE users SET email_verified_at = NOW() WHERE email_verified_at IS NULL");
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "Successfully 'verified' $count existing users to prevent lockouts.\n";
} catch (Exception $e) {
    echo "Error updating existing users: " . $e->getMessage() . "\n";
}
?>
