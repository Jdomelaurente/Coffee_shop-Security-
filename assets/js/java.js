// Generic toggle for eye icons (used in dashboards and security answers)
function toggleGenericPass(inputId, icon) {
    const field = document.getElementById(inputId);
    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Map toggleSecurityAnswer to toggleGenericPass for backward compatibility
function toggleSecurityAnswer(inputId, icon) {
    toggleGenericPass(inputId, icon);
}

// MULTI-STEP FORM LOGIC
let currentStep = 0;

function showStep(n) {
    const steps = document.getElementsByClassName("form-step");
    const indicators = document.getElementsByClassName("step-indicator");
    
    // Hide all steps
    for (let i = 0; i < steps.length; i++) {
        steps[i].classList.remove("active");
    }
    
    // Show current step
    steps[n].classList.add("active");
    
    // Update indicators
    for (let i = 0; i < indicators.length; i++) {
        indicators[i].classList.remove("active");
        if (i < n) {
            indicators[i].classList.add("finish");
        } else {
            indicators[i].classList.remove("finish");
        }
    }
    indicators[n].classList.add("active");

}

async function validateCurrentStep() {
    const steps = document.querySelectorAll(".form-step");
    const currentStepEl = steps[currentStep];
    const inputsToVerify = currentStepEl.querySelectorAll("input, select");
    
    let stepValid = true;
    
    for (const input of inputsToVerify) {
        if (!(await validateSingleInput(input))) {
            stepValid = false;
        }
    }

    return stepValid;
}

async function nextStep() {
    const steps = document.getElementsByClassName("form-step");
    if (!(await validateCurrentStep())) return;

    if (currentStep < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function prevStep() {
    if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const steps = document.getElementsByClassName("form-step");
    if (steps.length > 0) {
        showStep(currentStep);
    }

    // Real-time validation listeners
    const allFormInputs = document.querySelectorAll('#form input, #form select');
    allFormInputs.forEach(input => {
        // Validation on blur and input
        input.addEventListener('input', () => validateSingleInput(input));
        input.addEventListener('blur', () => validateSingleInput(input));
        
        // Auto-format ID number
        if (input.id === 'id_number') {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) {
                    value = value.slice(0, 4) + '-' + value.slice(4, 8);
                }
                e.target.value = value;
            });
        }
    });
});

async function validateSingleInput(input) {
    const isOptional = input.getAttribute('placeholder')?.toLowerCase().includes('optional') || 
                       input.id === 'middleName' || 
                       input.id === 'extensionName';
    
    // Basic presence check
    if (!isOptional && input.hasAttribute('required') || (!isOptional && input.type !== 'button' && input.type !== 'submit')) {
        if (input.value.trim() === "" && input.type !== 'checkbox' && input.tagName !== 'SELECT') {
            setError(input, "This field is required");
            return false;
        } else if (input.tagName === 'SELECT' && input.value === "") {
            setError(input, "Please select an option");
            return false;
        }
    }

    // Specific format checks
    if (input.id === 'id_number') {
        const val = input.value.trim();
        if (val !== "" && !/^\d{4}-\d{4}$/.test(val)) {
            setError(input, "Format: xxxx-xxxx");
            return false;
        }
        if (val.length === 9) {
            const isAvail = await checkAvailability('id_number', val);
            if (!isAvail) {
                setError(input, "ID Number already registered");
                return false;
            }
        }
    }

    if (input.id === 'username') {
        const val = input.value.trim();
        if (val !== "") {
            if (val.length < 4) {
                setError(input, "Min 4 characters");
                return false;
            } else if (/\s/.test(val)) {
                setError(input, "No spaces allowed");
                return false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(val)) {
                setError(input, "Letters, numbers & underscores only");
                return false;
            }
            if (val.length >= 4) {
                const isAvail = await checkAvailability('username', val);
                if (!isAvail) {
                    setError(input, "Username is already taken");
                    return false;
                }
            }
        }
    }

    if (input.id === 'email') {
        const val = input.value.trim();
        if (val !== "") {
            if (!/^\S+@\S+\.\S+$/.test(val)) {
                setError(input, "Invalid email format");
                return false;
            }
            const isAvail = await checkAvailability('email', val);
            if (!isAvail) {
                setError(input, "Email already in use");
                return false;
            }
        }
    }

    if (input.id === 'password1') {
        const val = input.value;
        if (val !== "" && val.length < 12) {
            setError(input, "Min 12 characters");
            return false;
        }
    }

    if (input.id === 'password2') {
        const p1 = document.getElementById('password1');
        if (input.value !== "" && p1 && input.value !== p1.value) {
            setError(input, "Passwords do not match");
            return false;
        }
    }

    if (input.id === 'contact') {
        const val = input.value.trim();
        if (val !== "" && !/^09\d{9}$/.test(val)) {
            setError(input, "Format: 09xxxxxxxxx (11 digits)");
            return false;
        }
    }

    if (input.value.trim() !== "" || isOptional) {
        setSuccess(input);
    }
    return true;
}

