<?php
session_start();
// Include database connection
include "../../connection.php";

// Get the previous page URL
$previous_page = $_SERVER['HTTP_REFERER'] ?? '../../dashboard.php';

// Check if patient_id is set and is numeric
if (isset($_POST['patient_id']) && is_numeric($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];
    
    // Prepare and execute the delete query
    $query = "DELETE FROM patients WHERE id = $patient_id";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        // Deletion successful
        $_SESSION['success_message'] = "Patient supprimé avec succès.";
        header("Location: $previous_page");
        exit();
    } else {
        // Error in deletion
        $_SESSION['error_message'] = "Erreur lors de la suppression du patient: " . mysqli_error($conn);
        header("Location: $previous_page");
        exit();
    }
} else {
    // Invalid or missing patient_id
    $_SESSION['error_message'] = "ID patient invalide ou manquant.";
    header("Location: $previous_page");
    exit();
}
?>
