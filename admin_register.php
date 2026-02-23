<?php
session_start();
// Redirect if already logged in as admin
if (isset($_SESSION['logged_in']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dash.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | Brew Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f5f0e1 0%, #e8dfc9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .register-container {
            background: white;
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(62, 31, 0, 0.1);
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
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .register-header p {
            opacity: 0.8;
            font-size: 1.4rem;
        }
        .register-body {
            padding: 4rem;
        }
        .form-section {
            margin-bottom: 3rem;
        }
        .section-title {
            font-size: 1.6rem;
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
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--brown-dark);
            margin-bottom: 0.8rem;
        }
        .form-control {
            width: 100%;
            padding: 1.2rem;
            border: 1.5px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            font-size: 1.4rem;
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
            padding: 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1.6rem;
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
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-header">
        <h1>Administrator Registration</h1>
        <p>Create a secure system administrator portal account</p>
    </div>
    <div class="register-body">
        <form id="adminRegisterForm">
            <!-- Personal Info -->
            <div class="form-section">
                <div class="section-title"><i class="fas fa-user"></i> Personal Information</div>
                <div class="grid-3">
                    <div class="form-group">
                        <label>ID Number*</label>
                        <input type="text" name="id_number" class="form-control" placeholder="0000-0000" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address*</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@brewmast.er" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number*</label>
                        <input type="text" name="contact" class="form-control" placeholder="09xxxxxxxxx" required>
                    </div>
                </div>
                <div class="grid-3">
                    <div class="form-group">
                        <label>First Name*</label>
                        <input type="text" name="firstName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name*</label>
                        <input type="text" name="lastName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middleName" class="form-control">
                    </div>
                </div>
                <div class="grid-3">
                    <div class="form-group">
                        <label>Date of Birth*</label>
                        <input type="date" name="dob" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Sex*</label>
                        <select name="sex" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password*</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
            </div>


            <button type="submit" class="btn-register">Initialize Admin Account</button>
            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('adminRegisterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('admin_signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Admin Created',
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
</script>

</body>
</html>
