<?php
$host = "localhost";       // Usually localhost
$port = "5432";            // Default PostgreSQL port
$dbname = "coffee_shop";   
$user = "postgres";        // Your PostgreSQL username
$password = "postgres";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
