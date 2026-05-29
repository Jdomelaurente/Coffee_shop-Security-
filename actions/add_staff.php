<?php
session_start();
header('Content-Type: application/json');

// Security Check: Only allow logged-in admins/superadmins
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

require_once '../includes/db.php';
require_once '../includes/log_functions.php';

// If admin, check for "Add User" privilege
if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT can_add_user FROM admin_privileges WHERE admin_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetchColumn()) {
        echo json_encode(["status" => "error", "message" => "Access Denied: You do not have the privilege to add users."]);
        exit();
    }
}

/* =========================
   COLLECT FORM DATA
========================= */
/* =========================
   COLLECT FORM DATA
========================= */
$id_number     = trim($_POST['id_number'] ?? '');
$username      = trim($_POST['username'] ?? '');
$email         = trim($_POST['email'] ?? '');
$firstName     = ucwords(strtolower(trim($_POST['firstName'] ?? '')));
$lastName      = ucwords(strtolower(trim($_POST['lastName'] ?? '')));
$middleName    = ucwords(strtolower(trim($_POST['middleName'] ?? '')));
$role          = strtolower(trim($_POST['role'] ?? 'user'));
$sex           = trim($_POST['sex'] ?? '');
$contact       = trim($_POST['contact'] ?? '');
$dob           = $_POST['dob'] ?? '';

/* =========================
   BASIC VALIDATION
========================= */
if (empty($id_number) || empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($dob)) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit;
}

if (!in_array($role, ['user', 'admin', 'superadmin'], true)) {
    echo json_encode(["status" => "error", "message" => "Invalid role selected"]);
    exit;
}

if ($role === 'superadmin' && ($_SESSION['role'] ?? '') !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Only a Superadmin can create another Superadmin account"]);
    exit;
}

if ($role === 'admin' && ($_SESSION['role'] ?? '') !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Only superadmin can create an admin account"]);
    exit;
}

// Calculate Age
try {
    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate)->y;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Invalid date of birth"]);
    exit;
}

/* =========================
   CHECK DUPLICATES
========================= */
try {
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id_number = :id OR email = :email OR username = :username");
    $stmt->execute(['id' => $id_number, 'email' => $email, 'username' => $username]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "ID Number, Email, or Username already exists"]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error during validation"]);
    exit;
}

/* =========================
   GENERATE SECURE PASSWORD
========================= */
// Generate a random 32-character temporary password
// The user will never see this; they will set their own during verification
$tempPassword = bin2hex(random_bytes(16)); 
$passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

/* =========================
   GENERATE VERIFICATION TOKEN
========================= */
$token = bin2hex(random_bytes(32));

/* =========================
   INSERT STAFF
========================= */
$sql = "
INSERT INTO users (
    id_number, username, first_name, last_name, middle_name,
    age, sex, contact, dob, email, password, role, status,
    verification_token, requires_password_change,
    purok, barangay, city_municipality, province, country, zip_code,
    question1, answer1, question2, answer2, question3, answer3
)
VALUES (
    :id_number, :username, :first_name, :last_name, :middle_name,
    :age, :sex, :contact, :dob, :email, :password, :role, 'approved',
    :token, TRUE,
    'N/A', 'N/A', 'N/A', 'N/A', 'Philippines', '0000',
    'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A'
)
";

try {
    // Transactional Handover for Superadmin creation
    $conn->beginTransaction();

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id_number' => $id_number,
        'username' => $username,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'middle_name' => $middleName ?: null,
        'age' => $age,
        'sex' => $sex,
        'contact' => $contact,
        'dob' => $dob,
        'email' => $email,
        'password' => $passwordHash,
        'role' => $role,
        'token' => $token
    ]);

    // Send Verification Email
    require_once '../includes/mailer.php';
    Mailer::sendVerificationEmail($email, $firstName . ' ' . $lastName, $token);

    if ($role === 'superadmin') {
        // Soft Delete (Deactivate) the creator
        $stmtDeactivate = $conn->prepare("UPDATE users SET status = 'deactivated' WHERE id = :id");
        $stmtDeactivate->execute(['id' => $_SESSION['user_id']]);
        
        logActivity("Transfer of Power: Created new Superadmin $firstName $lastName and deactivated self.", "User Mgmt");
    } else {
        logActivity("Added new $role: $firstName $lastName", "User Mgmt", [
            'new_user_id' => $id_number,
            'role' => $role
        ]);
    }

    $conn->commit();

    echo json_encode([
        "status"  => "success",
        "message" => "Account successfully created"
    ]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) { $conn->rollBack(); }
    echo json_encode([
        "status" => "error",
        "message" => "Database insertion failed: " . $e->getMessage()
    ]);
}
