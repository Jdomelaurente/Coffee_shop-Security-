<?php
require_once 'includes/db.php';

echo "--- Searching for User 'Doms' ---\n";
$stmt = $conn->prepare("SELECT id, first_name, last_name, username, role FROM users WHERE first_name ILIKE '%Doms%' OR last_name ILIKE '%Doms%' OR username ILIKE '%Doms%'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

echo "\n--- Recent Logs for 'Doms' ---\n";
$stmt = $conn->prepare("SELECT user_name, user_role, action, created_at FROM activity_logs WHERE user_name ILIKE '%Doms%' OR user_id ILIKE '%Doms%' OR details ILIKE '%Doms%' ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($logs);

echo "\n--- System Time Check ---\n";
$stmt = $conn->query("SELECT NOW() as sys_time");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
