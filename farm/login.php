<?php
require_once '../config/session.php';

// If already logged in, redirect
if (validateSession()) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management System - Login</title>
    <link rel="stylesheet" href="css/design.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">🚜</div>
            <h1>Moonlight Farm</h1>
            <p class="login-subtitle">Production Management System</p>
            
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Enter your password" required>
                </div>
                
                <div class="form-group">
                    <label for="userType">User Type</label>
                    <select id="userType" required>
                        <option value="">Select user type</option>
                        <option value="admin">Administrator</option>
                        <option value="user">Farm Staff</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary btn-login">Login</button>
                
                <div class="login-demo">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Admin - admin@farm.com / admin123</p>
                    <p>User - farmer@farm.com / user123</p>
                </div>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
                Don't have an account? <a href="register.php" style="color: var(--primary-color); text-decoration: none;">Register here</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const userType = document.getElementById('userType').value;
            
            try {
                const response = await fetch('../api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password, userType })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (userType === 'admin') {
                        window.location.href = 'admin-dashboard.php';
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                } else {
                    alert(data.message || 'Login failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });
    </script>
</body>
</html>
