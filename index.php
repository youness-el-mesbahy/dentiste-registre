<?php
session_start();
include "includes/auth/auth.php";

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Redirect to patients list
header("Location: dashboard.php");
exit();
?>