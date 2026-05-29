<?php
require_once dirname(__FILE__) . '/includes/db.php';
$stmt = $conn->query("SELECT id, username, first_name, last_name, status, email_verified_at, requires_password_change FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "All Users:\n";
print_r($users);
?>
