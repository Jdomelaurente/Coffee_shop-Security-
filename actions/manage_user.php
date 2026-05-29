<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/log_functions.php';

if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'] ?? '', ['admin', 'superadmin'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// If admin, check privileges for specific actions
if ($_SESSION['role'] === 'admin') {
    $action = $_POST['action'] ?? '';
    $actorId = $_SESSION['user_id'];
    
    // Strictly prevent deletion for admins
    if ($action === 'delete_user') {
        echo json_encode(["status" => "error", "message" => "Access Denied: Only the Superadmin can delete users."]);
        exit();
    }

    // Fetch Privileges
    $stmt = $conn->prepare("SELECT can_block_user, can_change_role FROM admin_privileges WHERE admin_id = ?");
    $stmt->execute([$actorId]);
    $privs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (in_array($action, ['block_user', 'unblock_user'])) {
        if (!($privs['can_block_user'] ?? false)) {
            echo json_encode(["status" => "error", "message" => "Access Denied: You do not have the privilege to block users."]);
            exit();
        }
    }

    if ($action === 'update_role') {
        if (!($privs['can_change_role'] ?? false)) {
            echo json_encode(["status" => "error", "message" => "Access Denied: You do not have the privilege to change user roles."]);
            exit();
        }
        
        // Admin specific role restrictions
        if (!in_array($newRole, ['user', 'admin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Admins can only assign User or Admin roles']);
            exit();
        }
    }
}

$actorRole = $_SESSION['role'];
$action = $_POST['action'] ?? '';
$targetUserId = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
$newRole = strtolower(trim($_POST['new_role'] ?? ''));

if (!in_array($action, ['update_role', 'delete_user', 'block_user', 'unblock_user'], true) || $targetUserId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters']);
    exit();
}

