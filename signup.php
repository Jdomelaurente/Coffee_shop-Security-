<?php
session_start();
header('Content-Type: application/json');

/* =========================
   DATABASE CONNECTION
========================= */
$host = "localhost";
$db   = "coffee_shop";
$user = "postgres";
$pass = "postgres"; // CHANGE if needed
$port = "5432";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

/* =========================
   COLLECT FORM DATA
========================= */
$question1 = $_POST['question1'] ?? '';
$answer1   = trim($_POST['answer1'] ?? '');
$question2 = $_POST['question2'] ?? '';
$answer2   = trim($_POST['answer2'] ?? '');
$question3 = $_POST['question3'] ?? '';
$answer3   = trim($_POST['answer3'] ?? '');
$id_number        = trim($_POST['id_number'] ?? '');
$firstName        = trim($_POST['firstName'] ?? '');
$lastName         = trim($_POST['lastName'] ?? '');
$middleName       = trim($_POST['middleName'] ?? '');
$extensionName    = trim($_POST['extensionName'] ?? '');
$sex              = trim($_POST['sex'] ?? '');
$contact          = trim($_POST['contact'] ?? '');
$dob              = $_POST['dob'] ?? '';
$email            = trim($_POST['email'] ?? '');
$password1        = $_POST['password1'] ?? '';
$password2        = $_POST['password2'] ?? '';
$purok            = trim($_POST['purok'] ?? '');
$barangay         = trim($_POST['barangay'] ?? '');
$cityMunicipality = trim($_POST['cityMunicipality'] ?? '');
$province         = trim($_POST['province'] ?? '');
$country          = trim($_POST['country'] ?? '');
$zipCode          = trim($_POST['zipCode'] ?? '');

/* =========================
   SECURITY QUESTIONS VALIDATION
========================= */
if (empty($answer1) || empty($answer2) || empty($answer3)) {
    echo json_encode(["status" => "error", "message" => "All security answers are required"]);
    exit;
}

// Ensure questions are unique
if ($question1 == $question2 || $question1 == $question3 || $question2 == $question3) {
    echo json_encode(["status" => "error", "message" => "Please select three different security questions"]);
    exit;
}

/* =========================
   BASIC VALIDATION
========================= */
if ($password1 !== $password2) {
    echo json_encode([
        "status" => "error",
        "message_password" => "Passwords do not match"
    ]);
    exit;
}

if (empty($dob)) {
    echo json_encode([
        "status" => "error",
        "message" => "Date of birth is required"
    ]);
    exit;
}

/* =========================
   CALCULATE AGE (SERVER SIDE)
========================= */
try {
    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate)->y;
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid date of birth"
    ]);
    exit;
}

if ($age < 18) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be at least 18 years old"
    ]);
    exit;
}

/* =========================
   CHECK DUPLICATES
========================= */
$stmt = $conn->prepare("SELECT 1 FROM users WHERE id_number = :id_number");
$stmt->execute(['id_number' => $id_number]);
if ($stmt->fetch()) {
    echo json_encode([
        "status" => "error",
        "message_id" => "ID number already exists"
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT 1 FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    echo json_encode([
        "status" => "error",
        "message_email" => "Email already exists"
    ]);
    exit;
}

/* =========================
   HASH PASSWORD
========================= */
$passwordHash = password_hash($password1, PASSWORD_DEFAULT);

/* =========================
   INSERT USER
========================= */
$sql = "
INSERT INTO users (
    id_number, first_name, last_name, middle_name, extension_name,
    age, sex, contact, dob, email, password,
    purok, barangay, city_municipality, province, country, zip_code, 
    question1, answer1, question2, answer2, question3, answer3, role, status
)
VALUES (
    :id_number, :first_name, :last_name, :middle_name, :extension_name,
    :age, :sex, :contact, :dob, :email, :password,
    :purok, :barangay, :city_municipality, :province, :country, :zip_code, 
    :q1, :a1, :q2, :a2, :q3, :a3, 'user', 'pending'
)
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id_number'        => $id_number,
        'first_name'       => $firstName,
        'last_name'        => $lastName,
        'middle_name'      => $middleName ?: null,
        'extension_name'   => $extensionName ?: null,
        'age'              => $age,
        'sex'              => $sex,
        'contact'          => $contact,
        'dob'              => $dob,
        'email'            => $email,
        'password'         => $passwordHash,
        'purok'            => $purok,
        'barangay'         => $barangay,
        'city_municipality'=> $cityMunicipality,
        'province'         => $province,
        'country'          => $country,
        'zip_code'         => $zipCode,
        'q1' => $question1,
        'a1' => password_hash($answer1, PASSWORD_DEFAULT), // Optional: Hash the answers for better security
        'q2' => $question2,
        'a2' => password_hash($answer2, PASSWORD_DEFAULT),
        'q3' => $question3,
        'a3' => password_hash($answer3, PASSWORD_DEFAULT)
    ]);

    echo json_encode([
        "status"  => "success",
        "message" => "Account successfully created! Please wait for the administrator to approve your account before logging in."
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database insert failed"
    ]);
}
