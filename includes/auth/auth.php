<?php
/**
 * Authentication Middleware
 * 
 * Functions to handle user authentication and authorization
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has a specific role
 * 
 * @param string $role The role to check
 * @return bool True if user has the role, false otherwise
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admin has access to everything
    if ($_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    // For other roles, check exact match
    return $_SESSION['user_role'] === $role;
}

/**
 * Require user to be logged in
 * 
 * Redirects to login page if user is not logged in
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Require user to have a specific role
 * 
 * Redirects to login page if user is not logged in or doesn't have the role
 * 
 * @param string $role The required role
 * @return void
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        // Redirect to unauthorized page or dashboard
        header("Location: index.php?error=unauthorized");
        exit();
    }
}
?>
