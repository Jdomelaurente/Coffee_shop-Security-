<?php
session_start();
header('Content-Type: application/json');

// Include the logging functions
require_once 'log_functions.php';

// Manual PHPMailer include (no Composer needed)
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['status' => 'error', 'message' => ''];

// ============================================
// ACTION 1: SEND OTP
// ============================================
if ($action === 'send_otp') {
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $response['message'] = 'Invalid email address';
        echo json_encode($response);
        exit();
    }

    // Check if email exists in database and get user details
    $stmt = $conn->prepare("SELECT id, id_number, first_name, last_name, role FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response['message'] = 'Email not found in our system';
        echo json_encode($response);
        exit();
    }

    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store OTP in session with expiry time (5 minutes)
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['reset_user_id_number'] = $user['id_number'];
    $_SESSION['reset_user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['reset_user_role'] = $user['role'];

    // Log OTP request
    logActivity(
        'Password reset OTP requested',
        'Authentication',
        [
            'user_id' => $user['id_number'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $email,
            'otp_sent' => true
        ]
    );

    // Send OTP via email
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jdomelaurente@gmail.com';  
        $mail->Password   = 'uiwy uymv zksh dfor'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('jdomelaurente@gmail.com', 'Coffee Shop');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Verification Code';
        $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                    .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
                    .header { background: linear-gradient(135deg, #4a2c2a 0%, #2c1810 100%); color: #FFEAC5; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .otp-box { background: #f9f9f9; border: 2px dashed #4a2c2a; padding: 20px; margin: 20px 0; text-align: center; border-radius: 8px; }
                    .otp { font-size: 32px; font-weight: bold; color: #4a2c2a; letter-spacing: 8px; }
                    .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>☕ Coffee Shop</h1>
                        <p>Password Reset Request</p>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Hello,</p>
                        <p>You have requested to reset your password. Use the verification code below to proceed:</p>
                        
                        <div class='otp-box'>
                            <p style='margin: 0; color: #666;'>Your Verification Code:</p>
                            <div class='otp'>$otp</div>
                        </div>
                        
                        <p><strong>This code will expire in 5 minutes.</strong></p>
                        <p>If you didn't request this, please ignore this email.</p>
                    </div>
                    <div class='footer'>
                        <p>© 2024 Coffee Shop. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        
        $response['status'] = 'success';
        $response['message'] = 'Verification code sent to your email';
        
    } catch (Exception $e) {
        $response['message'] = 'Failed to send email. Please try again later.';
        error_log("Mailer Error: {$mail->ErrorInfo}");
        
        // Log email failure
        logActivity(
            'Password reset OTP email failed',
            'Authentication',
            [
                'user_id' => $user['id_number'],
                'email' => $email,
                'error' => $mail->ErrorInfo
            ]
        );
    }

    echo json_encode($response);
    exit();
}

// ============================================
// ACTION 2: VERIFY OTP
// ============================================
if ($action === 'verify_otp') {
    $otp = $input['otp'] ?? '';

    // Check if OTP exists and not expired
    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
        $response['message'] = 'No verification code found. Please request a new one.';
        echo json_encode($response);
        exit();
    }

    if (time() > $_SESSION['otp_expiry']) {
        unset($_SESSION['otp'], $_SESSION['otp_expiry']);
        $response['message'] = 'Verification code expired. Please request a new one.';
        echo json_encode($response);
        exit();
    }

    if ($otp !== $_SESSION['otp']) {
        $response['message'] = 'Invalid verification code. Please try again.';
        
        // Log failed OTP attempt
        if (isset($_SESSION['reset_user_id_number'])) {
            logActivity(
                'Failed OTP verification attempt',
                'Authentication',
                [
                    'user_id' => $_SESSION['reset_user_id_number'],
                    'attempted_otp' => $otp
                ]
            );
        }
        
        echo json_encode($response);
        exit();
    }

    // OTP is valid
    $_SESSION['otp_verified'] = true;
    $response['status'] = 'success';
    $response['message'] = 'Verification successful';
    
    // Log successful OTP verification
    if (isset($_SESSION['reset_user_id_number'])) {
        logActivity(
            'OTP verified successfully',
            'Authentication',
            [
                'user_id' => $_SESSION['reset_user_id_number'],
                'user_name' => $_SESSION['reset_user_name'] ?? 'Unknown'
            ]
        );
    }
    
    echo json_encode($response);
    exit();
}

// ============================================
// ACTION 3: VERIFY SECURITY QUESTIONS
// ============================================
if ($action === 'verify_security') {
    if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
        $response['message'] = 'Please verify OTP first';
        echo json_encode($response);
        exit();
    }

    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Session expired. Please start over.';
        echo json_encode($response);
        exit();
    }

    $question1 = $input['question1'] ?? '';
    $answer1 = trim($input['answer1'] ?? '');
    $question2 = $input['question2'] ?? '';
    $answer2 = trim($input['answer2'] ?? '');
    $question3 = $input['question3'] ?? '';
    $answer3 = trim($input['answer3'] ?? '');

    // Fetch user's security questions from database
    $stmt = $conn->prepare("
        SELECT question1, answer1, question2, answer2, question3, answer3 
        FROM users 
        WHERE id = :user_id
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        exit();
    }

    // Verify questions match and answers are correct using password_verify()
    $q1_match = ($question1 === $user['question1']) && password_verify($answer1, $user['answer1']);
    $q2_match = ($question2 === $user['question2']) && password_verify($answer2, $user['answer2']);
    $q3_match = ($question3 === $user['question3']) && password_verify($answer3, $user['answer3']);

    if ($q1_match && $q2_match && $q3_match) {
        $_SESSION['security_verified'] = true;
        $response['status'] = 'success';
        $response['message'] = 'Security questions verified successfully';
        
        // Log successful security verification
        logActivity(
            'Security questions verified successfully',
            'Authentication',
            [
                'user_id' => $_SESSION['reset_user_id_number'],
                'user_name' => $_SESSION['reset_user_name'] ?? 'Unknown'
            ]
        );
    } else {
        // Log failed security verification
        logActivity(
            'Failed security questions verification',
            'Authentication',
            [
                'user_id' => $_SESSION['reset_user_id_number'],
                'user_name' => $_SESSION['reset_user_name'] ?? 'Unknown'
            ]
        );
        
        $response['message'] = 'One or more security answers are incorrect. Please try again.';
    }

    echo json_encode($response);
    exit();
}

