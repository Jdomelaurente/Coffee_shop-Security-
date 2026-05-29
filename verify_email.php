<?php
// verify_email.php
require_once 'includes/db.php';
require_once 'includes/log_functions.php';

$token = $_GET['token'] ?? '';
$success = false;
$message = '';

if (empty($token)) {
    $message = "Invalid or missing verification token.";
} else {
    try {
        // Find user by token
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE verification_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update user: set verified_at and status to approved, clear token
            $update = $conn->prepare("
                UPDATE users 
                SET email_verified_at = NOW(), 
                    status = 'approved', 
                    verification_token = NULL 
                WHERE id = :id
            ");
            $update->execute(['id' => $user['id']]);
            
            $success = true;
            $message = "Your email has been verified successfully!";
            
            // Check if they need to change password
            $stmtChange = $conn->prepare("SELECT requires_password_change FROM users WHERE id = :id");
            $stmtChange->execute(['id' => $user['id']]);
            $mustChange = $stmtChange->fetchColumn();

            if ($mustChange) {
                session_start();
                $_SESSION['temp_user_id'] = $user['id'];
                header("Location: setup/change_password.php");
                exit;
            }

            // Log the activity
            logActivity("Email Verified", "Authentication", [
                'user_id' => $user['id'],
                'email' => $user['email']
            ]);
        } else {
            $message = "The verification link is invalid or has already been used.";
        }
    } catch (Exception $e) {
        $message = "An error occurred during verification: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | Kalinga Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-brown: #6c4e31;
            --brown-dark: #3e2723;
            --brown-light: #8d6e63;
            --bg-sand: #f5f5f5;
            --success: #27ae60;
            --danger: #c0392b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-sand);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--brown-dark);
        }
        .container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .icon {
            font-size: 5rem;
            margin-bottom: 2rem;
        }
        .icon.success { color: var(--success); }
        .icon.error { color: var(--danger); }
        h1 { margin-bottom: 1rem; font-weight: 700; }
        p { color: #666; line-height: 1.6; margin-bottom: 2rem; }
        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: var(--primary-brown);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: var(--brown-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 78, 49, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="icon success"><i class="fas fa-check-circle"></i></div>
            <h1>Verification Successful!</h1>
            <p><?php echo $message; ?></p>
            <a href="login.php" class="btn">Go to Login</a>
        <?php else: ?>
            <div class="icon error"><i class="fas fa-times-circle"></i></div>
            <h1>Verification Failed</h1>
            <p><?php echo $message; ?></p>
            <a href="index.php" class="btn">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>