if ($action === 'update_role' && !in_array($newRole, ['user', 'admin', 'superadmin'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid target role']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, role FROM users WHERE id = :id");
    $stmt->execute(['id' => $targetUserId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target) {
        echo json_encode(['status' => 'error', 'message' => 'Target user not found']);
        exit();
    }

    $targetRole = strtolower($target['role'] ?? 'user');
    $targetName = trim(($target['first_name'] ?? '') . ' ' . ($target['last_name'] ?? ''));

    $actorDbId = $_SESSION['user_id'] ?? null;

    // Verify actor still exists in DB
    if ($actorDbId) {
        $checkActor = $conn->prepare("SELECT 1 FROM users WHERE id = :id");
        $checkActor->execute(['id' => $actorDbId]);
        if (!$checkActor->fetch()) {
            session_destroy();
            echo json_encode(['status' => 'error', 'message' => 'Your session is invalid. Please log in again.']);
            exit();
        }
    }

    if ($actorDbId && $targetUserId === (int)$actorDbId && $action === 'delete_user') {
        echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account']);
        exit();
    }

    // Role restrictions for Admins
    if ($actorRole === 'admin') {
        if ($action === 'update_role') {
            echo json_encode(['status' => 'error', 'message' => 'Role updates are restricted to Superadmins only']);
            exit();
        }
        if ($targetRole === 'superadmin') {
            echo json_encode(['status' => 'error', 'message' => 'Admins cannot manage Superadmin accounts']);
            exit();
        }
        if ($action === 'update_role' && $targetRole === 'admin' && $newRole === 'user') {
            echo json_encode(['status' => 'error', 'message' => 'Admins cannot demote other administrators']);
            exit();
        }
    }

    if ($actorRole === 'superadmin') {
        // Handle Superadmin Promotion
        if ($action === 'update_role' && $newRole === 'superadmin') {
            if ($targetUserId === (int)$actorDbId) {
                echo json_encode(['status' => 'error', 'message' => 'You are already a Superadmin.']);
                exit();
            }

            $stmtPromote = $conn->prepare("UPDATE users SET role = 'superadmin', status = 'approved' WHERE id = :id");
            $stmtPromote->execute(['id' => $targetUserId]);

            logActivity("Superadmin Promotion: $targetName promoted to Superadmin.", 'User Mgmt', [
                'promoted_user_id' => $targetUserId
            ]);

            echo json_encode(['status' => 'success', 'message' => "Successfully promoted $targetName to Superadmin."]);
            exit();
        }

        // --- "Last Man Standing" Protection & Soft Delete ---
        // Prevents deactivating the only superadmin without a handover
        if ($targetRole === 'superadmin') {
            if ($action === 'delete_user' || ($action === 'update_role' && $newRole !== 'superadmin')) {
                if ($superCount <= 1) {
                    echo json_encode(['status' => 'error', 'message' => 'The system must have at least one active Superadmin. To transfer authority, promote another user to Superadmin instead of deleting yourself.']);
                    exit();
                }
            }
            
            // Standard deactivation (not a handover)
            if ($action === 'delete_user') {
                $stmt = $conn->prepare("UPDATE users SET status = 'deactivated' WHERE id = :id");
                $stmt->execute(['id' => $targetUserId]);
                
                logActivity("Soft Deleted (Deactivated) Superadmin: $targetName", 'User Mgmt', ['target_user_id' => $targetUserId]);
                echo json_encode(['status' => 'success', 'message' => "Superadmin $targetName deactivated (Soft Delete)."]);
                exit();
            }
        }
    }

    // Direct Execution for other roles/actions
    if ($action === 'delete_user') {
        $reason = $_POST['deletion_reason'] ?? 'No reason provided';
        $docPath = '';

        if (isset($_FILES['deletion_doc']) && $_FILES['deletion_doc']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['deletion_doc']['tmp_name'];
            $ext = pathinfo($_FILES['deletion_doc']['name'], PATHINFO_EXTENSION);
            $newName = "deletion_" . $targetUserId . "_" . time() . "." . $ext;
            $uploadDir = '../uploads/deletion_docs/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                $docPath = $newName;
            }
        }

        if (empty($docPath) && $_SESSION['role'] === 'superadmin') {
            echo json_encode(['status' => 'error', 'message' => 'Supporting document is required for deletion.']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $targetUserId]);

        logActivity("Permanently Deleted User: $targetName", 'User Mgmt', [
            'target_user_id' => $targetUserId,
            'target_user_name' => $targetName,
            'deletion_reason' => $reason,
            'document_ref' => $docPath,
            'deleted_by' => $_SESSION['user_name']
        ]);

        echo json_encode(['status' => 'success', 'message' => "Successfully deleted account: $targetName."]);
        exit();
    }

    if ($action === 'block_user' || $action === 'unblock_user') {
        $newStatus = ($action === 'block_user') ? 'blocked' : 'approved';
        
        // If restoring (unblocking/reactivating) a Superadmin, demote them to Admin 
        // to maintain the "Single Active Superadmin" rule.
        if ($action === 'unblock_user' && $targetRole === 'superadmin') {
            $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'id' => $targetUserId]);
            logActivity("Restored deactivated Superadmin: $targetName.", 'User Mgmt');
            echo json_encode(['status' => 'success', 'message' => "Superadmin $targetName restored successfully."]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'id' => $targetUserId]);
            logActivity(($action === 'block_user' ? "Blocked user: $targetName" : "Unblocked user: $targetName"), 'User Mgmt');
            echo json_encode(['status' => 'success', 'message' => "User " . ($action === 'block_user' ? "blocked" : "unblocked") . " successfully"]);
        }
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt->execute(['role' => $newRole, 'id' => $targetUserId]);

    logActivity("Updated user role: $targetName", 'User Mgmt', [
        'target_user_id' => $targetUserId,
        'target_user_name' => $targetName,
        'old_role' => $targetRole,
        'new_role' => $newRole
    ]);

    echo json_encode(['status' => 'success', 'message' => 'User role updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}

