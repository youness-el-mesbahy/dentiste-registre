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
$previous_page = $_SERVER['HTTP_REFERER'] ?? '../../patient-dashboard.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in edit-consultation.php", __FILE__, __LINE__, 'security');
        $_SESSION['error_message'] = "Erreur de sécurité: session expirée ou requête invalide. Veuillez réessayer.";
        header("Location: $previous_page");
        exit();
    }
    // Check if all required fields are set
    if (
        isset($_POST['patient_id']) && is_numeric($_POST['patient_id']) &&
        isset($_POST['consult_id']) && is_numeric($_POST['consult_id']) &&
        isset($_POST['date_consultation']) && 
        isset($_POST['motif']) && 
        isset($_POST['diagnostic']) && 
        isset($_POST['traitement']) && 
        isset($_POST['cout']) && is_numeric($_POST['cout'])
    ) {
        // Get form data
        $patient_id = intval($_POST['patient_id']);
        $consult_id = intval($_POST['consult_id']);
        $date_consultation = trim($_POST['date_consultation']);
        $motif = trim($_POST['motif']);
        $diagnostic = trim($_POST['diagnostic']);
        $traitement = trim($_POST['traitement']);
        $cout = floatval($_POST['cout']);
        
        // Validate data
        $errors = [];
        
        // Validate IDs
        if ($patient_id <= 0) {
            $errors[] = "ID de patient invalide.";
        }
        
        if ($consult_id <= 0) {
            $errors[] = "ID de consultation invalide.";
        }
        
        // Validate date format
        if (empty($date_consultation) || !strtotime($date_consultation)) {
            $errors[] = "Format de date invalide.";
        }
        
        // Validate required text fields
        if (empty($motif)) {
            $errors[] = "Le motif de consultation est requis.";
        }
        
        if (empty($diagnostic)) {
            $errors[] = "Le diagnostic est requis.";
        }
        
        if (empty($traitement)) {
            $errors[] = "Le traitement est requis.";
        }
        
        // Validate cost
        if ($cout < 0) {
            $errors[] = "Le coût ne peut pas être négatif.";
        }
        
        // Verify that the consultation belongs to the specified patient
        $check_query = "SELECT * FROM consultations WHERE id = ? AND patient_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $consult_id, $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $errors[] = "La consultation spécifiée n'appartient pas au patient indiqué.";
            logError("Attempted to edit consultation that doesn't belong to specified patient. Consult ID: $consult_id, Patient ID: $patient_id", __FILE__, __LINE__, 'security');
        }
        
        // Check if there are validation errors
        if (!empty($errors)) {
            $_SESSION['error_message'] = "Erreurs de validation: " . implode(", ", $errors);
            header("Location: $previous_page");
            exit();
        }
        
        // Update consultation with prepared statement
        $query = "UPDATE consultations 
                  SET date_consultation = ?, 
                      motif = ?, 
                      diagnostic = ?, 
                      traitement = ?, 
                      cout = ? 
                  WHERE id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssdii", $date_consultation, $motif, $diagnostic, $traitement, $cout, $consult_id, $patient_id);
        $result = $stmt->execute();
        
        if ($result) {
            // Log successful action
            logError("Consultation ID: $consult_id updated successfully for patient ID: $patient_id", __FILE__, __LINE__, 'info');
            
            // Set success message and redirect back to previous page
            $_SESSION['success_message'] = "Consultation mise à jour avec succès.";
            header("Location: $previous_page");
            exit();
        } else {
            // Log database error
            $error = $stmt->error;
            logError("Database error while updating consultation: $error", __FILE__, __LINE__, 'error');
            
            // Set error message and redirect back to previous page
            $_SESSION['error_message'] = "Erreur lors de la mise à jour de la consultation. Veuillez réessayer.";
            header("Location: $previous_page");
            exit();
        }
    } else {
        // Missing required fields
        $_SESSION['error_message'] = "Champs obligatoires manquants.";
        header("Location: $previous_page");
        exit();
    }
} else {
    // Not a POST request
    header("Location: $previous_page");
    exit();
}
?>
