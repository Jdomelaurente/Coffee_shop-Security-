<?php
require_once dirname(__FILE__) . '/includes/db.php';
try {
    $stmt = $conn->query("SELECT id, id_number, username, role, status, email_verified_at, first_name, last_name FROM users ORDER BY id DESC LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/plain');
    echo "Recent Users:\n";
    foreach ($users as $u) {
        print_r($u);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
