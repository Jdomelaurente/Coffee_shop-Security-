<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        if (($_SESSION['role'] ?? '') === 'superadmin') {
            header("Location: superadmin_dash.php");
        } elseif (($_SESSION['role'] ?? '') === 'admin') {
            header("Location: admin_dash.php");
        } else {
            header("Location: pos.php");
        }
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login | Coffee Shop</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Poppins', sans-serif;
                background: url('assets/images/background.jpg') center/cover no-repeat fixed;
                padding: 20px;
            }

            body::before {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(30, 12, 0, 0.55);
                z-index: 0;
            }

            .login-wrap {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 420px;
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            }

            .login-head {
                background: #6C4E31;
                padding: 28px 32px;
                text-align: center;
                border-bottom: 3px solid #FFEAC5;
            }

            .login-head .brand {
                font-size: 1.5rem;
                font-weight: 700;
                color: #FFEAC5;
                letter-spacing: 2px;
                text-transform: uppercase;
            }

            .login-head .brand i {
                margin-left: 8px;
            }

            .login-head p {
                color: rgba(255, 234, 197, 0.75);
                font-size: 0.8rem;
                margin-top: 4px;
                letter-spacing: 1px;
                text-transform: uppercase;
            }

            .login-body {
                padding: 32px;
                background: #fff;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-group label {
                display: block;
                font-size: 0.78rem;
                font-weight: 600;
                color: #4a3424;
                text-transform: uppercase;
                letter-spacing: 0.8px;
                margin-bottom: 7px;
            }

            .input-icon-wrap {
                position: relative;
            }

            .input-icon-wrap i {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #a07850;
                font-size: 0.9rem;
            }

            .input-icon-wrap input {
                display: block;
                width: 100%;
                padding: 11px 48px 11px 36px;
                border: 1.5px solid #ddd0c0;
                border-radius: 7px;
                font-family: 'Poppins', sans-serif;
                font-size: 0.88rem;
                color: #333;
                background: #fdfaf7;
                transition: border-color 0.2s, box-shadow 0.2s;
                outline: none;
            }

            .input-icon-wrap input:focus {
                border-color: #6C4E31;
                box-shadow: 0 0 0 3px rgba(108, 78, 49, 0.12);
                background: #fff;
            }

            .toggle-pw {
                position: absolute;
                right: 18px;
                top: 50%;
                transform: translateY(-50%);
                cursor: pointer;
                color: #a07850;
                font-size: 0.95rem;
                background: none;
                border: none;
                padding: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 5;
                transition: color 0.2s;
                margin: 0;
            }

            .toggle-pw:hover {
                color: #6C4E31;
            }

            .helper-row {
                display: flex;
                justify-content: flex-end;
                margin-top: -8px;
                margin-bottom: 18px;
            }

            .helper-row a {
                color: #6C4E31;
                text-decoration: none;
                font-size: 0.78rem;
                font-weight: 500;
            }

            .helper-row a:hover { text-decoration: underline; }

            .err {
                color: #b30000;
                font-size: 0.8rem;
                min-height: 20px;
                margin-bottom: 10px;
                text-align: center;
            }

            .btn-login {
                width: 100%;
                padding: 12px;
                background: #6C4E31;
                color: #FFEAC5;
                border: none;
                border-radius: 7px;
                font-family: 'Poppins', sans-serif;
                font-size: 0.88rem;
                font-weight: 700;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                cursor: pointer;
                transition: background 0.2s, transform 0.15s;
            }

            .btn-login:hover { background: #5a3f28; transform: translateY(-1px); }
            .btn-login:active { transform: translateY(0); }
            .btn-login:disabled { background: #c8b8a6; cursor: not-allowed; transform: none; }

            .divider {
                text-align: center;
                margin: 20px 0 14px;
                font-size: 0.75rem;
                color: #b0a090;
                text-transform: uppercase;
                letter-spacing: 1px;
                position: relative;
            }

            .divider::before, .divider::after {
                content: '';
                position: absolute;
                top: 50%;
                width: 38%;
                height: 1px;
                background: #e8ddd0;
            }
            .divider::before { left: 0; }
            .divider::after { right: 0; }

            .meta-links {
                text-align: center;
                font-size: 0.78rem;
                color: #7a6a5a;
                line-height: 1.9;
            }

            .meta-links a {
                color: #6C4E31;
                text-decoration: none;
                font-weight: 600;
            }

            .meta-links a:hover { text-decoration: underline; }

            .login-footer {
                background: #faf6f2;
                border-top: 1px solid #ede0d0;
                padding: 14px 32px;
                text-align: center;
                font-size: 0.75rem;
                color: #a09080;
            }
        </style>
    </head>
    <body>
        <div class="login-wrap">
            <div class="login-head">
                <div class="brand">Kalinga <i class="fas fa-leaf"></i></div>
                <p>Management System</p>
            </div>
            <div class="login-body">
                <form id="login-form">
                    <div class="form-group">
                        <label for="log_username">Username</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" id="log_username" name="log_username" maxlength="20" placeholder="Enter your username" required autocomplete="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="log_password">Password</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="log_password" name="log_password" placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="toggle-pw" id="toggle-pw-btn">
                                <i class="fas fa-eye" id="toggle-pw-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="helper-row">
                        <a href="forgot_password.php">Forgot Password?</a>
                    </div>

                    <div class="err" id="timer-display"></div>
                    <button type="submit" id="login-button" class="btn-login">Sign In</button>
                </form>

                <div class="divider">or</div>
                <div class="meta-links">
                    <a href="form.php">Create an Account</a>
                    &nbsp;|&nbsp;
                    <a href="index.php">Back to Home</a>
                </div>
            </div>
            <div class="login-footer">
                Works for <strong>user</strong>, <strong>admin</strong> &amp; <strong>superadmin</strong>.
            </div>
        </div>

        <script>
            let countdownInterval = null;
            let isLocked = false;

            document.getElementById('toggle-pw-btn').addEventListener('click', function() {
                const pwField = document.getElementById('log_password');
                const icon = document.getElementById('toggle-pw-icon');
                if (pwField.type === 'password') {
                    pwField.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    pwField.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });

            function startCountdown(seconds) {
                const loginButton = document.getElementById('login-button');
                const timerDisplay = document.getElementById('timer-display');
                const usernameInput = document.getElementById('log_username');
                const passwordInput = document.getElementById('log_password');

                isLocked = true;
                loginButton.disabled = true;
                usernameInput.disabled = true;
                passwordInput.disabled = true;

                let remainingTime = seconds;
                if (countdownInterval) clearInterval(countdownInterval);
                timerDisplay.textContent = `Account locked. Try again in ${remainingTime} seconds.`;

                countdownInterval = setInterval(() => {
                    remainingTime--;
                    if (remainingTime > 0) {
                        timerDisplay.textContent = `Account locked. Try again in ${remainingTime} seconds.`;
                    } else {
                        clearInterval(countdownInterval);
                        loginButton.disabled = false;
                        usernameInput.disabled = false;
                        passwordInput.disabled = false;
                        timerDisplay.textContent = '';
                        isLocked = false;
                        fetch('actions/clear_lock.php').catch(() => {});
                    }
                }, 1000);
            }

            function checkLockStatus() {
                fetch('actions/check_lock.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.locked && data.remainingTime > 0) {
                            startCountdown(data.remainingTime);
                        }
                    })
                    .catch(() => {});
            }

            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                if (isLocked) return;

                const formData = new FormData(this);
                fetch('login.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    const timerDisplay = document.getElementById('timer-display');
                    if (data.status === 'success') {
                        if (data.role === 'superadmin') {
                            window.location.href = 'superadmin_dash.php';
                        } else if (data.role === 'admin') {
                            window.location.href = 'admin_dash.php';
                        } else {
                            window.location.href = 'pos.php';
                        }
                    } else if (data.status === 'requires_password_change') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Password Change Required',
                            text: data.message,
                            confirmButtonText: 'Proceed to Change Password'
                        }).then(() => {
                            window.location.href = 'setup/change_password.php';
                        });
                    } else if (data.status === 'reset_alert') {
                        timerDisplay.innerHTML = data.message + ' <a href="forgot_password.php">Reset Here</a>';
                    } else if (data.status === 'locked') {
                        startCountdown(data.lockTime);
                    } else {
                        timerDisplay.textContent = data.message || 'Login failed.';
                    }
                })
                .catch(() => {
                    document.getElementById('timer-display').textContent = 'Unable to process login.';
                });
            });

            checkLockStatus();
        </script>
    </body>
    </html>
    <?php
    exit;
}

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

