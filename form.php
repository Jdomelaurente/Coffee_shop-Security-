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
    <title>Kalinga Coffee | Pinoy's Favorite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<header class="formal-header">
    <div class="header-container">
        <a href="index.php" class="formal-logo">
            <div class="logo-text-wrapper">
                <span class="logo-text">KALINGA COFFEE</span>
                <span class="logo-subtext">MASANG KAPE</span>
            </div>
        </a>
        <div class="header-actions">
            <nav class="formal-nav" style="margin-right: 20px;">
                <a href="index.php">HOME</a>
            </nav>
            <div id="menu-btn" class="fas fa-bars"></div>
        </div>
    </div>
</header>

    <div class="signup">
        <form id="form" method="POST" action="actions/signup.php">
            <!-- Step Indicators -->
            <div class="indicators">
                <div class="step-indicator">
                    <span class="step-num">1</span>
                    <span class="step-label">Personal</span>
                </div>
                <div class="step-indicator">
                    <span class="step-num">2</span>
                    <span class="step-label">Security</span>
                </div>
                <div class="step-indicator">
                    <span class="step-num">3</span>
                    <span class="step-label">Address</span>
                </div>
            </div>

            <!-- STEP 1: Personal Information -->
            <fieldset class="form-step active" style="border: none; padding: 0; margin: 0;">
                <legend style="display: none;">Personal Information</legend>
                <h1>Personal Information</h1>
                <div class="row">
                    <div class="input">
                      <label for="id_number">ID Number<span class="hash">*</span></label>
                      <input maxlength="9" type="text" id="id_number" name="id_number" placeholder="xxxx-xxxx">
                      <div id="error_id" class="error"></div>
                    </div>
                    <div class="input">
                      <label for="username">Username<span class="hash">*</span></label>
                      <input maxlength="20" minlength="4" type="text" id="username" name="username" placeholder="Choose a username" autocomplete="username">
                      <div id="error_username" class="error"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="input">
                      <label for="firstName">First Name<span class="hash" >*</span></label>
                      <input maxlength="15" type="text" id="firstName" name="firstName" placeholder="Ex:June Dominic">
                      <div class="error"></div>
                    </div>
                    <div class="input">
                      <label for="middleName">
                        Middle Name (<span class="optional">optional</span>)
                      </label>
                      <input maxlength="15"  type="text" id="middleName" name="middleName" placeholder="Ex: Ganancial">
                      <div class="error"></div>
                    </div>
                    <div class="input">
                      <label for="lastName">Last Name<span class="hash" >*</span></label>
                      <input maxlength="15"  type="text" id="lastName" name="lastName"  placeholder="Ex:Laurente">
                      <div class="error"></div>
                    </div>
                    <div class="input">
                    <label for="extensionName">
                      Ext. (<span class="optional">optional</span>)
                    </label>
                    <input maxlength="3" type="text" id="extensionName" name="extensionName" placeholder="Jr">
                    <div class="error"></div>
                  </div>
                </div>

                <div class="row">
                    <div class="input">
                      <label for="dob">Date of Birth<span class="hash">*</span></label>
                      <input type="date" id="dob" name="dob" onchange="updateFromDOB()">
                      <div class="error" id="dobError" style="color: red;"></div>
                    </div>
                    <div class="input">
                      <label for="age">Age<span class="hash">*</span></label>
                      <input type="number" id="age" name="age" placeholder="Must be 18+" min="1" readonly>
                      <div class="error" id="ageError" style="color: red;"></div>
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
                      <input maxlength="11" type="text" id="contact" name="contact" placeholder="09xxxxxxxxx">
                      <div class="error"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="input" style="flex: 2;">
                      <label for="email">Email<span class="hash" >*</span></label>
                      <input maxlength="30" type="text" id="email" name="email" placeholder="Ex:jdomelaurente@gmail.com" autocomplete="email">
                      <div id="error_email" class="error"></div>
                    </div>
                    <div class="input">
                      <label for="password">Password<span class="hash" >*</span></label>
                      <div class="password-wrapper">
                        <input type="password" id="password1" name="password1" autocomplete="new-password" required>
                        <i class="fas fa-eye toggle-eye" onclick="toggleGenericPass('password1', this)"></i>
                      </div>
                      <div id="error_password" class="error"></div>
                      <p id="message"><span id="strength"></span></p>
                    </div>
                    <div class="input">
                      <label for="password2">Confirm Password<span class="hash" >*</span></label>
                      <div class="password-wrapper">
                        <input type="password" id="password2" name="password2" autocomplete="new-password" required>
                        <i class="fas fa-eye toggle-eye" onclick="toggleGenericPass('password2', this)"></i>
                      </div>
                      <div class="error"></div>
                    </div>
                </div>
                <div class="step-buttons">
                    <button type="button" class="next-btn" onclick="nextStep()">Next <i class="fas fa-arrow-right"></i></button>
                </div>
            </fieldset>

            <!-- STEP 2: Security Verification -->
            <fieldset class="form-step" style="border: none; padding: 0; margin: 0;">
                <legend style="display: none;">Security Verification</legend>
                <h1>Security Verification</h1>
                <p style="margin-bottom: 20px; font-size: 1.4rem; color: var(--main-color); text-align: center;">These questions will help secure your account.</p>
                
                <div class="row">
                    <div class="input">
                        <label for="question1">Security Question 1<span class="hash">*</span></label>
                        <select name="question1" id="question1" required>
                            <option value="" disabled selected>Select a question</option>
                            <option value="pet">What was the name of your first pet?</option>
                            <option value="city">In what city were you born?</option>
                            <option value="school">What was the name of your elementary school?</option>
                        </select>
                        <div class="password-wrapper" style="margin-top: 10px;">
                            <input type="password" name="answer1" id="ans1" placeholder="Your answer" required>
                            <i class="fas fa-eye toggle-eye" onclick="toggleSecurityAnswer('ans1', this)"></i>
                        </div>
                        <div class="error"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="input">
                        <label for="question2">Security Question 2<span class="hash">*</span></label>
                        <select name="question2" id="question2" required>
                            <option value="" disabled selected>Select a question</option>
                            <option value="car">What was the make of your first car?</option>
                            <option value="mother">What is your mother's maiden name?</option>
                            <option value="book">What is your favorite book?</option>
                        </select>
                        <div class="password-wrapper" style="margin-top: 10px;">
                            <input type="password" name="answer2" id="ans2" placeholder="Your answer" required>
                            <i class="fas fa-eye toggle-eye" onclick="toggleSecurityAnswer('ans2', this)"></i>
                        </div>
                        <div class="error"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="input">
                        <label for="question3">Security Question 3<span class="hash">*</span></label>
                        <select name="question3" id="question3" required>
                            <option value="" disabled selected>Select a question</option>
                            <option value="color">What is your favorite color?</option>
                            <option value="job">What was your first job?</option>
                            <option value="hobby">What is your favorite hobby?</option>
                        </select>
                        <div class="password-wrapper" style="margin-top: 10px;">
                            <input type="password" name="answer3" id="ans3" placeholder="Your answer" required>
                            <i class="fas fa-eye toggle-eye" onclick="toggleSecurityAnswer('ans3', this)"></i>
                        </div>
                        <div class="error"></div>
                    </div>
                </div>

                <div class="step-buttons">
                    <button type="button" class="back-btn" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="button" class="next-btn" onclick="nextStep()">Next <i class="fas fa-arrow-right"></i></button>
                </div>
            </fieldset>

            <!-- STEP 3: Address Information -->
            <fieldset class="form-step" style="border: none; padding: 0; margin: 0;">
                <legend style="display: none;">Address Information</legend>
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
                <div class="step-buttons">
                    <button type="button" class="back-btn" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="submit" id="signup-btn" name="signup-btn">SIGN-UP <i class="fas fa-check"></i></button>
                </div>
            </fieldset>
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
            <a href="index.php"> <i class="fas fa-arrow-right"></i> home </a>
            <a href="#about"> <i class="fas fa-arrow-right"></i> about </a>
            <a href="#menu"> <i class="fas fa-arrow-right"></i> menu </a>
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
        <p>&copy; <?php echo date('Y'); ?> Kalinga Coffee. All Rights Reserved.</p>
    </div>
</section>
<!-- footer -->

<!-- Link to your JavaScript file -->
<script src="assets/js/java.js?v=<?php echo time(); ?>" ></script>

</body>
</html>
