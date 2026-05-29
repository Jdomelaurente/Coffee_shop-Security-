<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: url('assets/images/background.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(45, 23, 15, 0.75);
            z-index: 0;
        }

        .forgot-password-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 50px;
            background: linear-gradient(135deg, #3d241d 0%, #23120b 100%);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            border: 1px solid rgba(255, 234, 197, 0.1);
        }

        .forgot-password-container h1 {
            text-align: center;
            color: #FFEAC5;
            margin-bottom: 12px;
            font-size: 2.4rem;
            font-weight: 700;
        }

        .forgot-password-container p {
            text-align: center;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 35px;
            font-size: 1.6rem;
            line-height: 1.6;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 5px;
            border-radius: 8px;
            color: #999;
            font-size: 1.25rem;
            position: relative;
        }

        .step.active {
            background: #FFEAC5;
            color: #2c1810;
            font-weight: bold;
        }

        .step.completed {
            background: #4CAF50;
            color: white;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            color: #FFEAC5;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 1.35rem;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid rgba(255, 234, 197, 0.2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 1.45rem;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #FFEAC5;
            background: rgba(255, 255, 255, 0.15);
        }

        .input-group select option {
            background: #2c1810;
            color: white;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .hash {
            color: #ff6b6b;
        }

        .btn-submit {
            width: 100%;
            padding: 18px;
            background: #FFEAC5;
            color: #2c1810;
            border: none;
            border-radius: 12px;
            font-size: 1.4rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .btn-submit:hover {
            background: #ffd89b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 234, 197, 0.3);
        }

        .btn-submit:disabled {
            background: #cccccc;
            color: #666666;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: #FFEAC5;
            border: 2px solid #FFEAC5;
            border-radius: 12px;
            font-size: 1.25rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: rgba(255, 234, 197, 0.1);
        }

        .password-toggle-wrapper {
            position: relative;
            margin-top: 10px;
        }

        .password-toggle-wrapper input {
            padding-right: 48px !important;
        }

        .toggle-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #FFEAC5;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .toggle-icon:hover {
            opacity: 1;
        }

        .otp-input {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 10px;
        }

        .resend-link {
            text-align: center;
            margin-top: 15px;
            color: #FFEAC5;
            font-size: 0.9rem;
        }

        .resend-link a {
            color: #FFEAC5;
            text-decoration: underline;
            cursor: pointer;
        }

        .resend-link a:hover {
            color: #ffd89b;
        }

        #timer {
            color: #ff6b6b;
            font-weight: bold;
        }

        .req-item {
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .req-item i {
            font-size: 0.6rem;
        }

        .req-item.valid {
            color: #4CAF50 !important;
        }

        .req-item.valid i {
            content: "\f058"; /* check-circle */
        }
    </style>
</head>
<body>

<div class="forgot-password-container">
    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active" id="step-1-indicator">1. Information</div>
        <div class="step" id="step-2-indicator">2. OTP</div>
        <div class="step" id="step-3-indicator">3. Security</div>
        <div class="step" id="step-4-indicator">4. Password</div>
    </div>

    <div class="form-step active" id="step-1">
        <h1>Forgot Password?</h1>
        <p>Enter your ID Number to verify your account</p>
        
        <form id="email-form">
            <div class="input-group">
                <label for="uid">ID Number <span class="hash">*</span></label>
                <input type="text" id="uid" name="id_number" placeholder="xxxx-xxxx" required>
                <div id="user-info-indicator" style="margin-top: 15px; display: none; background: rgba(76, 175, 80, 0.1); padding: 12px; border-radius: 10px; border-left: 4px solid #4CAF50;">
                    <div style="color: #4CAF50; font-weight: 700; font-size: 1.2rem; margin-bottom: 5px;">
                        <i class="fas fa-check-circle"></i> User Found: <span id="indicator-name" style="color: #f7fff7;"></span>
                    </div>
                    <div style="color: #FFEAC5; font-size: 1.1rem; opacity: 0.9;">
                        Email: <span id="indicator-email" style="font-family: monospace; letter-spacing: 1px;"></span>
                    </div>
                </div>
                <div class="error-message" id="email-error"></div>
            </div>
            
            <button type="submit" class="btn-submit" id="send-otp-btn" disabled>Send Verification Code</button>
        </form>
        
        <button class="btn-back" onclick="window.location.href='index.php'">Back to Login</button>
    </div>

    <!-- Step 2: OTP Verification -->
    <div class="form-step" id="step-2">
        <h1>Verify Code</h1>
        <p>Enter the 6-digit code sent to <span id="email-display" style="color: #FFEAC5;"></span></p>
        
        <form id="otp-form">
            <div class="input-group">
                <label for="otp">Verification Code <span class="hash">*</span></label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" placeholder="000000" required>
                <div class="error-message" id="otp-error"></div>
            </div>
            
            <button type="submit" class="btn-submit" id="verify-otp-btn">Verify Code</button>
        </form>
        
        <div class="resend-link">
            Didn't receive code? <a id="resend-otp">Resend</a> <span id="timer"></span>
        </div>
    </div>

   <!-- Step 3: Security Questions -->
    <div class="form-step" id="step-3">
        <h1>Security Verification</h1>
        <p>Answer your security questions to verify your identity</p>
        
        <form id="security-form">
            <div class="input-group">
                <label for="question1">Security Question 1 <span class="hash">*</span></label>
                <select name="question1" id="question1" required>
                    <option value="" disabled selected>Select a question</option>
                    <option value="pet">What was the name of your first pet?</option>
                    <option value="city">In what city were you born?</option>
                    <option value="school">What was the name of your elementary school?</option>
                </select>
                <div class="password-toggle-wrapper">
                    <input type="password" name="answer1" id="answer1" placeholder="Your answer" required>
                    <i class="fas fa-eye toggle-icon" onclick="toggleField('answer1', this)"></i>
                </div>
                <div class="error-message" id="q1-error"></div>
            </div>

            <div class="input-group">
                <label for="question2">Security Question 2 <span class="hash">*</span></label>
                <select name="question2" id="question2" required>
                    <option value="" disabled selected>Select a question</option>
                    <option value="car">What was the make of your first car?</option>
                    <option value="mother">What is your mother's maiden name?</option>
                    <option value="book">What is your favorite book?</option>
                </select>
                <div class="password-toggle-wrapper">
                    <input type="password" name="answer2" id="answer2" placeholder="Your answer" required>
                    <i class="fas fa-eye toggle-icon" onclick="toggleField('answer2', this)"></i>
                </div>
                <div class="error-message" id="q2-error"></div>
            </div>

            <div class="input-group">
                <label for="question3">Security Question 3 <span class="hash">*</span></label>
                <select name="question3" id="question3" required>
                    <option value="" disabled selected>Select a question</option>
                    <option value="color">What is your favorite color?</option>
                    <option value="job">What was your first job?</option>
                    <option value="hobby">What is your favorite hobby?</option>
                </select>
                <div class="password-toggle-wrapper">
                    <input type="password" name="answer3" id="answer3" placeholder="Your answer" required>
                    <i class="fas fa-eye toggle-icon" onclick="toggleField('answer3', this)"></i>
                </div>
                <div class="error-message" id="q3-error"></div>
            </div>
            
            <button type="submit" class="btn-submit" id="verify-security-btn">Verify Answers</button>
        </form>
    </div>

    <!-- Step 4: Reset Password -->
    <div class="form-step" id="step-4">
        <h1>Reset Password</h1>
        <p>Create a new password for your account</p>
        
        <form id="password-form">
            <div class="input-group">
                <label for="new-password">New Password <span class="hash">*</span></label>
                <input type="password" id="new-password" name="new_password" placeholder="Enter new password" required>
                <div id="password-requirements" style="margin-top: 10px; font-size: 0.85rem; color: #FFEAC5;">
                    <div id="req-length" class="req-item"><i class="fas fa-circle"></i> 12-15 characters</div>
                    <div id="req-upper" class="req-item"><i class="fas fa-circle"></i> At least one uppercase letter</div>
                    <div id="req-lower" class="req-item"><i class="fas fa-circle"></i> At least one lowercase letter</div>
                    <div id="req-number" class="req-item"><i class="fas fa-circle"></i> At least one number</div>
                    <div id="req-special" class="req-item"><i class="fas fa-circle"></i> At least one special character</div>
                </div>
                <div class="error-message" id="password-error"></div>
            </div>

            <div class="input-group">
                <label for="confirm-password">Confirm Password <span class="hash">*</span></label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm new password" required>
                <div class="error-message" id="confirm-error"></div>
            </div>

            <div class="input-group">
                <input type="checkbox" id="show-passwords" style="width: auto; margin-right: 5px;">
                <label for="show-passwords" style="display: inline; color: white; font-size: 0.9rem;">Show Passwords</label>
            </div>
            
            <button type="submit" class="btn-submit" id="reset-password-btn">Reset Password</button>
        </form>
    </div>
</div>

<script>
    let currentStep = 1;
    let resendTimer = null;
    let resendCountdown = 60;

    // Show/hide passwords
    document.getElementById('show-passwords').addEventListener('change', function() {
        const newPassword = document.getElementById('new-password');
        const confirmPassword = document.getElementById('confirm-password');
        const type = this.checked ? 'text' : 'password';
        newPassword.type = type;
        confirmPassword.type = type;
    });

    // Function to move to next step
    function goToStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step').forEach(el => {
            el.classList.remove('active');
            el.classList.remove('completed');
        });

        // Show current step
        document.getElementById(`step-${step}`).classList.add('active');
        document.getElementById(`step-${step}-indicator`).classList.add('active');

        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            document.getElementById(`step-${i}-indicator`).classList.add('completed');
        }

        currentStep = step;
    }

    function toggleField(id, icon) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function maskName(name) {
        if (!name) return "";
        return name.split(' ').map(part => {
            if (part.length <= 1) return part;
            return part[0] + '*'.repeat(part.length - 2) + part[part.length - 1];
        }).join(' ');
    }

    // Real-time user check
    const uidInput = document.getElementById('uid');
    const indicator = document.getElementById('user-info-indicator');
    const indicatorName = document.getElementById('indicator-name');
    const indicatorEmail = document.getElementById('indicator-email');
    const sendBtn = document.getElementById('send-otp-btn');
    const errorDiv = document.getElementById('email-error');

    uidInput.addEventListener('input', function() {
        const val = this.value.trim();
        if (val.length >= 4) {
            fetch('actions/process_forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_user_info', id_number: val })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    indicatorName.textContent = maskName(data.full_name);
                    indicatorEmail.textContent = data.masked_email;
                    indicator.style.display = 'block';
                    errorDiv.style.display = 'none';
                    sendBtn.disabled = false;
                } else {
                    indicator.style.display = 'none';
                    sendBtn.disabled = true;
                    
                    // Show specific note if account is pending or rejected
                    if (data.message && data.message.toLowerCase().includes('account is currently')) {
                        errorDiv.innerHTML = `<i class="fas fa-info-circle"></i> ${data.message}`;
                        errorDiv.style.color = '#FFEAC5'; 
                        errorDiv.style.background = 'rgba(255, 152, 0, 0.2)';
                        errorDiv.style.padding = '15px';
                        errorDiv.style.borderRadius = '10px';
                        errorDiv.style.borderLeft = '4px solid #ff9800';
                        errorDiv.style.fontSize = '1.3rem';
                        errorDiv.style.fontWeight = '600';
                        errorDiv.style.marginTop = '15px';
                        errorDiv.style.display = 'block';
                    } else if (val.length > 8) { // Only show "Not Found" if they typed a full ID
                        errorDiv.textContent = data.message || 'User not found';
                        errorDiv.style.color = '#ff6b6b'; // Error red
                        errorDiv.style.background = 'none';
                        errorDiv.style.padding = '0';
                        errorDiv.style.border = 'none';
                        errorDiv.style.fontSize = '1.1rem';
                        errorDiv.style.display = 'block';
                    } else {
                        errorDiv.style.display = 'none';
                    }
                }
            });
        } else {
            indicator.style.display = 'none';
            errorDiv.style.display = 'none';
            sendBtn.disabled = true;
        }
    });

    // Step 1: Send OTP
    document.getElementById('email-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id_number = document.getElementById('uid').value;
        const btn = document.getElementById('send-otp-btn');
        
        btn.disabled = true;
        btn.textContent = 'Sending...';

        fetch('actions/process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_otp', id_number: id_number })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('email-display').textContent = indicatorEmail.textContent;
                goToStep(2);
                startResendTimer();
                Swal.fire({
                    icon: 'success',
                    title: 'Code Sent!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                document.getElementById('email-error').textContent = data.message;
                document.getElementById('email-error').style.display = 'block';
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to send code. Please try again.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Send Verification Code';
        });
    });

    // Step 2: Verify OTP
    document.getElementById('otp-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const otp = document.getElementById('otp').value;
        const btn = document.getElementById('verify-otp-btn');
        
        btn.disabled = true;
        btn.textContent = 'Verifying...';

        fetch('actions/process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify_otp', otp: otp })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                goToStep(3);
                clearInterval(resendTimer);
                Swal.fire({
                    icon: 'success',
                    title: 'Verified!',
                    text: 'OTP verified successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                document.getElementById('otp-error').textContent = data.message;
                document.getElementById('otp-error').style.display = 'block';
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to verify code. Please try again.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Verify Code';
        });
    });

    // Step 3: Verify Security Questions
    document.getElementById('security-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'verify_security',
            question1: document.getElementById('question1').value,
            answer1: document.getElementById('answer1').value,
            question2: document.getElementById('question2').value,
            answer2: document.getElementById('answer2').value,
            question3: document.getElementById('question3').value,
            answer3: document.getElementById('answer3').value
        };
        
        const btn = document.getElementById('verify-security-btn');
        btn.disabled = true;
        btn.textContent = 'Verifying...';

        fetch('actions/process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                goToStep(4);
                Swal.fire({
                    icon: 'success',
                    title: 'Verified!',
                    text: 'Security questions answered correctly',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    text: data.message
                });
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to verify answers. Please try again.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Verify Answers';
        });
    });

    // Step 4: Password Validation & Show/Hide
    const newPassInput = document.getElementById('new-password');
    const confirmPassInput = document.getElementById('confirm-password');
    const showPassCheck = document.getElementById('show-passwords');
    
    showPassCheck.addEventListener('change', function() {
        const type = this.checked ? 'text' : 'password';
        newPassInput.type = type;
        confirmPassInput.type = type;
    });

    function validatePassword(pass) {
        const checks = {
            length: pass.length >= 12 && pass.length <= 15,
            upper: /[A-Z]/.test(pass),
            lower: /[a-z]/.test(pass),
            number: /[0-9]/.test(pass),
            special: /[^A-Za-z0-9]/.test(pass)
        };

        Object.keys(checks).forEach(id => {
            const el = document.getElementById('req-' + id);
            if (checks[id]) {
                el.classList.add('valid');
                el.querySelector('i').className = 'fas fa-check-circle';
            } else {
                el.classList.remove('valid');
                el.querySelector('i').className = 'fas fa-circle';
            }
        });

        return Object.values(checks).every(v => v === true);
    }

    newPassInput.addEventListener('input', function() {
        validatePassword(this.value);
    });

    document.getElementById('password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = newPassInput.value;
        const confirmPassword = confirmPassInput.value;
        
        if (!validatePassword(newPassword)) {
            document.getElementById('password-error').textContent = 'Password does not meet requirements';
            document.getElementById('password-error').style.display = 'block';
            return;
        }

        if (newPassword !== confirmPassword) {
            document.getElementById('confirm-error').textContent = 'Passwords do not match';
            document.getElementById('confirm-error').style.display = 'block';
            return;
        }
        
        const btn = document.getElementById('reset-password-btn');
        btn.disabled = true;
        btn.textContent = 'Resetting...';

        fetch('actions/process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'reset_password', 
                new_password: newPassword 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset!',
                    text: data.message,
                    confirmButtonText: 'Go to Login'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to reset password. Please try again.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Reset Password';
        });
    });

    // Resend OTP timer
    function startResendTimer() {
        resendCountdown = 60;
        const resendLink = document.getElementById('resend-otp');
        const timerDisplay = document.getElementById('timer');
        
        resendLink.style.pointerEvents = 'none';
        resendLink.style.opacity = '0.5';
        
        resendTimer = setInterval(() => {
            resendCountdown--;
            timerDisplay.textContent = `(${resendCountdown}s)`;
            
            if (resendCountdown <= 0) {
                clearInterval(resendTimer);
                resendLink.style.pointerEvents = 'auto';
                resendLink.style.opacity = '1';
                timerDisplay.textContent = '';
            }
        }, 1000);
    }

    // Resend OTP
    document.getElementById('resend-otp').addEventListener('click', function() {
        const id_number = uidInput.value;
        
        fetch('actions/process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_otp', id_number: id_number })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                startResendTimer();
                Swal.fire({
                    icon: 'success',
                    title: 'Code Resent!',
                    text: 'A new verification code has been sent',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
</script>

</body>
</html>