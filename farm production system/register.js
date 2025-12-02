// Form validation and password strength checker
const form = document.getElementById('registrationForm');
const fullNameInput = document.getElementById('fullName');
const emailInput = document.getElementById('email');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirmPassword');
const phoneInput = document.getElementById('phone');
const termsCheckbox = document.getElementById('terms');
const togglePasswordBtn = document.getElementById('togglePassword');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');

// Password strength meter
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

// Validation patterns
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const phonePattern = /^[0-9+\-\s()]{7,}$/;
const usernamePattern = /^[a-zA-Z0-9_]{3,20}$/;

// Toggle password visibility
togglePasswordBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    togglePasswordBtn.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
});

// Real-time password strength indicator
passwordInput.addEventListener('input', (e) => {
    const password = e.target.value;
    const strength = calculatePasswordStrength(password);
    updatePasswordStrength(strength);
});

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    return strength;
}

function updatePasswordStrength(strength) {
    const percentages = [0, 16, 32, 50, 66, 83, 100];
    const colors = ['#ef4444', '#f59e0b', '#f59e0b', '#f59e0b', '#10b981', '#10b981', '#10b981'];
    const texts = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very strong', 'Excellent'];
    
    strengthBar.style.width = percentages[strength] + '%';
    strengthBar.style.backgroundColor = colors[strength];
    strengthText.textContent = texts[strength];
}

// Form validation
function validateForm() {
    let isValid = true;
    clearErrors();
    
    // Full Name validation
    if (!fullNameInput.value.trim()) {
        showFieldError('fullName', 'Full name is required');
        isValid = false;
    } else if (fullNameInput.value.trim().length < 2) {
        showFieldError('fullName', 'Full name must be at least 2 characters');
        isValid = false;
    }
    
    // Email validation
    if (!emailInput.value.trim()) {
        showFieldError('email', 'Email is required');
        isValid = false;
    } else if (!emailPattern.test(emailInput.value.trim())) {
        showFieldError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Username validation
    if (!usernameInput.value.trim()) {
        showFieldError('username', 'Username is required');
        isValid = false;
    } else if (!usernamePattern.test(usernameInput.value.trim())) {
        showFieldError('username', 'Username must be 3-20 characters (letters, numbers, underscore only)');
        isValid = false;
    }
    
    // Password validation
    if (!passwordInput.value) {
        showFieldError('password_reg', 'Password is required');
        isValid = false;
    } else if (passwordInput.value.length < 8) {
        showFieldError('password_reg', 'Password must be at least 8 characters');
        isValid = false;
    }
    
    // Confirm password validation
    if (!confirmPasswordInput.value) {
        showFieldError('confirmPassword', 'Please confirm your password');
        isValid = false;
    } else if (passwordInput.value !== confirmPasswordInput.value) {
        showFieldError('confirmPassword', 'Passwords do not match');
        isValid = false;
    }
    
    // Phone validation
    if (!phoneInput.value.trim()) {
        showFieldError('phone', 'Phone number is required');
        isValid = false;
    } else if (!phonePattern.test(phoneInput.value.trim())) {
        showFieldError('phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    // Terms validation
    if (!termsCheckbox.checked) {
        showFieldError('terms', 'You must agree to the terms and conditions');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(fieldName, message) {
    const errorElement = document.getElementById(`${fieldName}Error`);
    const inputElement = document.getElementById(fieldName);
    
    if (errorElement) {
        errorElement.textContent = message;
    }
    if (inputElement) {
        inputElement.classList.add('error');
    }
}

function clearErrors() {
    document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
    document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
}

// Form submission
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
        showError('Please fix the errors above');
        return;
    }
    
    // Send data to PHP backend
    const formData = new FormData(form);
    
    try {
        const response = await fetch('register.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Account created successfully! Redirecting to login...');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
            form.reset();
            clearErrors();
        } else {
            showError(result.message || 'Registration failed. Please try again.');
        }
    } catch (error) {
        showError('An error occurred. Please try again later.');
        console.error('Error:', error);
    }
});

function showError(message) {
    errorMessage.textContent = message;
    errorMessage.classList.add('show');
    successMessage.classList.remove('show');
    window.scrollTo(0, 0);
}

function showSuccess(message) {
    successMessage.textContent = message;
    successMessage.classList.add('show');
    errorMessage.classList.remove('show');
    window.scrollTo(0, 0);
}

// Clear errors on input
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', () => {
        input.classList.remove('error');
        const errorId = input.id + 'Error';
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = '';
        }
    });
});