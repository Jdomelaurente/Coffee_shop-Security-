<?php
session_start();
header('Content-Type: application/json');

// PostgreSQL connection
$host = "localhost";
$db = "coffee_shop";
$user = "postgres";
$pass = "postgres";
$port = "5432";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db;", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Initialize response
$response = [
    'status' => 'error',
    'message' => ''
];

// Check if the user is locked out
if (isset($_SESSION['lock_time']) && time() < $_SESSION['lock_time']) {
    $remainingTime = $_SESSION['lock_time'] - time();
    $response['status'] = 'locked';
    $response['lockTime'] = $remainingTime;
    $response['message'] = "Account is locked. Try again in $remainingTime seconds.";
    echo json_encode($response);
    exit();
}

// Get username and password from POST
$log_username = $_POST['log_username'] ?? '';
$log_password = $_POST['log_password'] ?? '';

// Validate username length and format
if (strlen($log_username) < 4 || strlen($log_username) > 11) {
    echo json_encode(["status" => "error", "message" => "ID must be between 4 and 11 characters"]);
    exit();
}

if (!preg_match('/^[0-9-]+$/', $log_username)) {
    echo json_encode(["status" => "error", "message" => "ID must contain only numbers and dashes"]);
    exit();
}

// Track failed login attempts
$lockDurations = [15, 30, 60]; // seconds
$failedAttempts = $_SESSION['failed_attempts'] ?? 0;

// Fetch user from database
$stmt = $conn->prepare("SELECT id, password, status, role FROM users WHERE id_number = :id_number");
$stmt->execute(['id_number' => $log_username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Check Status first
    if ($user['status'] === 'pending') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Your account is pending administrator approval. Please wait for confirmation.'
        ]);
        exit;
    }

    if (password_verify($log_password, $user['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $log_username;
        
        // Include logging functions
        require_once 'log_functions.php';
        
        // Get COMPLETE user details for logging and session
        $stmt = $conn->prepare("SELECT first_name, last_name, role FROM users WHERE id_number = :id");
        $stmt->execute(['id' => $log_username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Store user data in session
        $_SESSION['role'] = $userData['role'];
        $_SESSION['first_name'] = $userData['first_name'];
        $_SESSION['last_name'] = $userData['last_name'];
        $_SESSION['user_name'] = trim($userData['first_name'] . ' ' . $userData['last_name']);
        
        // Log the login activity with complete user information
        logActivity(
            'User logged in',
            'Authentication',
            [
                'username' => $log_username,
                'user_name' => $_SESSION['user_name'],
                'role' => $userData['role'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );
        
        $response['status'] = 'success';
        $response['role'] = $userData['role'];
        $response['message'] = 'Login successful!';
        
        // Reset failed attempts on successful login
        $_SESSION['failed_attempts'] = 0;
        unset($_SESSION['lock_time']);
        
        echo json_encode($response);
        exit();
    } else {
        // Incorrect password
        $_SESSION['failed_attempts'] = ++$failedAttempts;
        $response['message'] = 'Incorrect password.';
    }
} else {
    // Username not found
    $_SESSION['failed_attempts'] = ++$failedAttempts;
    $response['message'] = 'Incorrect username.';
}

// Handle lockout after multiple failed attempts
if ($failedAttempts >= 9) {
    $_SESSION['lock_time'] = time() + $lockDurations[2];
    $response['status'] = 'locked';
    $response['lockTime'] = $lockDurations[2];
    $response['message'] = "Too many failed attempts. Account locked for 60 seconds.";
} elseif ($failedAttempts >= 6) {
    $_SESSION['lock_time'] = time() + $lockDurations[1];
    $response['status'] = 'locked';
    $response['lockTime'] = $lockDurations[1];
    $response['message'] = "Too many failed attempts. Account locked for 30 seconds.";
} elseif ($failedAttempts >= 3) {
    $_SESSION['lock_time'] = time() + $lockDurations[0];
    $response['status'] = 'locked';
    $response['lockTime'] = $lockDurations[0];
    $response['message'] = "Too many failed attempts. Account locked for 15 seconds.";
} else {
    $response['message'] = "Attempt $failedAttempts: Incorrect credentials. Please try again.";
}

// Optional: Suggest password reset after 2 failed attempts
if ($failedAttempts >= 2 && $failedAttempts < 3) {
    $response['status'] = 'reset_alert';
    $response['message'] = 'Forgot Password? Would you like to reset it?';
}

echo json_encode($response);
?>