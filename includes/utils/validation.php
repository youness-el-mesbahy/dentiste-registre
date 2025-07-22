<?php
/**
 * Validation Utility
 * 
 * Provides functions for validating user inputs
 */

/**
 * Validate that required fields are not empty
 * 
 * @param array $fields Associative array of field names and values
 * @param array $required Array of required field names
 * @return array Array of error messages, empty if no errors
 */
function validateRequired($fields, $required) {
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($fields[$field]) || trim($fields[$field]) === '') {
            $errors[$field] = "Le champ est obligatoire";
        }
    }
    
    return $errors;
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate date format and check if it's a valid date
 * 
 * @param string $date Date in format YYYY-MM-DD
 * @return bool True if valid, false otherwise
 */
function validateDate($date) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $parts = explode('-', $date);
        return checkdate($parts[1], $parts[2], $parts[0]);
    }
    return false;
}

/**
 * Validate phone number format (allowing international formats)
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    // Basic check for numeric with optional +, -, (, ) and spaces
    return preg_match('/^[0-9\s\(\)\+\-]{8,20}$/', $phone) === 1;
}

/**
 * Validate file type is allowed
 * 
 * @param string $fileType MIME type of the file
 * @param array $allowedTypes Array of allowed MIME types
 * @return bool True if allowed, false otherwise
 */
function validateFileType($fileType, $allowedTypes) {
    return in_array($fileType, $allowedTypes);
}

/**
 * Validate file size is within limit
 * 
 * @param int $fileSize Size of the file in bytes
 * @param int $maxSize Maximum allowed size in bytes
 * @return bool True if within limit, false otherwise
 */
function validateFileSize($fileSize, $maxSize) {
    return $fileSize <= $maxSize;
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @param int $minLength Minimum required length
 * @return bool True if strong enough, false otherwise
 */
function validatePassword($password, $minLength = 8) {
    // Check minimum length
    if (strlen($password) < $minLength) {
        return false;
    }
    
    // Check for at least one uppercase letter, one lowercase letter, and one number
    $hasUppercase = preg_match('/[A-Z]/', $password) === 1;
    $hasLowercase = preg_match('/[a-z]/', $password) === 1;
    $hasNumber = preg_match('/[0-9]/', $password) === 1;
    
    return $hasUppercase && $hasLowercase && $hasNumber;
}

/**
 * Validate Moroccan CIN format
 * 
 * @param string $cin CIN to validate
 * @return bool True if valid, false otherwise
 */
function validateCIN($cin) {
    // CIN format: one or two letters followed by 6 digits
    return preg_match('/^[A-Za-z]{1,2}[0-9]{6}$/', $cin) === 1;
}

/**
 * Display form validation errors
 * 
 * @param array $errors Array of error messages
 * @return string HTML for displaying errors
 */
function displayValidationErrors($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $html = '<div class="alert alert-danger" role="alert">';
    $html .= '<ul class="mb-0">';
    
    foreach ($errors as $field => $message) {
        $html .= '<li>' . htmlspecialchars($message) . '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    
    return $html;
}
?>
