<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';
require_once 'log_functions.php';

try {
    // Collect and sanitize data
    $id_number = trim($_POST['id_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $sex = $_POST['sex'] ?? 'Other';
    $contact = trim($_POST['contact'] ?? '');
    

    // Basic Validation
    if (empty($id_number) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        exit;
    }

    // Check if ID or Email exists
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id_number = :id OR email = :email");
    $stmt->execute(['id' => $id_number, 'email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'ID Number or Email already registered.']);
        exit;
    }

    // Calculate age
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    // Hash secrets
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

    // Insert into DB
    $sql = "INSERT INTO users (
                id_number, first_name, last_name, middle_name, 
                age, sex, contact, dob, email, password, role
            ) VALUES (
                :id, :f, :l, :m, :age, :sex, :contact, :dob, :email, :pass, 'admin', 'approved'
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id' => $id_number,
        'f' => $firstName,
        'l' => $lastName,
        'm' => $middleName,
        'age' => $age,
        'sex' => $sex,
        'contact' => $contact,
        'dob' => $dob,
        'email' => $email,
        'pass' => $hashed_pass
    ]);

    // Log the activity
    logActivity(
        'Admin account initialized',
        'System Initialization',
        ['admin_id' => $id_number, 'admin_name' => "$firstName $lastName"]
    );

    echo json_encode(['status' => 'success', 'message' => 'Administrator account initialized successfully!']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
