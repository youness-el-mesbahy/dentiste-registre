<?php
session_start();
// Include database connection and utilities
include "connection.php";
include "includes/utils/error_handler.php";
include "includes/utils/validation.php";
include "includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Get the previous page URL
$previous_page =  $_SERVER['HTTP_REFERER'] ?? "patients-list.php";

// Function to merge query parameters without duplicates
function mergeQueryParams($url, $newParams) {
    // Parse URL components
    $urlParts = parse_url($url);
    
    // Parse existing query parameters
    $queryParams = [];
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $queryParams);
    }
    
    // Replace existing parameters with new ones
    $queryParams = array_replace($queryParams, $newParams);
    
    // Rebuild query string
    $newQuery = http_build_query($queryParams);
    
    // Rebuild URL components
    $scheme = isset($urlParts['scheme']) ? $urlParts['scheme'] . '://' : '';
    $host = isset($urlParts['host']) ? $urlParts['host'] : '';
    $port = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
    $path = isset($urlParts['path']) ? $urlParts['path'] : '';
    
    // Build final URL
    $newUrl = $scheme . $host . $port . $path;
    
    // Add query string if it exists
    if ($newQuery) {
        $newUrl .= '?' . $newQuery;
    }
    
    return $newUrl;
}

// Check if form was submitted with file and patient ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_id']) && is_numeric($_POST['patient_id']) && isset($_FILES['document'])) {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in upload-document.php", __FILE__, __LINE__, 'security');
        $newUrl = mergeQueryParams($previous_page, ['error' => 'csrf_error']);
        header("Location: $newUrl");
        exit();
    }
    $patient_id = $_POST['patient_id'];
    
    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/patients/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Check if file was successfully uploaded
    if ($_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $tempName = $_FILES['document']['tmp_name'];
        $originalName = $_FILES['document']['name'];
        $fileType = $_FILES['document']['type'];
        
        // Generate unique filename
        $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
        $newFileName = uniqid('doc_') . '.' . $fileExtension;
        $destination = $uploadDir . $newFileName;
        
        // Move uploaded file to destination
        if (move_uploaded_file($tempName, $destination)) {
            // Insert document info into database
            $query = "INSERT INTO patient_documents (patient_id, file_name, file_type, file_path) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "isss", $patient_id, $newFileName, $fileType, $destination);
            
            if (mysqli_stmt_execute($stmt)) {
                // Success
                $newUrl = mergeQueryParams($previous_page, ['success' => 'document_uploaded']);
                header("Location: $newUrl");
                exit();
            } else {
                // Database error
                $newUrl = mergeQueryParams($previous_page, ['error' => 'db_error']);
                header("Location: $newUrl");
                exit();
            }
        } else {
            // File move error
            $newUrl = mergeQueryParams($previous_page, ['error' => 'file_move_failed']);
            header("Location: $newUrl");
            exit();
        }
    } else {
        // File upload error
        $newUrl = mergeQueryParams($previous_page, [
            'error' => 'upload_failed',
            'message' => $_FILES['document']['error']
        ]);
        header("Location: $newUrl");
        exit();
    }
} else {
    // Invalid form submission
    $newUrl = mergeQueryParams($previous_page, ['error' => 'invalid_form']);
    header("Location: $newUrl");
    exit();
}
?>
