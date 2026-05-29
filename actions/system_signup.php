<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/log_functions.php';

ob_start();

try {
    // Collect and sanitize data
    $id_number = trim($_POST['id_number'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = ucwords(strtolower(trim($_POST['firstName'] ?? '')));
    $lastName = ucwords(strtolower(trim($_POST['lastName'] ?? '')));
    $middleName = ucwords(strtolower(trim($_POST['middleName'] ?? '')));
    $dob = $_POST['dob'] ?? '';
    $sex = $_POST['sex'] ?? 'Other';
    $contact = trim($_POST['contact'] ?? '');
    $requestedRole = strtolower(trim($_POST['role'] ?? 'admin'));

    // Validate role
    if (!in_array($requestedRole, ['admin', 'superadmin'], true)) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid role selected.']);
        exit;
    }

    // Basic Validation
    if (empty($id_number) || empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        exit;
    }

    // Username format check
    if (strlen($username) < 4 || strlen($username) > 20) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Username must be between 4 and 20 characters.']);
        exit;
    }

    // Password Complexity (12-15 chars, Upper, Lower, Number, Special)
    $hasUpper = preg_match('@[A-Z]@', $password);
    $hasLower = preg_match('@[a-z]@', $password);
    $hasNumber = preg_match('@[0-9]@', $password);
    $hasSpecial = preg_match('@[^a-zA-Z0-9]@', $password);
    $length = strlen($password);

    if (!$hasUpper || !$hasLower || !$hasNumber || !$hasSpecial || $length < 12) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Password must be at least 12 characters long and include uppercase, lowercase, numbers, and symbols."]);
        exit();
    }

    // Check if ID, Username, or Email exists
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id_number = :id OR username = :u OR email = :email");
    $stmt->execute(['id' => $id_number, 'u' => $username, 'email' => $email]);
    if ($stmt->fetch()) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'ID Number, Username, or Email already registered.']);
        exit;
    }

    // ── Superadmin Slot Management (max 1 active at a time) ──────────────────
    if ($requestedRole === 'superadmin') {
        $SUPERADMIN_LIMIT = 1;

        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin' AND status = 'approved'");
        $activeCount = (int)$stmt->fetchColumn();

        // If a superadmin already exists, prevent creating another one from the registration page
        if ($activeCount >= $SUPERADMIN_LIMIT) {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'A Superadmin already exists. Only one Superadmin is allowed in the system.']);
            exit;
        }
    }

    // ── Admin Slot Management (max 1 active at a time) ──────────────────
    if ($requestedRole === 'admin') {
        $ADMIN_LIMIT = 1;

        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'approved'");
        $activeAdminCount = (int)$stmt->fetchColumn();

        // If an admin already exists, prevent creating another one from the registration page
        if ($activeAdminCount >= $ADMIN_LIMIT) {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'An Administrator already exists. Only one Admin is allowed in the system.']);
            exit;
        }
    }


    // Calculate age
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    // Hash secrets
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

    // Insert into DB
    $sql = "INSERT INTO users (
                id_number, username, first_name, last_name, middle_name,
                age, sex, contact, dob, email, password, role, status,
                verification_token
            ) VALUES (
                :id, :u, :f, :l, :m, :age, :sex, :contact, :dob, :email, :pass, :role, 'pending', :token
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id' => $id_number,
        'u' => $username,
        'f' => $firstName,
        'l' => $lastName,
        'm' => $middleName,
        'age' => $age,
        'sex' => $sex,
        'contact' => $contact,
        'dob' => $dob,
        'email' => $email,
        'pass' => $hashed_pass,
        'role' => $requestedRole,
        'token' => $token
    ]);

    // Send Verification Email
    require_once '../includes/mailer.php';
    Mailer::sendVerificationEmail($email, $firstName . ' ' . $lastName, $token);

    // Log the activity
    logActivity(
        "$requestedRole account initialized",
        'System Initialization',
        ['target_id' => $id_number, 'target_name' => "$firstName $lastName", 'role' => $requestedRole]
    );

    ob_clean();
    echo json_encode(['status' => 'success', 'message' => ucfirst($requestedRole) . " account created successfully!"]);

} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
ob_end_flush();
?>
