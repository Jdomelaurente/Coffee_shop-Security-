<?php
session_start();
require_once 'includes/db.php';

// Redirect if already logged in (optional, but requested for previous separate pages)
if (isset($_SESSION['logged_in'])) {
    if (($_SESSION['role'] ?? '') === 'superadmin') {
        header("Location: superadmin_dash.php");
        exit;
    }
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin_dash.php");
        exit;
    }
}

// Check if a superadmin already exists
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin' AND status = 'approved'");
$superadminCount = (int)$stmt->fetchColumn();
$SUPERADMIN_LIMIT = 1;
$superadminFull = $superadminCount >= $SUPERADMIN_LIMIT;

// Check if an admin already exists
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'approved'");
$adminCount = (int)$stmt->fetchColumn();
$ADMIN_LIMIT = 1;
$adminFull = $adminCount >= $ADMIN_LIMIT;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Registration | Brew Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: url('assets/images/background.jpg') center/cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(45, 23, 15, 0.75);
            z-index: 0;
        }
        .register-container {
            position: relative;
            z-index: 1;
            background: white;
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .register-header {
            background: var(--brown-dark);
            color: var(--cream);
            padding: 3rem;
            text-align: center;
            position: relative;
        }
        .register-header h1 {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .register-header p {
            opacity: 0.8;
            font-size: 1.8rem;
        }
        .register-body {
            padding: 4rem;
        }
        .form-section {
            margin-bottom: 3rem;
        }
        .section-title {
            font-size: 2.0rem;
            font-weight: 700;
            color: var(--brown);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(139, 99, 71, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .form-group {
            margin-bottom: 2rem;
        }
        .form-group label {
            display: block;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--brown-dark);
            margin-bottom: 0.8rem;
        }
        .form-control {
            width: 100%;
            padding: 1.4rem;
            border: 1.5px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            font-size: 1.7rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--brown);
            box-shadow: 0 0 0 4px rgba(62, 31, 0, 0.05);
        }
        .btn-register {
            background: var(--brown-dark);
            color: var(--cream);
            width: 100%;
            padding: 1.8rem;
            border: none;
            border-radius: 10px;
            font-size: 1.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 2rem;
        }
        .btn-register:hover {
            background: var(--brown);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(62, 31, 0, 0.2);
        }
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        .back-link a {
            color: var(--brown);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.4rem;
        }
        @media (max-width: 768px) {
            .grid-3, .grid-2 { grid-template-columns: 1fr; }
        }
        .role-selector {
            background: #fdfaf0;
            border: 2px dashed var(--brown-light);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 3rem;
            text-align: center;
        }
        .role-selector label {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--brown-dark);
        }
        .password-requirements {
            font-size: 1.1rem;
            color: #666;
            margin-top: 0.5rem;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .form-group.error .form-control { border-color: #e74c3c; background-color: #fdf2f2; }
        .error-msg { color: #e74c3c; font-size: 0.85rem; margin-top: 0.4rem; display: none; font-weight: 500; }
        .form-group.error .error-msg { display: block; }
    </style>
</head>
<body>

<div class="register-container" style="max-width: 1000px; border-radius: 12px; border: 1px solid rgba(108, 78, 49, 0.1);">
    <div class="register-header" style="background: linear-gradient(135deg, var(--primary-brown) 0%, #4D3723 100%); padding: 2.5rem; text-align: left; display: flex; align-items: center; gap: 2rem;">
        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2.4rem; color: #fff;">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <h1 id="headerTitle" style="font-size: 2.8rem; margin: 0; color: #fff;">System Registration</h1>
            <p id="headerSub" style="font-size: 1.6rem; opacity: 0.9; color: #fff;">Initialize official administrative access for Kalinga Coffee</p>
        </div>
    </div>
    
    <div class="register-body" style="padding: 2.5rem;">
        <form id="systemRegisterForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                
                <!-- Left Column: Personal Information -->
                <div style="border-right: 1px solid rgba(108, 78, 49, 0.08); padding-right: 3rem;">
                    <h4 style="color: var(--primary-brown); border-bottom: 2px solid rgba(108, 78, 49, 0.1); padding-bottom: 0.6rem; margin-bottom: 1.5rem; font-size: 1.6rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-user-circle" style="margin-right: 0.6rem;"></i>Personal Details
                    </h4>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="firstName" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="e.g. Juan" required>
                            <div class="error-msg">First name is required</div>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="lastName" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="e.g. Dela Cruz" required>
                            <div class="error-msg">Last name is required</div>
                        </div>
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middleName" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="Optional">
                            <div class="error-msg"></div>
                        </div>
                        <div class="form-group">
                            <label>Sex</label>
                            <select name="sex" class="form-control" style="padding: 0.8rem 1.2rem;" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <div class="error-msg"></div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control" style="padding: 0.8rem 1.2rem;" required>
                            <div class="error-msg">Date of birth is required</div>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="09xxxxxxxxx" required>
                            <div class="error-msg">Contact number is required</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="name@example.com" required>
                        <div class="error-msg">Valid email is required</div>
                    </div>
                </div>

                <!-- Right Column: Account & Security -->
                <div>
                    <h4 style="color: var(--primary-brown); border-bottom: 2px solid rgba(108, 78, 49, 0.1); padding-bottom: 0.6rem; margin-bottom: 1.5rem; font-size: 1.6rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-shield-alt" style="margin-right: 0.6rem;"></i>Account Security
                    </h4>

                    <div class="form-group">
                        <label>Register As</label>
                        <select name="role" id="regRole" class="form-control" style="padding: 0.8rem 1.2rem; background: rgba(108, 78, 49, 0.03); font-weight: 600;" onchange="updateUI()">
                            <option value="admin" <?php echo ($adminCount >= $ADMIN_LIMIT && ($_SESSION['role'] ?? '') !== 'superadmin' ? 'disabled' : ''); ?>>Administrator (<?php echo $adminCount; ?>/<?php echo $ADMIN_LIMIT; ?> slot used)</option>
                            <option value="superadmin" <?php echo ($superadminCount >= $SUPERADMIN_LIMIT && ($_SESSION['role'] ?? '') !== 'superadmin' ? 'disabled' : 'selected'); ?>>Superadmin (<?php echo $superadminCount; ?>/<?php echo $SUPERADMIN_LIMIT; ?> slot used)</option>
                         </select>
                        <?php if ($superadminFull || $adminFull): ?>
                            <div style="margin-top: 0.6rem;">
                                <?php if ($adminFull): ?>
                                    <p style="font-size: 1.1rem; color: var(--danger, #c0392b); margin-bottom: 0.4rem; font-weight: 600; font-style: italic;"><i class="fas fa-exclamation-triangle"></i> Admin limit reached. Only one admin is allowed.</p>
                                <?php endif; ?>
                                <?php if ($superadminFull): ?>
                                    <p style="font-size: 1.1rem; color: var(--danger, #c0392b); margin-top: 0.4rem; font-weight: 600; font-style: italic;"><i class="fas fa-exclamation-triangle"></i> Superadmin limit reached. Only one superadmin is allowed.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>ID Number</label>
                            <input type="text" name="id_number" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="0000-0000" maxlength="20" required>
                            <div class="error-msg">ID number is required</div>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="system_user" maxlength="20" required>
                            <div class="error-msg">Username is required</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="passInput" class="form-control" style="padding: 0.8rem 1.2rem;" placeholder="Create strong password" required>
                            <i class="fas fa-eye" style="position: absolute; right: 1.2rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--muted); font-size: 1.1rem;" onclick="const p=document.getElementById('passInput'); p.type=p.type==='password'?'text':'password'; this.classList.toggle('fa-eye-slash')"></i>
                        </div>
                        <div class="error-msg">Password does not meet policy</div>
                    </div>

                    <div style="background: rgba(108, 78, 49, 0.05); padding: 1.5rem; border-radius: 8px; border: 1px solid rgba(108, 78, 49, 0.1);">
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary-brown); margin-bottom: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Security Policy:</div>
                        <ul style="font-size: 1.1rem; color: var(--muted); padding-left: 2rem; margin: 0; line-height: 1.6;">
                            <li>12-15 characters long</li>
                            <li>Mix of A-Z, a-z, 0-9, and symbols</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2.5rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                <button type="submit" class="btn-register" id="submitBtn" style="padding: 1.5rem 5rem; width: auto; min-width: 300px;">Initialize Account</button>
                <a href="index.php" style="color: var(--muted); text-decoration: none; font-size: 1.6rem; font-weight: 500; display: flex; align-items: center; gap: 0.6rem; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-brown)'" onmouseout="this.style.color='var(--muted)'">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 is already included above -->
<script>
function updateUI() {
    const role = document.getElementById('regRole').value;
    const headerTitle = document.getElementById('headerTitle');
    const headerSub = document.getElementById('headerSub');
    const submitBtn = document.getElementById('submitBtn');

    if (role === 'superadmin') {
        headerTitle.innerText = "Superadmin Registration";
        headerSub.innerText = "Initialize the primary system controller";
        submitBtn.innerText = "Initialize Superadmin Account";
    } else {
        headerTitle.innerText = "Administrator Registration";
        headerSub.innerText = "Create a secure system administrator account";
        submitBtn.innerText = "Initialize Admin Account";
    }
}

function setError(el, msg) {
    const group = el.closest('.form-group');
    group.classList.add('error');
    group.querySelector('.error-msg').innerText = msg;
}
function setSuccess(el) {
    const group = el.closest('.form-group');
    group.classList.remove('error');
}

document.getElementById('systemRegisterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    
    this.querySelectorAll('input[required]').forEach(inp => {
        if (!inp.value.trim()) {
            setError(inp, `${inp.previousElementSibling.innerText} is required`);
            isValid = false;
        } else {
            setSuccess(inp);
        }
    });

    const formData = new FormData(this);
    const username = String(formData.get('username') || '').trim();
    const password = String(formData.get('password') || '');
    const email = String(formData.get('email') || '').trim();

    if (username && username.length < 4) {
        setError(this.querySelector('[name="username"]'), 'Username must be at least 4 chars');
        isValid = false;
    }
    if (email && !/^\S+@\S+\.\S+$/.test(email)) {
        setError(this.querySelector('[name="email"]'), 'Invalid email format');
        isValid = false;
    }
    if (password && (password.length < 12 || password.length > 15)) {
        setError(this.querySelector('[name="password"]'), 'Must be 12-15 characters');
        isValid = false;
    }

    if (!isValid) return;

    fetch('actions/system_signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful',
                text: data.message,
                confirmButtonColor: '#3E1F00'
            }).then(() => {
                window.location.href = 'index.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: data.message,
                confirmButtonColor: '#3E1F00'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected connection error occurred.',
            confirmButtonColor: '#3E1F00'
        });
    });
});

// Run once on load
updateUI();

// Real-time capitalization for name fields
['firstName', 'middleName', 'lastName'].forEach(name => {
    const field = document.querySelector(`[name="${name}"]`);
    if (field) {
        field.addEventListener('input', function() {
            let cursorPosition = this.selectionStart;
            let originalValue = this.value;
            let newValue = this.value.toLowerCase().replace(/(^\w|\s\w)/g, m => m.toUpperCase());
            if (originalValue !== newValue) {
                this.value = newValue;
                this.setSelectionRange(cursorPosition, cursorPosition);
            }
        });
    }
});
</script>

</body>
</html>
