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
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .forgot-password-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: linear-gradient(135deg, #4a2c2a 0%, #2c1810 100%);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .forgot-password-container h1 {
            text-align: center;
            color: #FFEAC5;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .forgot-password-container p {
            text-align: center;
            color: #ffffff;
            margin-bottom: 25px;
            font-size: 0.95rem;
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
            font-size: 0.85rem;
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
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255, 234, 197, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
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
            padding: 12px;
            background: #FFEAC5;
            color: #2c1810;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
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
            padding: 10px;
            background: transparent;
            color: #FFEAC5;
            border: 2px solid #FFEAC5;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: rgba(255, 234, 197, 0.1);
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
    </style>
</head>
<body>

<div class="forgot-password-container">
    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active" id="step-1-indicator">1. Email</div>
        <div class="step" id="step-2-indicator">2. OTP</div>
        <div class="step" id="step-3-indicator">3. Security</div>
        <div class="step" id="step-4-indicator">4. Password</div>
    </div>

    <!-- Step 1: Email Input -->
    <div class="form-step active" id="step-1">
        <h1>Forgot Password?</h1>
        <p>Enter your email address to receive a verification code</p>
        
        <form id="email-form">
            <div class="input-group">
                <label for="email">Email Address <span class="hash">*</span></label>
                <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                <div class="error-message" id="email-error"></div>
            </div>
            
            <button type="submit" class="btn-submit" id="send-otp-btn">Send Verification Code</button>
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
                <input type="text" name="answer1" id="answer1" placeholder="Your answer" required style="margin-top: 10px;">
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
                <input type="text" name="answer2" id="answer2" placeholder="Your answer" required style="margin-top: 10px;">
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
                <input type="text" name="answer3" id="answer3" placeholder="Your answer" required style="margin-top: 10px;">
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

    // Step 1: Send OTP
    document.getElementById('email-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const btn = document.getElementById('send-otp-btn');
        
        btn.disabled = true;
        btn.textContent = 'Sending...';

        fetch('process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_otp', email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('email-display').textContent = email;
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

        fetch('process_forgot_password.php', {
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

        fetch('process_forgot_password.php', {
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

   // Step 4: Reset Password - Update this section in your forgot_password.php
    document.getElementById('password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        // Validate passwords match
        if (newPassword !== confirmPassword) {
            document.getElementById('confirm-error').textContent = 'Passwords do not match';
            document.getElementById('confirm-error').style.display = 'block';
            return;
        }

        // Validate password strength
        if (newPassword.length < 8) {
            document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
            document.getElementById('password-error').style.display = 'block';
            return;
        }
        
        const btn = document.getElementById('reset-password-btn');
        btn.disabled = true;
        btn.textContent = 'Resetting...';

        fetch('process_forgot_password.php', {
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
        const email = document.getElementById('email').value;
        
        fetch('process_forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_otp', email: email })
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