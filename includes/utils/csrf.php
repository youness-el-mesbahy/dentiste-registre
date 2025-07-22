<?php
/**
 * CSRF Protection Utility
 * 
 * Provides functions for generating and validating CSRF tokens
 */

/**
 * Initialize CSRF protection
 * Ensures the session contains a CSRF token
 * 
 * @return void
 */
function initCSRF() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Generate a CSRF token input field
 * 
 * @return string HTML for the CSRF token input field
 */
function generateCSRFToken() {
    initCSRF();
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Validate the submitted CSRF token
 * 
 * @return bool True if the token is valid, false otherwise
 */
function validateCSRFToken() {
    initCSRF();
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        return false;
    }
    
    return true;
}

/**
 * Verify the CSRF token and handle invalid tokens
 * 
 * @param string $redirectUrl URL to redirect to if token is invalid
 * @return bool True if the token is valid, otherwise redirects and exits
 */
function verifyCSRFToken($redirectUrl = '') {
    if (!validateCSRFToken()) {
        if (function_exists('logError')) {
            logError('CSRF token validation failed', __FILE__, __LINE__, 'security');
        }
        
        if (empty($redirectUrl)) {
            // Default redirect to current page
            $redirectUrl = $_SERVER['PHP_SELF'];
        }
        
        if (function_exists('handleError')) {
            handleError('Session expirée ou requête invalide. Veuillez réessayer.', 
                       'CSRF token validation failed', 'error', $redirectUrl);
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = 'Session expirée ou requête invalide. Veuillez réessayer.';
            header("Location: $redirectUrl");
            exit();
        }
        
        return false;
    }
    
    return true;
}
?>
