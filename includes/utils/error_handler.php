<?php
/**
 * Error Handler Utility
 * 
 * Provides consistent error handling functions for the application
 */

/**
 * Log an error message to the error log
 * 
 * @param string $message Error message
 * @param string $file File where error occurred
 * @param int $line Line number where error occurred
 * @param string $severity Error severity (error, warning, notice)
 * @return void
 */
function logError($message, $file = '', $line = 0, $severity = 'error') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$severity] $message";
    
    if (!empty($file)) {
        $logMessage .= " in $file";
        
        if ($line > 0) {
            $logMessage .= " on line $line";
        }
    }
    
    // Log to PHP error log
    error_log($logMessage);
    
    // Optionally log to a custom file
    $logDir = __DIR__ . '/../../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/app_errors.log';
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

/**
 * Display a user-friendly error message
 * 
 * @param string $publicMessage Message to display to the user
 * @param string $technicalMessage Technical details (for logging only)
 * @param string $severity Error severity (error, warning, info)
 * @param string $redirectUrl Optional URL to redirect to
 * @return void
 */
function handleError($publicMessage, $technicalMessage = '', $severity = 'error', $redirectUrl = '') {
    // Log the technical error
    if (!empty($technicalMessage)) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? '';
        $line = $backtrace[0]['line'] ?? 0;
        logError($technicalMessage, $file, $line, $severity);
    }
    
    // If redirecting, store error in session
    if (!empty($redirectUrl)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['error_message'] = $publicMessage;
        $_SESSION['error_severity'] = $severity;
        
        header("Location: $redirectUrl");
        exit();
    }
    
    // Otherwise return the error message for direct display
    return $publicMessage;
}

/**
 * Display an error alert if one exists in the session
 * 
 * @return string HTML for the error alert, or empty string if no error
 */
function displaySessionErrors() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $output = '';
    
    if (isset($_SESSION['error_message'])) {
        $severity = $_SESSION['error_severity'] ?? 'error';
        $alertClass = 'alert-danger';
        
        if ($severity === 'warning') {
            $alertClass = 'alert-warning';
        } elseif ($severity === 'info') {
            $alertClass = 'alert-info';
        } elseif ($severity === 'success') {
            $alertClass = 'alert-success';
        }
        
        $output = '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        $output .= htmlspecialchars($_SESSION['error_message']);
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $output .= '</div>';
        
        // Clear the error from the session
        unset($_SESSION['error_message']);
        unset($_SESSION['error_severity']);
    }
    
    return $output;
}

/**
 * Handle database errors consistently
 * 
 * @param mysqli $conn The database connection
 * @param string $publicMessage Message to display to the user
 * @param string $query The SQL query that failed (for logging only)
 * @param string $redirectUrl Optional URL to redirect to
 * @return void
 */
function handleDbError($conn, $publicMessage, $query = '', $redirectUrl = '') {
    $dbError = $conn->error;
    $technicalMessage = "Database error: $dbError";
    
    if (!empty($query)) {
        $technicalMessage .= " in query: $query";
    }
    
    return handleError($publicMessage, $technicalMessage, 'error', $redirectUrl);
}

/**
 * Safely output HTML content with proper escaping
 * 
 * @param string $text Text to sanitize and output
 * @param bool $echo Whether to echo the result or return it
 * @return string|void The sanitized string if $echo is false
 */
function h($text, $echo = true) {
    $sanitized = htmlspecialchars($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    if ($echo) {
        echo $sanitized;
        return;
    }
    
    return $sanitized;
}

/**
 * Output content as HTML with multi-line support (nl2br)
 * 
 * @param string $text Text to sanitize and format
 * @param bool $echo Whether to echo the result or return it
 * @return string|void The sanitized and formatted string if $echo is false
 */
function hbr($text, $echo = true) {
    $sanitized = nl2br(htmlspecialchars($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    
    if ($echo) {
        echo $sanitized;
        return;
    }
    
    return $sanitized;
}
?>
