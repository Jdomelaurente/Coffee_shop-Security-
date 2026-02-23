<?php
session_start();  // Start the session

// Redirect to appropriate dashboard if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dash.php");
    } elseif (in_array($_SESSION['role'], ['staff', 'supervisor', 'manager'])) {
        header("Location: staff_dash.php");
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
    <title>Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Header -->
<header class="header">
    <div id="menu-btn" class="fas fa-bars"></div>
    <a href="#" style="margin-right: 60%; color: #FFEAC5;" class="logo"> coffee <i class="fas fa-mug-hot"></i> </a>
    <nav class="navbar">
        <a href="#about">About</a>
        <a href="#menu">Menu</a>
        <a href="#home">Home</a>
    </nav>
    <button class='bx bxs-user' onclick="openLoginModal()" style="width:auto;">Login</button>
</header>

<!-- Login Modal -->
<div id="id01" class="modal">
    <form id="login-form" class="modal-content animate" method="post">
        <div class="imgcontainer">
            <span id="close-modal-btn" class="close" title="Close Modal">&times;</span>
        </div>
        <h1 style="text-align: center; color: #FFEAC5; margin-top: 20px;">Log In</h1>
        <div class="container">
            <div class="error" id="timer-display" style="color: red; font-weight: bold; text-align: center;"></div>
            
            <label for="log_username">ID Number:</label>
            <input type="text" maxlength="9" placeholder="xxxx-xxxx" name="log_username" id="log_username" required>
            
            <label for="log_password">Password:</label>
            <input type="password" name="log_password" id="log_password" required>
            
            <!-- Container for checkbox and forgot password link -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0;">
                <div style="display: flex; align-items: center;">
                    <input type="checkbox" id="show_log_password" style="margin-right: 5px;">
                    <label for="show_log_password" style="color: white; font-size: 0.85em;">Show Password</label>
                </div>
                <a href="forgot_password.php" style="color:red; font-size: 0.956rem; text-decoration: none;">Forgot Password?</a>
            </div>

            <button type="submit" id="login-button">Login</button>
            <p>Don't have an account? <a href="form.php" style="color:white;">Sign Up</a></p>
        </div>
    </form>
</div>

<!-- Home Section -->
<section class="home" id="home">
    <div class="row">
        <div class="content">
            <h3 style="color: #603F26;">fresh coffee in the morning</h3>
            <button class='button-74' onclick="openLoginModal()" style="width:auto;">Buy Now..</button>
        </div>
        <div class="image">
            <img src="image/coffee1.png" class="main-home-image" alt="">
        </div>
    </div>
    <div class="image-slider">
        <img src="image/coffee1.png" alt="">
        <img src="image/coffee2.png" alt="">
        <img src="image/coffee3.png" alt="">
    </div>
</section>

<!-- About Section -->
<section class="about" id="about">
    <h1 class="heading"> about us <span>why choose us</span> </h1>    
    <div class="row">
        <div class="image">
            <img src="image/about-img.png" alt="">
        </div>
        <div class="content">
            <h3 class="title">what's make our coffee special!</h3>
            <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Esse et commodi, ad, doloremque obcaecati maxime quam minima dolore mollitia saepe quos, debitis incidunt. Itaque possimus adipisci ipsam harum at autem.</p>
            <a href="#" class="btn">read more</a>
            <div class="icons-container">
                <div class="icons">
                    <img src="image/about-icon-1.png" alt="">
                    <h3>quality coffee</h3>
                </div>
                <div class="icons">
                    <img src="image/about-icon-2.png" alt="">
                    <h3>our branches</h3>
                </div>
                <div class="icons">
                    <img src="image/about-icon-3.png" alt="">
                    <h3>free delivery</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section class="menu" id="menu">
    <h1 style="font-size: xxx-large; color: #603F26;text-align: center;">Our Best Seller </h1>
    <div class="box-container">
        <?php for ($i=1; $i<=6; $i++): ?>
        <a href="#" class="box">
            <img src="image/menu-<?php echo $i; ?>.png" alt="">
            <div class="content">
                <h3>our special coffee</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magnam, id.</p>
                <span>$8.99</span>
            </div>
        </a>
        <?php endfor; ?>
    </div>
</section>

<!-- Footer -->
<section class="footer">
    <div class="box-container">
        <div class="box">
            <h3>our branches</h3>
            <a href="#"> <i class="fas fa-arrow-right"></i> Cabadbaran </a>
            <a href="#"> <i class="fas fa-arrow-right"></i> Magallanes </a>
            <a href="#"> <i class="fas fa-arrow-right"></i> RTR </a>
            <a href="#"> <i class="fas fa-arrow-right"></i> Tubay </a>
        </div>
        <div class="box">
            <h3>quick links</h3>
            <a href="#"> <i class="fas fa-arrow-right"></i> home </a>
            <a href="#"> <i class="fas fa-arrow-right"></i> about </a>
            <a href="#"> <i class="fas fa-arrow-right"></i> menu </a>
        </div>
        <div class="box">
            <h3>contact info</h3>
            <a href="#"> <i class="fas fa-phone"></i> 09272308675 </a>
            <a href="#"> <i class="fas fa-envelope"></i> jdomelaurente@gmail.com </a>
        </div>
        <div class="box">
            <h3>follow us</h3>
            <a href="#"> <i class="fab fa-facebook-f"></i> facebook </a>
            <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; 2024 Your Company Name. All Rights Reserved.</p>
    </div>
</section>

<!-- Styles -->
<style>
    #login-button:disabled {
        background-color: #cccccc;
        color: #666666;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    #login-form input:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }
    
    /* Prevent closing modal when locked */
    .modal.locked .close {
        pointer-events: none;
        opacity: 0.3;
        cursor: not-allowed;
    }
</style>

<!-- Scripts -->
<script>
    let countdownInterval = null;
    let isLocked = false;

    // Show/hide password
    document.getElementById('show_log_password').addEventListener('change', function() {
        document.getElementById('log_password').type = this.checked ? 'text' : 'password';
    });

    // Function to open login modal and check lock status
    function openLoginModal() {
        document.getElementById('id01').style.display = 'block';
        checkLockStatus();
    }

    // Prevent closing modal when locked
    document.getElementById('close-modal-btn').addEventListener('click', function(e) {
        if (isLocked) {
            e.preventDefault();
            e.stopPropagation();
            Swal.fire({
                icon: 'warning',
                title: 'Account Locked',
                text: 'Cannot close while account is locked. Please wait for the timer to expire.',
                confirmButtonText: 'OK'
            });
            return false;
        }
        document.getElementById('id01').style.display = 'none';
    });

    // Also prevent clicking outside to close when locked
    window.onclick = function(event) {
        const modal = document.getElementById('id01');
        if (event.target == modal && !isLocked) {
            modal.style.display = "none";
        } else if (event.target == modal && isLocked) {
            Swal.fire({
                icon: 'warning',
                title: 'Account Locked',
                text: 'Cannot close while account is locked. Please wait for the timer to expire.',
                confirmButtonText: 'OK'
            });
        }
    }

    // Prevent page refresh/reload when locked
    window.addEventListener('beforeunload', function(e) {
        if (isLocked) {
            e.preventDefault();
            e.returnValue = ''; // Required for Chrome
            return 'Account is locked. Refreshing will not bypass the lockout timer.';
        }
    });

    // Prevent F5, Ctrl+R, Cmd+R when locked
    document.addEventListener('keydown', function(e) {
        if (isLocked) {
            // F5 or Ctrl+R or Cmd+R
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r') || (e.metaKey && e.key === 'r')) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Refresh Disabled',
                    text: 'Cannot refresh page while account is locked. Please wait for the timer to expire.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        }
    });

    // Function to start countdown timer
    function startCountdown(seconds) {
        const loginButton = document.getElementById('login-button');
        const timerDisplay = document.getElementById('timer-display');
        const modal = document.getElementById('id01');
        const usernameInput = document.getElementById('log_username');
        const passwordInput = document.getElementById('log_password');
        
        // Set locked state
        isLocked = true;
        modal.classList.add('locked');
        
        // Disable button, inputs and form
        loginButton.disabled = true;
        loginButton.textContent = 'LOCKED';
        usernameInput.disabled = true;
        passwordInput.disabled = true;
        
        let remainingTime = seconds;
        
        // Clear any existing interval
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        // Update display immediately
        timerDisplay.textContent = `Account locked. Try again in ${remainingTime} seconds.`;
        
        // Start countdown
        countdownInterval = setInterval(() => {
            remainingTime--;
            
            if (remainingTime > 0) {
                timerDisplay.textContent = `Account locked. Try again in ${remainingTime} seconds.`;
            } else {
                // Timer expired - unlock
                clearInterval(countdownInterval);
                loginButton.disabled = false;
                loginButton.textContent = 'Login';
                usernameInput.disabled = false;
                passwordInput.disabled = false;
                timerDisplay.textContent = '';
                isLocked = false;
                modal.classList.remove('locked');
                
                // Also clear the session lock on server
                fetch('clear_lock.php')
                    .catch(err => console.error('Error clearing lock:', err));
            }
        }, 1000);
    }

    // Function to check lock status
    function checkLockStatus() {
        fetch('check_lock.php')
            .then(response => response.json())
            .then(data => {
                if (data.locked && data.remainingTime > 0) {
                    startCountdown(data.remainingTime);
                }
            })
            .catch(err => console.error('Error checking lock status:', err));
    }

    // AJAX login
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loginButton = document.getElementById('login-button');
        
        // Prevent submission if locked
        if (loginButton.disabled) {
            return;
        }
        
        const form = document.getElementById('login-form');
        const formData = new FormData(form);

        fetch('login.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            const timerDisplay = document.getElementById('timer-display');

            if (data.status === 'success') {
                // Clear any countdown on success
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (data.role === 'admin') {
                        window.location.href = 'admin_dash.php';
                    } else if (['staff', 'supervisor', 'manager'].includes(data.role)) {
                        window.location.href = 'staff_dash.php';
                    } else {
                        window.location.href = 'pos.php';
                    }
                });
            } else if (data.status === 'reset_alert') {
                timerDisplay.innerHTML = data.message + ' <a href="forgot_password.php" style="color:#FFEAC5;">Reset Here</a>';
            } else if (data.status === 'locked') {
                // Start countdown timer
                startCountdown(data.lockTime);
            } else {
                timerDisplay.textContent = data.message;
            }
        })
        .catch(err => console.error('Error:', err));
    });

    // Check if already locked on page load and auto-show modal if locked
    window.addEventListener('DOMContentLoaded', function() {
        fetch('check_lock.php')
            .then(response => response.json())
            .then(data => {
                if (data.locked && data.remainingTime > 0) {
                    // Auto-show modal if locked
                    document.getElementById('id01').style.display = 'block';
                    startCountdown(data.remainingTime);
                }
            })
            .catch(err => console.error('Error checking lock status:', err));
    });
</script>

</body>
</html>