// ============================================
// ACTION 4: RESET PASSWORD
// ============================================
if ($action === 'reset_password') {
    if (!isset($_SESSION['security_verified']) || !$_SESSION['security_verified']) {
        $response['message'] = 'Please complete security verification first';
        echo json_encode($response);
        exit();
    }

    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Session expired. Please start over.';
        echo json_encode($response);
        exit();
    }

    $new_password = $input['new_password'] ?? '';

    if (strlen($new_password) < 8) {
        $response['message'] = 'Password must be at least 8 characters';
        echo json_encode($response);
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
    $success = $stmt->execute([
        'password' => $hashed_password,
        'user_id' => $_SESSION['user_id']
    ]);

    if ($success) {
        // Get user details for logging
        $user_id_number = $_SESSION['reset_user_id_number'] ?? 'Unknown';
        $user_name = $_SESSION['reset_user_name'] ?? 'Unknown';
        $user_role = $_SESSION['reset_user_role'] ?? 'Unknown';
        
        // LOG THE PASSWORD CHANGE HERE!
        logActivity(
            'Password changed successfully',
            'Authentication',
            [
                'user_id' => $user_id_number,
                'user_name' => $user_name,
                'user_role' => $user_role,
                'method' => 'forgot_password',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );

        // Clear all session variables
        session_unset();
        session_destroy();

        $response['status'] = 'success';
        $response['message'] = 'Password reset successfully! You can now login with your new password.';
    } else {
        $response['message'] = 'Failed to update password. Please try again.';
        
        // Log password change failure
        logActivity(
            'Password change failed',
            'Authentication',
            [
                'user_id' => $_SESSION['reset_user_id_number'] ?? 'Unknown',
                'user_name' => $_SESSION['reset_user_name'] ?? 'Unknown'
            ]
        );
    }

    echo json_encode($response);
    exit();
}

// Invalid action
$response['message'] = 'Invalid action';
echo json_encode($response);
?>