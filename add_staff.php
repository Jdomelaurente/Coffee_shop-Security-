<?php
session_start();
header('Content-Type: application/json');

// Security Check: Only allow logged-in admins
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

require_once 'db.php';
require_once 'log_functions.php';

/* =========================
   COLLECT FORM DATA
========================= */
$id_number     = trim($_POST['id_number'] ?? '');
$email         = trim($_POST['email'] ?? '');
$firstName     = trim($_POST['firstName'] ?? '');
$lastName      = trim($_POST['lastName'] ?? '');
$middleName    = trim($_POST['middleName'] ?? '');
$role          = trim($_POST['role'] ?? 'staff');
$password      = $_POST['password'] ?? '';
$sex           = trim($_POST['sex'] ?? '');
$contact       = trim($_POST['contact'] ?? '');
$dob           = $_POST['dob'] ?? '';

/* =========================
   BASIC VALIDATION
========================= */
if (empty($id_number) || empty($email) || empty($firstName) || empty($lastName) || empty($password) || empty($dob)) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
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
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id_number = :id OR email = :email");
    $stmt->execute(['id' => $id_number, 'email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "ID Number or Email already exists"]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error during validation"]);
    exit;
}

/* =========================
   HASH PASSWORD
======================== */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/* =========================
   INSERT STAFF
========================= */
$sql = "
INSERT INTO users (
    id_number, first_name, last_name, middle_name,
    age, sex, contact, dob, email, password, role, status,
    purok, barangay, city_municipality, province, country, zip_code,
    question1, answer1, question2, answer2, question3, answer3
)
VALUES (
    :id_number, :first_name, :last_name, :middle_name,
    :age, :sex, :contact, :dob, :email, :password, :role, 'approved',
    'N/A', 'N/A', 'N/A', 'N/A', 'Philippines', '0000',
    'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A'
)
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id_number' => $id_number,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'middle_name' => $middleName ?: null,
        'age' => $age,
        'sex' => $sex,
        'contact' => $contact,
        'dob' => $dob,
        'email' => $email,
        'password' => $passwordHash,
        'role' => $role
    ]);

    // Log the activity
    logActivity("Added new $role: $firstName $lastName", "User Mgmt", [
        'new_user_id' => $id_number,
        'role' => $role
    ]);

    echo json_encode([
        "status"  => "success",
        "message" => "Staff account successfully created"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database insertion failed: " . $e->getMessage()
    ]);
}
