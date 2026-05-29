<?php
require_once dirname(__FILE__) . '/includes/db.php';
$stmt = $conn->query("SELECT id, username, id_number, status, email_verified_at FROM users WHERE username LIKE '%Laurente%' OR last_name LIKE '%Laurente%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