// Password strength checker
document.addEventListener('DOMContentLoaded', function () {
    const pass = document.getElementById("password1");
    const msg = document.getElementById("message");
    const str = document.getElementById("strength");

    if (!pass) return; // Exit if password field doesn't exist on this page

    let timeout;
    pass.addEventListener('input', () => {
        const password = pass.value;
        const hasNumbers = /\d/.test(password);
        const hasLetters = /[a-zA-Z]/.test(password);
        const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        if (msg) msg.style.display = password.length > 0 ? "block" : "none";

        let strength = "Weak Password";
        let color = "red";

        if (password.length >= 8 && hasLetters && hasNumbers && hasSpecialChars) {
            strength = "Strong Password";
            color = "green";
            if (msg) {
                clearTimeout(timeout);
                timeout = setTimeout(() => { msg.style.display = "none"; }, 3000);
            }
        } else if (password.length >= 4 && (hasLetters || hasNumbers)) {
            strength = "Medium Password";
            color = "orange";
            clearTimeout(timeout);
        } else {
            clearTimeout(timeout);
        }

        if (str) {
            str.innerHTML = strength;
            str.style.color = color;
        }
    });
});

// Form validations
const form = document.getElementById('form');
const id_number = document.getElementById('id_number');
const firstName = document.getElementById('firstName');
const lastName = document.getElementById('lastName');
const middleName = document.getElementById('middleName');
const extensionName = document.getElementById('extensionName');
const sex = document.getElementById('sex');
const contact = document.getElementById('contact');
const email = document.getElementById('email');
const password1 = document.getElementById('password1');
const password2 = document.getElementById('password2');
const purok = document.getElementById('purok');
const barangay = document.getElementById('barangay');
const cityMunicipality = document.getElementById('cityMunicipality');
const province = document.getElementById('province');
const country = document.getElementById('country');
const zipCode = document.getElementById('zipCode');
const age = document.getElementById('age');
const dob = document.getElementById('dob');

// Real-time capitalization for name fields
document.addEventListener('DOMContentLoaded', () => {
    const nameFields = ['firstName', 'middleName', 'lastName'];
    nameFields.forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.addEventListener('input', function() {
                let cursorPosition = this.selectionStart;
                let originalValue = this.value;
                
                // Capitalize first letter of each word
                let newValue = this.value.toLowerCase().replace(/(^\w|\s\w)/g, m => m.toUpperCase());
                
                if (originalValue !== newValue) {
                    this.value = newValue;
                    // Restore cursor position
                    this.setSelectionRange(cursorPosition, cursorPosition);
                }
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const signupBtn = document.getElementById('signup-btn');
    if (signupBtn) {
        signupBtn.addEventListener('click', (event) => {
            event.preventDefault();
            if (validateInputs()) {
                const form = document.getElementById('form');
                const formData = new FormData(form);

                fetch('actions/signup.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const errorId = document.getElementById('error_id');
                    const errorEmail = document.getElementById('error_email');
                    const errorPassword = document.getElementById('error_password');

                    if (errorId) errorId.textContent = '';
                    if (errorEmail) errorEmail.textContent = '';
                    if (errorPassword) errorPassword.textContent = '';

                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            confirmButtonText: 'OK',
                        }).then(() => {
                            window.location.href = 'index.php';
                        });
                    } else {
                        if (data.message_id && errorId) errorId.textContent = data.message_id;
                        if (data.message_email && errorEmail) errorEmail.textContent = data.message_email;
                        if (data.message_password && errorPassword) errorPassword.textContent = data.message_password;
                        
                        if (data.message && !data.message_id && !data.message_email && !data.message_password) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to process registration. Please try again later.'
                    });
                });
            }
        });
    }
});

async function checkAvailability(field, value) {
    try {
        const response = await fetch('actions/check_availability.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ field, value })
        });
        const data = await response.json();
        return data.available;
    } catch (e) {
        console.error("Availability check failed", e);
        return true; // Fallback
    }
}

const setError = (element, message) => {
    // Walk up to the nearest .input container (handles nested .password-wrapper)
    const inputControl = element.closest('.input') || element.parentElement;
    const errorDisplay = inputControl ? inputControl.querySelector('.error') : null;

    if (errorDisplay) errorDisplay.innerText = message;
    if (inputControl) {
        inputControl.classList.add('error');
        inputControl.classList.remove('success');
    }
};

const setSuccess = element => {
    // Walk up to the nearest .input container (handles nested .password-wrapper)
    const inputControl = element.closest('.input') || element.parentElement;
    const errorDisplay = inputControl ? inputControl.querySelector('.error') : null;

    if (errorDisplay) errorDisplay.innerText = '';
    if (inputControl) {
        inputControl.classList.add('success');
        inputControl.classList.remove('error');
    }
};

const validateInputs = () => {
    let allStepsValid = true;
    const steps = document.querySelectorAll(".form-step");
    let firstErrorStep = -1;

    // Validate each step
    for (let i = 0; i < steps.length; i++) {
        const originalStep = currentStep;
        currentStep = i;
        if (!validateCurrentStep()) {
            allStepsValid = false;
            if (firstErrorStep === -1) firstErrorStep = i;
        }
        currentStep = originalStep;
    }

    if (!allStepsValid && firstErrorStep !== -1) {
        currentStep = firstErrorStep;
        showStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    return allStepsValid;
};
function updateFromDOB() {
    const dob = document.getElementById("dob").value;
    const ageField = document.getElementById("age");
    const dobError = document.getElementById("dobError");

    if (dob) {
        const dobDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - dobDate.getFullYear();
        const monthDiff = today.getMonth() - dobDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dobDate.getDate())) {
            age--;
        }

        if (age >= 18) {
            ageField.value = age;
            dobError.textContent = "";
        } else {
            ageField.value = "";
            dobError.textContent = "You must be 18 years old or above.";
        }
    } else {
        ageField.value = "";
        dobError.textContent = "";
    }
}

