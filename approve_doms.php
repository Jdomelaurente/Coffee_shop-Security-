<?php
require_once dirname(__FILE__) . '/includes/db.php';
$stmt = $conn->prepare("UPDATE users SET status = 'approved', email_verified_at = NOW() WHERE username = 'Doms'");
$stmt->execute();
echo "Approved " . $stmt->rowCount() . " users named Doms.\n";
?>
