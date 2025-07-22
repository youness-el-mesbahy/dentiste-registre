<?php
// Include database connection
include "../../connection.php";

// Get the previous page URL
$previous_page =  $_SERVER['HTTP_REFERER'] ?? "patients-list.php";

// Function to merge query parameters
function mergeQueryParams($url, $newParams) {
    $urlParts = parse_url($url);
    $queryParams = [];
    
    // Parse existing query parameters
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $queryParams);
    }
    
    // Merge with new parameters
    $queryParams = array_merge($queryParams, $newParams);
    
    // Rebuild URL with merged parameters
    $query = http_build_query($queryParams);
    
    // If there were no previous query parameters, add ?
    if (!isset($urlParts['query'])) {
        $url .= '?';
    }
    
    // If there are existing query parameters, add &
    if (isset($urlParts['query'])) {
        $url .= '&';
    }
    
    return $url . $query;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
    $remarque = isset($_POST['remarque']) ? mysqli_real_escape_string($conn, $_POST['remarque']) : '';
    
    // Validate patient ID
    if ($patient_id <= 0) {
        $newUrl = mergeQueryParams($previous_page, ['error' => 'invalid_patient']);
        header("Location: $newUrl");
        exit();
    }
    
    // Update patient remarque
    $query = "UPDATE patients SET remarque = '$remarque' WHERE id = $patient_id";
    
    if (mysqli_query($conn, $query)) {
        // Success
        $newUrl = mergeQueryParams($previous_page, ['success' => 'remarque_updated']);
        header("Location: $newUrl");
        exit();
    } else {
        // Error
        $newUrl = mergeQueryParams($previous_page, ['error' => 'update_failed']);
        header("Location: $newUrl");
        exit();
    }
} else {
    // If not POST request, redirect to previous page
    header("Location: $previous_page");
    exit();
}
?>
