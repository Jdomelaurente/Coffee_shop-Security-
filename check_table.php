<?php
require 'includes/db.php';
$stmt = $conn->query("DESCRIBE activity_logs");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
unlink(__FILE__);
?>
