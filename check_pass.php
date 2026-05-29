<?php
require_once dirname(__FILE__) . '/includes/db.php';
$password_to_check = '@Laurente123';
$stmt = $conn->prepare("SELECT password FROM users WHERE username = 'Doms'");
$stmt->execute();
$hash = $stmt->fetchColumn();

if ($hash) {
    if (password_verify($password_to_check, $hash)) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
        echo "Hash in DB: $hash\n";
    }
} else {
    echo "User not found.\n";
}
?>
