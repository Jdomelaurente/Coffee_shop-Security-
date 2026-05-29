<?php
require_once dirname(__FILE__) . '/includes/db.php';
try {
    $stmt = $conn->query("SELECT id, username, first_name, role, status FROM users");
    echo "Current Users:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Username: {$row['username']} | Name: {$row['first_name']} | Role: {$row['role']} | Status: {$row['status']}\n";
    }

    // Resetting passwords to a default one
    $defaultPass = 'Password!123';
    $hashed = password_hash($defaultPass, PASSWORD_BCRYPT);
    $conn->query("UPDATE users SET password = '$hashed', status = 'active'");
    echo "\nAll user passwords have been temporarily reset to: $defaultPass\n";
    echo "All user accounts have been set to 'active' status just in case.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
