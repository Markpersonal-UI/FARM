<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'config/database.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $login_error = 'Email and password are required';
    } else {
        $user = fetchSingleResult('SELECT * FROM users WHERE email = ?', [$email], 's');
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            
            if ($user['role'] == 'admin') {
                header('Location: admin-dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $login_error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management System - Login</title>
    
    <!-- ========== CSS SECTION ========== -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2d5016 0%, #1a3009 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><defs><pattern id="field" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><rect x="0" y="0" width="100" height="100" fill="%232d5016"/><path d="M 0 50 Q 25 45 50 50 T 100 50" stroke="%23468c31" stroke-width="1" fill="none" opacity="0.5"/></pattern></defs><rect x="0" y="0" width="1200" height="600" fill="url(%23field)"/><circle cx="150" cy="100" r="30" fill="%238B4513"/><circle cx="200" cy="80" r="25" fill="%238B4513"/><circle cx="100" cy="120" r="20" fill="%238B4513"/><circle cx="1050" cy="150" r="35" fill="%238B4513"/><circle cx="1100" cy="100" r="28" fill="%238B4513"/></svg>') repeat;
            opacity: 0.1;
            animation: drift 20s infinite linear;
        }

        @keyframes drift {
            0% { transform: translateX(0); }
            100% { transform: translateX(100px); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            padding: 60px 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 15px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            color: #2d5016;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2d5016;
            font-weight: 600;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #468c31;
            box-shadow: 0 0 0 3px rgba(70, 140, 49, 0.1);
        }

        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: <?php echo $login_error ? 'block' : 'none'; ?>;
        }

        .login-button {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #468c31 0%, #2d5016 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(70, 140, 49, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .signup-link a {
            color: #468c31;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #e8f5e9;
            border-left: 4px solid #468c31;
            padding: 15px;
            margin-top: 25px;
            border-radius: 5px;
            font-size: 13px;
            color: #1b5e20;
        }

        .demo-credentials strong {
            color: #2d5016;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #999;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ddd;
        }

        .divider span {
            margin: 0 10px;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 40px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .logo {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- ========== HTML SECTION ========== -->
    <div class="login-container">
        <div class="header">
            <div class="logo">🚜</div>
            <h1>Farm Management</h1>
            <p class="subtitle">Smart Agricultural Production System</p>
        </div>

        <?php if ($login_error): ?>
            <div class="error-box">
                <strong>Login Failed!</strong><br>
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="admin@farm.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="login-button">Sign In to Farm System</button>
        </form>

        <div class="divider"><span>or</span></div>

        <div class="signup-link">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>

        <div class="demo-credentials">
            <strong>📋 Demo Credentials:</strong><br>
            <strong>Admin:</strong> admin@farm.com / admin123<br>
            <strong>Farmer:</strong> farmer@farm.com / user123
        </div>
    </div>

    <!-- ========== JAVASCRIPT SECTION ========== -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }

            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const demoButtons = document.querySelectorAll('.demo-credentials strong');
            if (demoButtons.length > 0) {
                console.log('[v0] Login page loaded successfully');
            }
        });
    </script>
</body>
</html>
