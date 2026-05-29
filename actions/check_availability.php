<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$field = $input['field'] ?? '';
$value = trim($input['value'] ?? '');

if (empty($field) || empty($value)) {
    echo json_encode(['available' => true]);
    exit();
}

$allowedFields = ['id_number', 'username', 'email'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['available' => true]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $field = ?");
    $stmt->execute([$value]);
    $count = $stmt->fetchColumn();

    echo json_encode(['available' => $count == 0]);
} catch (Exception $e) {
    echo json_encode(['available' => true, 'error' => $e->getMessage()]);
}
?>
