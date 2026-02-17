function startCountdown(seconds) {
    var timerDisplay = document.getElementById('timer-display');
    var inputs = document.querySelectorAll('#login-form input[type="text"], #login-form input[type="password"]');
    var loginButton = document.querySelector('#login-form button[type="submit"]');
    var registerLink = document.querySelector('#login-form p a');

    // Calculate the expiration time and store it
    var expirationTime = Date.now() + seconds * 1000;
    localStorage.setItem('lockExpiration', expirationTime);

    // Make inputs read-only, disable the login button, disable register link, and change input background color
    inputs.forEach(input => {
        input.readOnly = true;
        input.style.backgroundColor = '#d3d3d3'; // Light gray color
    });
    loginButton.disabled = true;
    registerLink.style.pointerEvents = 'none';

    // Show the timer
    timerDisplay.style.display = 'block';

    // Prevent back navigation during countdown
    preventBackNavigation();

    // Update the timer every second
    var timerInterval = setInterval(function () {
        var currentTime = Date.now();
        var remainingTime = Math.floor((expirationTime - currentTime) / 1000);

        if (remainingTime >= 0) {
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;

            // Display the remaining time
            timerDisplay.innerHTML = `Please wait: ${minutes}m ${seconds}s`;
        } else {
            // Clear the interval, hide the timer, and re-enable elements
            clearInterval(timerInterval);
            timerDisplay.style.display = 'none';

            inputs.forEach(input => {
                input.readOnly = false;
                input.style.backgroundColor = ''; // Reset to default background color
            });
            loginButton.disabled = false;
            registerLink.style.pointerEvents = 'auto';

            localStorage.removeItem('lockExpiration'); // Remove lock expiration
            window.onpopstate = null;
        }
    }, 1000);
}

function preventBackNavigation() {
    history.pushState(null, document.title, location.href);
    window.onpopstate = function () {
        history.pushState(null, document.title, location.href);
    };
}



// Prevent Back Navigation
function preventBackNavigation() {
    // Push multiple states to history on page load
    for (let i = 0; i < 100; i++) {
        history.pushState(null, '', location.href);
    }

    // Listen for back button clicks
    window.onpopstate = function () {
        // Immediately push state again to prevent navigation
        history.pushState(null, '', location.href);
    };
}

// Execute functions on page load
window.onload = function () {
    preventBackNavigation();

    // Check if lock exists and restart countdown if needed
    var expirationTime = localStorage.getItem('lockExpiration');
    if (expirationTime) {
        var remainingTime = Math.floor((expirationTime - Date.now()) / 1000);
        if (remainingTime > 0) {
            startCountdown(remainingTime);
        } else {
            localStorage.removeItem('lockExpiration'); // Clean up expired locks
        }
    }
};

// Show password functionality
document.getElementById('show_log_password').addEventListener('change', function () {
    var passwordField = document.getElementById('log_password');
    passwordField.type = this.checked ? 'text' : 'password';
});

// Handle login form submission with AJAX
document.getElementById('login-form').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent traditional form submission

    // Get form data
    var username = document.getElementById('log_username').value;
    var password = document.getElementById('log_password').value;

    // Simple client-side validation
    if (!username || !password) {
        alert('Please enter both username and password');
        return;
    }

    // Prepare the form data for AJAX
    var formData = new FormData();
    formData.append('log_username', username);
    formData.append('log_password', password);

    // Send the AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/login.php', true);

    // Handle response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            // Display error/success message
            var messageContainer = document.querySelector('.container .error');
            messageContainer.innerHTML = response.message;

            if (response.status === 'success') {
                // Redirect on successful login
                window.location.href = 'php/pos.php';
            } else if (response.status === 'reset_alert') {
                // Handle forgot password prompt
                Swal.fire({
                    title: 'Forgot Password?',
                    text: response.message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset it!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'reset_password.html';
                    }
                });
            }

            // Handle lock message with countdown timer
            if (response.message.includes('Try again in')) {
                var lockTimeMatch = response.message.match(/(\d+) seconds/);
                if (lockTimeMatch) {
                    var lockTimeRemaining = parseInt(lockTimeMatch[1]);
                    startCountdown(lockTimeRemaining);
                }
            }

            // Show modal for generic error messages
            if (response.status !== 'success') {
                document.getElementById('id01').style.display = 'block';
            }
        }
    };

    // Send the data
    xhr.send(formData);
});
