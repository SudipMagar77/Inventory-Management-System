<?php
include 'includes/config.php';

// Redirect to dashboard if logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: pages/dashboard.php");
    exit();
} else {
    header("Location: auth/login.php");
    exit();
}
