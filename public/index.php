<?php
require_once '../config/session.php';

// If already logged in, redirect to appropriate dashboard
if (validateSession()) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

// Redirect to login
header('Location: login.php');
exit();
?>
