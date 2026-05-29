<?php
require_once dirname(__FILE__) . '/includes/db.php';
try {
    $stmt = $conn->prepare("UPDATE users SET email_verified_at = NOW() WHERE status = 'approved' AND email_verified_at IS NULL");
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "Fixed $count users who were approved but not verified.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
