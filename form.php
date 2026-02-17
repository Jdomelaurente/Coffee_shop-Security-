<?php
session_start();  // Start the session

// Check if the user is logged in, if yes, redirect to pos.php
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");  // Redirect to pos.php if logged in
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
<!-- header section starts  -->
<header class="header">

    <div id="menu-btn" class="fas fa-bars"></div>

    <a href="#" style="margin-right: 72%; color: #FFEAC5;" class="logo" > coffee <i class="fas fa-mug-hot"></i> </a>

    <nav class="navbar">
        <a href="#about"> </a>
        <a href="#menu"> </a>
        <a href="index.php">HOME</a>
    </nav>

</header>

    <div class="signup">
        <form id="form" method="POST" action="signup.php">
            <div class="personal">
                <h1>Personal Information</h1>
                <div class="row">
                    <div class="input">
                    <label for="id_number">ID Number<span class="hash">*</span></label>
                      <input maxlength="9" type="text" id="id_number" name="id_number" placeholder="xxxx-xxxx">
                      <div id="error_id" class="error"></div>
                      
                    </div>
                    <div class="input">
                      <label for="firstName">First Name<span class="hash" >*</span></label>
                      <input maxlength="15" type="text" id="firstName" name="firstName" placeholder="Ex:June Dominic">
                      <div class="error"></div>
                    </div>
                    <div class="input">
                      <label for="lastName">Last Name<span class="hash" >*</span></label>
                      <input maxlength="15"  type="text" id="lastName" name="lastName"  placeholder="Ex:Laurente">
                      <div class="error"></div>
                    </div>
                    <div class="input">
                      <label for="middleName">
                        Middle Name (<span class="optional">optional</span>)
                      </label>
                      <input maxlength="15"  type="text" id="middleName" name="middleName" placeholder="Ex: Ganancial">
                      <div class="error"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="input">
                    <label for="age">Age<span class="hash">*</span></label>
                    <input style="width:220px;" type="number" id="age" name="age" placeholder="Ex: Must be 18 or above" min="1" readonly>
                    <div class="error" id="ageError" style="color: red;"></div>
                    </div>
                    <div class="input">
                    <label for="extensionName">
                      Extension Name (<span class="optional">optional</span>)
                    </label>
                    <input maxlength="3" type="text" id="extensionName" name="extensionName" placeholder="Ex: Jr">
                    <div class="error"></div>
                  </div>

                    <div class="input">
                      <label for="sex">Sex:<span class="hash" >*</span></label>
                      <select name="sex" id="sex" >
                        <option value="" disabled selected>Select:</option>
                        <option>Male</option>
                        <option>Female</option>
                      </select>
                      <div class="error"></div>
                    </div>
                    <div class="input">
                      <label for="contact">Contact Number<span class="hash" >*</span></label>
                      <input maxlength="11" type="text" id="contact" name="contact" placeholder="Ex:09272308675">
                      <div class="error"></div>
                    </div>
                </div>
                <div class="row">
                  <div class="row">
                      <div class="input">
                      <label for="dob">Date of Birth<span class="hash">*</span></label>
                      <input style="width: 240px;" type="date" id="dob" name="dob" onchange="updateFromDOB()">
                      <div class="error" id="dobError" style="color: red;"></div>
                      </div>
                  </div>
                  
                    <div class="input">
                      <label for="email">Email<span class="hash" >*</span></label>
                      <input maxlength="30" type="text" id="email" name="email" placeholder="Ex:Jdomelaurente@gmail.com">
                      <div id="error_email" class="error"></div>
                    </div>
                    <div class="input">
                      <label for="password">Password<span class="hash" >*</span></label>
                      <input type="password" id="password1" name="password1" required>
                      <input type="checkbox" id="showPassword1"> Show Password
                      <div id="error_password" class="error"></div>
                      <p id="message"><span id="strength"></span></p>
                  </div>
                  <div class="input">
                      <label for="password2">Confirm Password<span class="hash" >*</span></label>
                      <input type="password" id="password2" name="password2" required>
                      <input type="checkbox" id="showPassword2"> Show Password
                      <div class="error"></div>
                      
                  </div>
                  
                  
                </div>
                <div class="verification-section">
                    <h1>Security Verification</h1>
                    <p style="margin-bottom: 15px; font-size: 1.2rem; color: red;">These questions will help secure your account.</p>
                    
                    <div class="row">
                        <div class="input">
                            <label for="question1">Security Question 1<span class="hash">*</span></label>
                            <select name="question1" id="question1" required>
                                <option value="" disabled selected>Select a question</option>
                                <option value="pet">What was the name of your first pet?</option>
                                <option value="city">In what city were you born?</option>
                                <option value="school">What was the name of your elementary school?</option>
                            </select>
                            <input type="text" name="answer1" placeholder="Your answer" required>
                            <div class="error"></div>
                        </div>

                        <div class="input">
                            <label for="question2">Security Question 2<span class="hash">*</span></label>
                            <select name="question2" id="question2" required>
                                <option value="" disabled selected>Select a question</option>
                                <option value="car">What was the make of your first car?</option>
                                <option value="mother">What is your mother's maiden name?</option>
                                <option value="book">What is your favorite book?</option>
                            </select>
                            <input type="text" name="answer2" placeholder="Your answer" required>
                            <div class="error"></div>
                        </div>

                        <div class="input">
                            <label for="question3">Security Question 3<span class="hash">*</span></label>
                            <select name="question3" id="question3" required>
                                <option value="" disabled selected>Select a question</option>
                                <option value="color">What is your favorite color?</option>
                                <option value="job">What was your first job?</option>
                                <option value="hobby">What is your favorite hobby?</option>
                            </select>
                            <input type="text" name="answer3" placeholder="Your answer" required>
                            <div class="error"></div>
                        </div>
                    </div>
                </div>
                
                <div class="address">
                    <h1>Address Information</h1>
                    <div class="row">
                        <div class="input">
                          <label for="purok">Purok<span class="hash" >*</span></label>
                          <input maxlength="20"  type="text" id="purok" name="purok">
                          <div class="error"></div>
                        </div>
                        <div class="input">
                          <label for="barangay">Barangay<span class="hash" >*</span></label>
                          <input maxlength="20"  type="text" id="barangay" name="barangay">
                          <div class="error"></div>
                        </div>
                        <div class="input">
                          <label for="cityMunicipality">City/Municipality<span class="hash" >*</span></label>
                          <input maxlength="20"  type="text" id="cityMunicipality" name="cityMunicipality">
                          <div class="error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input">
                          <label for="province">Province<span class="hash" >*</span></label>
                          <input maxlength="20"  type="text" id="province" name="province">
                          <div class="error"></div>
                        </div>
                        <div class="input">
                          <label for="country">Country<span class="hash" >*</span></label>
                          <input maxlength="20"  type="text" id="country" name="country">
                          <div class="error"></div>
                        </div>
                        <div class="input">
                          <label for="zipCode">Zip Code<span class="hash" >*</span></label>
                          <input maxlength="4" type="text" id="zipCode" name="zipCode">
                          <div class="error"></div>
                        </div>
                    </div>
                    <div class="signup-btn">
                        <button type="submit" id="submit" name="submit">SIGN-UP</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    

    
