<?php
require_once 'db.php';

try {
    $stmt = $conn->prepare("
        INSERT INTO users (id_number, first_name, last_name, email, password)
        VALUES ('TEST001', 'Test', 'User', 'test@test.com', '123')
    ");

    $stmt->execute();
    echo "INSERT OK";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
