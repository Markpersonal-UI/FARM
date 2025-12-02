<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

require 'config/database.php';

$register_error = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $register_error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $register_error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $register_error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = 'Invalid email format';
    } else {
        $existing = fetchSingleResult('SELECT id FROM users WHERE email = ? OR username = ?', [$email, $username], 'ss');
        
        if ($existing) {
            $register_error = 'Email or username already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $result = executeQuery(
                'INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)',
                [$username, $email, $hashed_password, $full_name, $phone, 'user'],
                'ssssss'
            );
            
            if ($result['success']) {
                $register_success = 'Account created successfully! Redirecting to login...';
                header('Refresh: 2; url=index.php');
            } else {
                $register_error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Farm Management System</title>
    
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
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><defs><pattern id="field" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><rect x="0" y="0" width="100" height="100" fill="%232d5016"/><path d="M 0 50 Q 25 45 50 50 T 100 50" stroke="%23468c31" stroke-width="1" fill="none" opacity="0.5"/></pattern></defs><rect x="0" y="0" width="1200" height="600" fill="url(%23field)"/></svg>') repeat;
            opacity: 0.1;
            z-index: -1;
        }

        .register-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 550px;
            width: 100%;
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 40px;
            margin-bottom: 10px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            color: #2d5016;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 13px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #2d5016;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 11px 13px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus {
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
            display: <?php echo $register_error ? 'block' : 'none'; ?>;
        }

        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: <?php echo $register_success ? 'block' : 'none'; ?>;
        }

        .password-meter {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-meter-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            background: #dc3545;
        }

        .strength-text {
            font-size: 12px;
            margin-top: 3px;
            color: #666;
        }

        .register-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #468c31 0%, #2d5016 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(70, 140, 49, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #468c31;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .register-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <!-- ========== HTML SECTION ========== -->
    <div class="register-container">
        <div class="header">
            <div class="logo">🌾</div>
            <h1>Create Farm Account</h1>
            <p class="subtitle">Join our farm management community</p>
        </div>

        <?php if ($register_error): ?>
            <div class="error-box">
                <strong>Error!</strong><br>
                <?php echo htmlspecialchars($register_error); ?>
            </div>
        <?php endif; ?>

        <?php if ($register_success): ?>
            <div class="success-box">
                <strong>Success!</strong><br>
                <?php echo $register_success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" placeholder="Juan Dela Cruz">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" placeholder="09XXXXXXXXX">
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="juandelacruz">
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="juan@farm.com">
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required onkeyup="checkPasswordStrength(this.value)" placeholder="Min 6 characters">
                    <div class="password-meter">
                        <div class="password-meter-bar" id="passwordMeterBar"></div>
                    </div>
                    <div class="strength-text">
                        Strength: <span id="strengthText">Weak</span>
                    </div>
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                </div>
            </div>

            <button type="submit" class="register-button">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="index.php">Sign In</a>
        </div>
    </div>

    <!-- ========== JAVASCRIPT SECTION ========== -->
    <script>
        function checkPasswordStrength(password) {
            let strength = 0;
            const bar = document.getElementById('passwordMeterBar');
            const text = document.getElementById('strengthText');

            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*]/.test(password)) strength++;

            bar.style.width = (strength * 20) + '%';

            if (strength <= 1) {
                bar.style.background = '#dc3545';
                text.textContent = 'Weak';
            } else if (strength === 2) {
                bar.style.background = '#ffc107';
                text.textContent = 'Fair';
            } else if (strength === 3) {
                bar.style.background = '#17a2b8';
                text.textContent = 'Good';
            } else if (strength >= 4) {
                bar.style.background = '#28a745';
                text.textContent = 'Strong';
            }
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters');
                return false;
            }

            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email');
                return false;
            }

            console.log('[v0] Form validation passed, submitting...');
        });
    </script>
</body>
</html>
