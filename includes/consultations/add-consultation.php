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
        logError("CSRF token validation failed in add-consultation.php", __FILE__, __LINE__, 'security');
        $_SESSION['error_message'] = "Erreur de sécurité: session expirée ou requête invalide. Veuillez réessayer.";
        header("Location: $previous_page");
        exit();
    }
    // Check if all required fields are set
    if (
        isset($_POST['patient_id']) && is_numeric($_POST['patient_id']) &&
        isset($_POST['date_consultation']) && 
        isset($_POST['motif']) && 
        isset($_POST['diagnostic']) && 
        isset($_POST['traitement']) && 
        isset($_POST['cout']) && is_numeric($_POST['cout'])
    ) {
        // Get form data
        $patient_id = intval($_POST['patient_id']);
        $date_consultation = trim($_POST['date_consultation']);
        $motif = trim($_POST['motif']);
        $diagnostic = trim($_POST['diagnostic']);
        $traitement = trim($_POST['traitement']);
        $cout = floatval($_POST['cout']);
        
        // Validate data
        $errors = [];
        
        // Validate patient ID
        if ($patient_id <= 0) {
            $errors[] = "ID de patient invalide.";
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
        
        // Check if there are validation errors
        if (!empty($errors)) {
            $_SESSION['error_message'] = "Erreurs de validation: " . implode(", ", $errors);
            header("Location: $previous_page");
            exit();
        }
        
        // Insert new consultation with prepared statement
        $query = "INSERT INTO consultations (patient_id, date_consultation, motif, diagnostic, traitement, cout) 
                  VALUES (?, ?, ?, ?, ?, ?)";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssd", $patient_id, $date_consultation, $motif, $diagnostic, $traitement, $cout);
        $result = $stmt->execute();
        
        if ($result) {
            // Log successful action
            logError("Consultation added successfully for patient ID: $patient_id", __FILE__, __LINE__, 'info');
            
            // Set success message and redirect back to previous page
            $_SESSION['success_message'] = "Consultation ajoutée avec succès.";
            header("Location: $previous_page");
            exit();
        } else {
            // Log database error
            $error = $stmt->error;
            logError("Database error while adding consultation: $error", __FILE__, __LINE__, 'error');
            
            // Set error message and redirect back to previous page
            $_SESSION['error_message'] = "Erreur lors de l'ajout de la consultation. Veuillez réessayer.";
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
