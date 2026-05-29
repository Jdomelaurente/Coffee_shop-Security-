<?php
// setup/change_password.php
session_start();
require_once '../includes/db.php';
require_once '../includes/log_functions.php';

// Only allow if they are in the "password change" flow
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['temp_user_id'];
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_setup'])) {
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    
    $q1 = $_POST['question1'] ?? '';
    $a1 = trim($_POST['answer1'] ?? '');
    $q2 = $_POST['question2'] ?? '';
    $a2 = trim($_POST['answer2'] ?? '');
    $q3 = $_POST['question3'] ?? '';
    $a3 = trim($_POST['answer3'] ?? '');

    // Complexity Check for Password
    $hasUpper = preg_match('@[A-Z]@', $newPass);
    $hasLower = preg_match('@[a-z]@', $newPass);
    $hasNumber = preg_match('@[0-9]@', $newPass);
    $hasSpecial = preg_match('@[^\w]@', $newPass);
    $length = strlen($newPass);

    if (!$hasUpper || !$hasLower || !$hasNumber || !$hasSpecial || $length < 12 || $length > 15) {
        $error = "Password must be 12-15 characters long and include uppercase, lowercase, numbers, and symbols.";
    } elseif ($newPass !== $confirmPass) {
        $error = "Passwords do not match.";
    } elseif (empty($a1) || empty($a2) || empty($a3)) {
        $error = "All security questions must be answered.";
    } else {
        try {
            $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
            $hashedA1 = password_hash($a1, PASSWORD_DEFAULT);
            $hashedA2 = password_hash($a2, PASSWORD_DEFAULT);
            $hashedA3 = password_hash($a3, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                UPDATE users 
                SET password = :p, 
                    requires_password_change = FALSE,
                    question1 = :q1, answer1 = :a1,
                    question2 = :q2, answer2 = :a2,
                    question3 = :q3, answer3 = :a3
                WHERE id = :id
            ");
            
            $stmt->execute([
                'p' => $hashedPass,
                'q1' => $q1, 'a1' => $hashedA1,
                'q2' => $q2, 'a2' => $hashedA2,
                'q3' => $q3, 'a3' => $hashedA3,
                'id' => $userId
            ]);

            // Clear temp session and force a fresh login
            session_destroy();
            $success = true;
            logActivity("Account setup completed (Password & Security Questions)", "Security", ['user_id' => $userId]);
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Account Setup | Kalinga Coffee</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #6c4e31; --primary-light: #FFEAC5; --bg-overlay: rgba(45, 23, 15, 0.85); }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: url('../assets/images/background.jpg') center/cover no-repeat fixed;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--bg-overlay);
            z-index: 0;
        }

        .card { 
            background: #fff; 
            padding: 2rem; 
            border-radius: 20px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.3); 
            width: 100%; 
            max-width: 420px; 
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 234, 197, 0.2);
        }

        .step-indicator {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }

        .step-dot {
            flex: 1;
            height: 4px;
            background: #eee;
            border-radius: 10px;
            transition: 0.3s;
        }

        .step-dot.active { background: var(--primary); }

        h1 { color: var(--primary); font-size: 1.5rem; margin-bottom: 0.3rem; text-align: center; font-weight: 800; }
        .subtitle { color: #666; text-align: center; margin-bottom: 1.5rem; font-size: 0.85rem; line-height: 1.4; }
        
        .form-step { display: none; }
        .form-step.active { display: block; animation: fadeIn 0.4s ease; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.4rem; font-weight: 700; font-size: 0.75rem; color: #444; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input, select { 
            width: 100%; 
            padding: 0.8rem; 
            border: 2px solid #f0f0f0; 
            border-radius: 10px; 
            box-sizing: border-box; 
            font-family: inherit; 
            font-size: 0.9rem;
            transition: 0.3s;
        }

        input:focus, select:focus { outline: none; border-color: var(--primary); background: #fffcf9; }

        .btn-container { display: flex; gap: 0.8rem; margin-top: 1.5rem; }
        .btn { flex: 1; padding: 0.9rem; background: var(--primary); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .btn:hover { background: #5a3f28; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(108, 78, 49, 0.3); }
        .btn-outline { background: transparent; border: 2px solid var(--primary); color: var(--primary); }
        .btn-outline:hover { background: #fdfaf7; color: #5a3f28; border-color: #5a3f28; }

        .error { background: #fee2e2; color: #b91c1c; padding: 0.8rem; border-radius: 10px; font-size: 0.8rem; margin-bottom: 1.2rem; text-align: center; border-left: 4px solid #b91c1c; }
        
        .req-list { display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; margin-top: 0.6rem; }
        .req-item { font-size: 0.7rem; color: #888; display: flex; align-items: center; gap: 6px; }
        .req-item.valid { color: #059669; }
        .req-item i { font-size: 0.6rem; }

        .toggle-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999; }
    </style>
</head>
<body>
    <div class="card">
        <div class="step-indicator">
            <div class="step-dot active" id="dot1"></div>
            <div class="step-dot" id="dot2"></div>
        </div>

        <h1>Account Setup</h1>
        
        <?php if ($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="setupForm">
            <!-- STEP 1: PASSWORD -->
            <div class="form-step active" id="step1">
                <p class="subtitle">Welcome! To secure your account, please set a strong private password.</p>
                
                <div class="form-group">
                    <label>New Password</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" id="new_pass" required>
                        <i class="fas fa-eye toggle-icon" id="toggle_new"></i>
                    </div>
                    <div class="req-list">
                        <div class="req-item" id="req_len"><i class="fas fa-circle"></i> 12-15 Chars</div>
                        <div class="req-item" id="req_up"><i class="fas fa-circle"></i> Uppercase</div>
                        <div class="req-item" id="req_low"><i class="fas fa-circle"></i> Lowercase</div>
                        <div class="req-item" id="req_num"><i class="fas fa-circle"></i> Numbers</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div style="position: relative;">
                        <input type="password" name="confirm_password" id="confirm_pass" required>
                        <i class="fas fa-eye toggle-icon" id="toggle_confirm"></i>
                    </div>
                </div>

                <button type="button" class="btn" onclick="nextStep()">Next: Security Questions <i class="fas fa-arrow-right"></i></button>
            </div>

            <!-- STEP 2: SECURITY QUESTIONS -->
            <div class="form-step" id="step2">
                <p class="subtitle">Choose 3 security questions to help recover your account if you ever lose access.</p>
                
                <div class="form-group">
                    <label>Question 1</label>
                    <select name="question1" required>
                        <option value="pet">What was the name of your first pet?</option>
                        <option value="city" selected>In what city were you born?</option>
                        <option value="school">What was the name of your elementary school?</option>
                    </select>
                    <input type="password" name="answer1" placeholder="Your answer" required style="margin-top: 0.5rem;">
                </div>

                <div class="form-group">
                    <label>Question 2</label>
                    <select name="question2" required>
                        <option value="car">What was the make of your first car?</option>
                        <option value="mother" selected>What is your mother's maiden name?</option>
                        <option value="book">What is your favorite book?</option>
                    </select>
                    <input type="password" name="answer2" placeholder="Your answer" required style="margin-top: 0.5rem;">
                </div>

                <div class="form-group">
                    <label>Question 3</label>
                    <select name="question3" required>
                        <option value="color">What is your favorite color?</option>
                        <option value="job" selected>What was your first job?</option>
                        <option value="hobby">What is your favorite hobby?</option>
                    </select>
                    <input type="password" name="answer3" placeholder="Your answer" required style="margin-top: 0.5rem;">
                </div>

                <div class="btn-container">
                    <button type="button" class="btn btn-outline" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="submit" name="complete_setup" class="btn">Finish & Login <i class="fas fa-check-circle"></i></button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function nextStep() {
            const pass = document.getElementById('new_pass').value;
            const confirm = document.getElementById('confirm_pass').value;
            
            if (pass.length < 12 || pass !== confirm) {
                Swal.fire({ icon: 'warning', title: 'Wait!', text: 'Please ensure your passwords match and meet the 12-character requirement.' });
                return;
            }

            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.getElementById('dot2').classList.add('active');
        }

        function prevStep() {
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');
            document.getElementById('dot2').classList.remove('active');
        }

        // Toggle Password Visibility
        function setupToggle(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            toggle.addEventListener('click', () => {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                toggle.classList.toggle('fa-eye');
                toggle.classList.toggle('fa-eye-slash');
            });
        }
        setupToggle('new_pass', 'toggle_new');
        setupToggle('confirm_pass', 'toggle_confirm');

        // Real-time Password Validation
        document.getElementById('new_pass').addEventListener('input', function() {
            const val = this.value;
            const checks = {
                len: val.length >= 12 && val.length <= 15,
                up: /[A-Z]/.test(val),
                low: /[a-z]/.test(val),
                num: /[0-9]/.test(val)
            };
            
            for (const [id, valid] of Object.entries(checks)) {
                const el = document.getElementById('req_' + id);
                el.classList.toggle('valid', valid);
                el.querySelector('i').className = valid ? 'fas fa-check-circle' : 'fas fa-circle';
            }
        });

        <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Setup Complete!',
            text: 'Your account is now fully secured. You can log in with your new password.',
            confirmButtonText: 'Proceed to Login',
            confirmButtonColor: '#6c4e31'
        }).then(() => {
            window.location.href = '../login.php';
        });
        <?php endif; ?>
    </script>
</body>
</html>