<!-- footer -->

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
          <h3>contact info</h3>
          <a href="#"> <i class="fab fa-facebook-f"></i> facebook </a>
          <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
      </div>

  </div>

  <div class="copyright">
    <p>&copy; 2024 Your Company Name. All Rights Reserved.</p>
</div>

</section>
<!-- footer -->

<!-- Link to your JavaScript file -->
<script src="javascript/java.js" ></script>
<script>
document.getElementById('submit').addEventListener('click', function (e) {
    e.preventDefault(); // Prevent form submission

    let isValid = true; // Initialize the isValid variable

    const form = document.getElementById('form');
    const formData = new FormData(form);

    fetch('signup.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json()) // Get JSON response
    .then(data => {
        // Handle different error messages or success message
        let errorFound = false;

        // Check for ID error
        if (data.status === 'error' && data.message_id) {
            document.getElementById('error_id').textContent = data.message_id;
            isValid = false; // Set isValid to false if there's an error
        } else {
            document.getElementById('error_id').textContent = '';
        }

        // Check for email error
        if (data.status === 'error' && data.message_email) {
            document.getElementById('error_email').textContent = data.message_email;
            isValid = false; // Set isValid to false if there's an error
        } else {
            document.getElementById('error_email').textContent = '';
        }

        // Check for password error
        if (data.status === 'error' && data.message_password) {
            document.getElementById('error_password').textContent = data.message_password;
            isValid = false; // Set isValid to false if there's an error
        } else {
            document.getElementById('error_password').textContent = '';
        }

        // If there are no errors (isValid is true), show success message and redirect
        if (isValid && data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message, // Display the success message from the PHP response
                confirmButtonText: 'OK',
            }).then(() => {
                window.location.href = 'pos.php'; // Redirect to the success page
            });
        } else {
            // If there were errors, do not allow the form to submit
            return isValid;
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log any network or server error
    });
});
</script>

</body>
</html>
