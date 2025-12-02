<?php
require_once '../config/session.php';

// If already logged in, redirect
if (validateSession()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management System - Register</title>
    <link rel="stylesheet" href="css/design.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box" style="max-width: 500px;">
            <div class="login-logo">🚜</div>
            <h1>Create Account</h1>
            <p class="login-subtitle">Join our farm management system</p>
            
            <form id="registerForm" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" placeholder="Enter your phone number">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Enter password (min 6 chars)" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" placeholder="Confirm password" required>
                </div>
                
                <button type="submit" class="btn-primary btn-login">Register</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
                Already have an account? <a href="login.php" style="color: var(--primary-color); text-decoration: none;">Login here</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const fullName = document.getElementById('fullName').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            try {
                const response = await fetch('../api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, email, fullName, phone, password, confirmPassword })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'login.php';
                } else {
                    alert(data.message || 'Registration failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });
    </script>
</body>
</html>
