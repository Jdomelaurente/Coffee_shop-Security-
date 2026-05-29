<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once '../includes/db.php';
ob_start();

try {
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
    $username         = trim($_POST['username'] ?? '');
    $firstName        = ucwords(strtolower(trim($_POST['firstName'] ?? '')));
    $lastName         = ucwords(strtolower(trim($_POST['lastName'] ?? '')));
    $middleName       = ucwords(strtolower(trim($_POST['middleName'] ?? '')));
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
       BASIC VALIDATION
    ========================= */
    if (empty($id_number) || empty($username) || empty($email) || empty($password1) || empty($firstName) || empty($lastName)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
        exit;
    }

    if (strlen($username) < 4 || strlen($username) > 20) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Username must be between 4 and 20 characters"]);
        exit;
    }

    if (preg_match('/\s/', $username)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Username cannot contain spaces"]);
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Username can only contain letters, numbers, and underscores"]);
        exit;
    }

    if (empty($answer1) || empty($answer2) || empty($answer3)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "All security answers are required"]);
        exit;
    }

    if ($password1 !== $password2) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }

    if (empty($dob)) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Date of birth is required"]);
        exit;
    }

    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate)->y;

    if ($age < 18) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "You must be at least 18 years old"]);
        exit;
    }

    /* =========================
       CHECK DUPLICATES
    ========================= */
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id_number = :id_number OR username = :username");
    $stmt->execute(['id_number' => $id_number, 'username' => $username]);
    if ($stmt->fetch()) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "ID Number or Username already exists"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
        exit;
    }

    /* =========================
       HASH PASSWORD & INSERT
    ========================= */
    $passwordHash = password_hash($password1, PASSWORD_DEFAULT);

    $sql = "
    INSERT INTO users (
        id_number, username, first_name, last_name, middle_name, extension_name,
        age, sex, contact, dob, email, password,
        purok, barangay, city_municipality, province, country, zip_code, 
        question1, answer1, question2, answer2, question3, answer3, role, status
    )
    VALUES (
        :id_number, :username, :first_name, :last_name, :middle_name, :extension_name,
        :age, :sex, :contact, :dob, :email, :password,
        :purok, :barangay, :city_municipality, :province, :country, :zip_code, 
        :q1, :a1, :q2, :a2, :q3, :a3, 'user', 'pending'
    )
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id_number'        => $id_number,
        'username'         => $username,
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
        'a1' => password_hash($answer1, PASSWORD_DEFAULT),
        'q2' => $question2,
        'a2' => password_hash($answer2, PASSWORD_DEFAULT),
        'q3' => $question3,
        'a3' => password_hash($answer3, PASSWORD_DEFAULT)
    ]);

    ob_clean();
    echo json_encode([
        "status"  => "success",
        "message" => "Account successfully created! Please wait for approval."
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => "System error: " . $e->getMessage()
    ]);
}
ob_end_flush();
?>
