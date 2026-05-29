<?php
$host = "localhost";       // Usually localhost
$port = "3306";            // Default MySQL port
$dbname = "coffee_shop";   
$user = "root";            // Default MySQL username
$password = "";            // Default MySQL password (blank locally for XAMPP/WampServer)

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
