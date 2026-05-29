<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/log_functions.php';

if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'superadmin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$pendingActionId = isset($_POST['pending_action_id']) ? (int)$_POST['pending_action_id'] : 0;
$decision = $_POST['decision'] ?? '';

if ($pendingActionId <= 0 || !in_array($decision, ['approve', 'reject'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT
            pa.id,
            pa.target_user_id,
            pa.action_type,
            pa.new_data,
            pa.status,
            tu.first_name,
            tu.last_name,
            tu.role AS target_role
        FROM pending_actions pa
        LEFT JOIN users tu ON tu.id = pa.target_user_id
        WHERE pa.id = :id
    ");
    $stmt->execute(['id' => $pendingActionId]);
    $pending = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pending || ($pending['status'] ?? '') !== 'pending') {
        echo json_encode(['status' => 'error', 'message' => 'Pending action not found']);
        exit();
    }

    $targetName = trim(($pending['first_name'] ?? '') . ' ' . ($pending['last_name'] ?? ''));
    if ($targetName === '') {
        $targetName = 'Unknown';
    }

    if ($decision === 'reject') {
        $stmt = $conn->prepare("UPDATE pending_actions SET status = 'rejected' WHERE id = :id");
        $stmt->execute(['id' => $pendingActionId]);

        logActivity("Rejected pending admin action #$pendingActionId", 'User Mgmt', [
            'pending_action_id' => $pendingActionId,
            'target_user_id' => (int)$pending['target_user_id'],
            'action_type' => $pending['action_type'] ?? ''
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Pending action rejected']);
        exit();
    }

    $actionType = $pending['action_type'] ?? '';
    if ($actionType === 'delete_user') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => (int)$pending['target_user_id']]);

        logActivity("Approved delete request for user: $targetName", 'User Mgmt', [
            'pending_action_id' => $pendingActionId,
            'target_user_id' => (int)$pending['target_user_id']
        ]);
    } elseif ($actionType === 'update_role') {
        $newData = json_decode($pending['new_data'] ?? '{}', true);
        $newRole = strtolower(trim((string)($newData['new_role'] ?? '')));
        if (!in_array($newRole, ['user', 'admin', 'superadmin'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid role in pending request']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([
            'role' => $newRole,
            'id' => (int)$pending['target_user_id']
        ]);

        logActivity("Approved role update for user: $targetName", 'User Mgmt', [
            'pending_action_id' => $pendingActionId,
            'target_user_id' => (int)$pending['target_user_id'],
            'new_role' => $newRole
        ]);
    } elseif ($actionType === 'block_user' || $actionType === 'unblock_user') {
        $newStatus = ($actionType === 'block_user') ? 'blocked' : 'approved';
        $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => (int)$pending['target_user_id']]);

        logActivity("Approved " . ($actionType === 'block_user' ? "block" : "unblock") . " request for: $targetName", 'User Mgmt', [
            'pending_action_id' => $pendingActionId,
            'target_user_id' => (int)$pending['target_user_id']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unsupported pending action type']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE pending_actions SET status = 'approved' WHERE id = :id");
    $stmt->execute(['id' => $pendingActionId]);

    echo json_encode(['status' => 'success', 'message' => 'Pending action approved']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}

