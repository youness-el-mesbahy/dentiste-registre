<?php
session_start();
// Include database connection and utilities
include "../../connection.php";
include "../../includes/utils/error_handler.php";
include "../../includes/utils/validation.php";
include "../../includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Get the previous page URL
$previous_page = $_SERVER['HTTP_REFERER'] ?? '../../dashboard.php';

// Verify CSRF token before processing deletion request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in delete-consultation.php", __FILE__, __LINE__, 'security');
        $_SESSION['error_message'] = "Erreur de sécurité: session expirée ou requête invalide. Veuillez réessayer.";
        header("Location: $previous_page");
        exit();
    }
}

// Check if consultation ID and patient ID are set and numeric
if (isset($_POST['consult_id']) && is_numeric($_POST['consult_id']) && isset($_POST['patient_id']) && is_numeric($_POST['patient_id'])) {
    $consult_id = intval($_POST['consult_id']);
    $patient_id = intval($_POST['patient_id']);
    
    // Verify consultation exists and belongs to the patient
    $verify_query = "SELECT * FROM consultations WHERE id = ? AND patient_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $consult_id, $patient_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        // Consultation doesn't exist or doesn't belong to this patient
        logError("Attempted to delete consultation that doesn't exist or doesn't belong to the patient. Consult ID: $consult_id, Patient ID: $patient_id", __FILE__, __LINE__, 'security');
        $_SESSION['error_message'] = "La consultation spécifiée n'existe pas ou ne peut pas être supprimée.";
        header("Location: $previous_page");
        exit();
    }
    
    // Delete the consultation from the database with prepared statement
    $query = "DELETE FROM consultations WHERE id = ? AND patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $consult_id, $patient_id);
    $result = $stmt->execute();
    
    if ($result) {
        // Log successful deletion
        logError("Consultation ID: $consult_id deleted successfully for patient ID: $patient_id", __FILE__, __LINE__, 'info');
        
        // Deletion successful
        $_SESSION['success_message'] = "Consultation supprimée avec succès.";
        header("Location: $previous_page");
        exit();
    } else {
        // Log error
        $error = $stmt->error;
        logError("Database error while deleting consultation: $error", __FILE__, __LINE__, 'error');
        
        // Error in deletion
        $_SESSION['error_message'] = "Erreur lors de la suppression de la consultation. Veuillez réessayer.";
        header("Location: $previous_page");
        exit();
    }
} else {
    // Invalid or missing parameters
    $_SESSION['error_message'] = "Paramètres invalides ou manquants.";
    header("Location: $previous_page");
    exit();
}
?>