ob_start();

// Centralized database connection
require_once 'includes/db.php';

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
    ob_clean();
    echo json_encode($response);
    exit();
}

// Get ID number and password from POST
$log_username = trim($_POST['log_username'] ?? '');
$log_password = $_POST['log_password'] ?? '';

if ($log_username === '') {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Username is required"]);
    exit();
}

// Track failed login attempts
$lockDurations = [15, 30, 60]; // seconds
$failedAttempts = $_SESSION['failed_attempts'] ?? 0;

// Validate username format only
if (strlen($log_username) < 4 || strlen($log_username) > 20) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Username must be between 4 and 20 characters"]);
    exit();
}

if (!preg_match('/^[A-Za-z0-9_]+$/', $log_username)) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Username must contain only letters, numbers, and underscores"]);
    exit();
}

// Fetch user from database by Username (case-insensitive) or ID Number
$stmt = $conn->prepare("SELECT id, id_number, username, password, status, role, first_name, last_name, email_verified_at, requires_password_change FROM users WHERE LOWER(username) = LOWER(:val) OR id_number = :val");
$stmt->execute(['val' => $log_username]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($account) {
    // Check Status first
    if ($account['status'] === 'pending') {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Your account is pending approval by an Admin or Superadmin. Please wait for confirmation.'
        ]);
        exit;
    }

    // Check Email Verification
    if ($account['email_verified_at'] === null) {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Your email has not been verified yet. Please check your inbox for the verification link.'
        ]);
        exit;
    }

    if ($account['status'] === 'blocked') {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Your account has been blocked. Please contact the administrator.'
        ]);
        exit;
    }

    if ($account['status'] === 'deactivated') {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'This account has been deactivated. Please contact the system administrator to restore access.'
        ]);
        exit;
    }

    if (password_verify($log_password, $account['password'])) {
        if ($account['requires_password_change']) {
            $_SESSION['temp_user_id'] = $account['id'];
            ob_clean();
            echo json_encode([
                'status' => 'requires_password_change',
                'message' => 'First-time login detected. You must change your password to continue.'
            ]);
            exit;
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id']   = $account['id'];
        $_SESSION['id_number'] = $account['id_number'];
        $_SESSION['username']  = $account['username'];

        // Include logging functions
        require_once 'includes/log_functions.php';

        // Record login session data in users table
        $last_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $last_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stamp = $conn->prepare("UPDATE users SET last_login = NOW(), last_ip = :ip, last_agent = :ua WHERE id = :id");
        $stamp->execute(['ip' => $last_ip, 'ua' => $last_ua, 'id' => $account['id']]);

        // Create a dedicated session history entry
        $sess_stmt = $conn->prepare("INSERT INTO user_sessions (user_id, ip_address, user_agent, login_at) VALUES (:u, :ip, :ua, NOW())");
        $sess_stmt->execute(['u' => $account['id'], 'ip' => $last_ip, 'ua' => $last_ua]);
        $_SESSION['current_session_id'] = $conn->lastInsertId();

        // Store user data in session
        $_SESSION['role'] = $account['role'];
        $_SESSION['first_name'] = $account['first_name'];
        $_SESSION['last_name'] = $account['last_name'];
        $_SESSION['user_name'] = trim($account['first_name'] . ' ' . $account['last_name']);

        // Log the login activity with complete user information
        logActivity(
            'User Logged In',
            'Authentication',
            [
                'username' => $account['id_number'],
                'user_name' => $_SESSION['user_name'],
                'role' => $account['role'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );

        $response['status'] = 'success';
        $response['role'] = $account['role'];
        $response['message'] = 'Login successful!';

        // Reset failed attempts on successful login
        $_SESSION['failed_attempts'] = 0;
        unset($_SESSION['lock_time']);

        ob_clean();
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

ob_clean();
echo json_encode($response);
ob_end_flush();
?>
