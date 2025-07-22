<?php
session_start();
include "../../connection.php";
include "../auth/auth.php";
include "../../includes/utils/error_handler.php";
include "../../includes/utils/validation.php";
include "../../includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Require user to be logged in
requireLogin();
$previous_page = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in add-appointment.php", __FILE__, __LINE__, 'security');
        $redirect_url = $previous_page . '?error=csrf_error';
        header("Location: $redirect_url");
        exit();
    }
    // Validate and sanitize inputs
    $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '09:00';
    
    // Combine date and time
    if (!empty($appointment_date)) {
        $appointment_date = $appointment_date . ' ' . $appointment_time . ':00';
    }
    
    // Handle custom duration or selected preset
    if (isset($_POST['custom_duration']) && !empty($_POST['custom_duration'])) {
        $duration_minutes = intval($_POST['custom_duration']);
    } else {
        $duration_minutes = isset($_POST['duration_minutes']) ? intval($_POST['duration_minutes']) : 30;
    }
    
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : '';
    $user_id = $_SESSION['user_id'] ?? 0;
    
    // Comprehensive validation
    $errors = [];
    
    // Validate patient ID
    if ($patient_id <= 0) {
        $errors[] = "Patient non valide.";
    }
    
    // Validate appointment date
    if (empty($appointment_date)) {
        $errors[] = "Date de rendez-vous non valide.";
    } else {
        // Check if appointment date is in the future
        $appointmentDateTime = new DateTime($appointment_date);
        $now = new DateTime();
        
        if ($appointmentDateTime <= $now) {
            $errors[] = "La date du rendez-vous doit être dans le futur.";
        }
        
        // Hour validation removed - using fixed time value
    }
    
    // Duration validation removed - using fixed duration value
    // Ensure duration is set to default 30 minutes
    $duration_minutes = 30;
    
    // Check if user is authenticated
    if ($user_id <= 0) {
        $errors[] = "Utilisateur non authentifié. Veuillez vous reconnecter.";
    }
    
    // Check if patient exists and belongs to current user (unless admin)
    $patientQuery = "SELECT * FROM patients WHERE id = ?";
    $stmt = $conn->prepare($patientQuery);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $errors[] = "Patient introuvable.";
    } else {
        $patient = $result->fetch_assoc();
        // Check if patient belongs to current user (unless admin)
        if (!hasRole('admin') && $patient['user_id'] != $user_id) {
            $errors[] = "Vous n'êtes pas autorisé à ajouter un rendez-vous pour ce patient.";
        }
    }
    
    // If no errors, insert the appointment
    if (empty($errors)) {
        // Format the appointment date
        $formatted_date = date('Y-m-d H:i:s', strtotime($appointment_date));
        
        // Insert query
        $query = "INSERT INTO appointments (patient_id, user_id, appointment_date, duration_minutes, notes) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisis", $patient_id, $user_id, $formatted_date, $duration_minutes, $notes);
        
        if ($stmt->execute()) {
            // Success
            $_SESSION['success_message'] = "Rendez-vous planifié avec succès!";
            header("Location: $previous_page");
            exit();
        } else {
            // Error
            $_SESSION['error_message'] = "Erreur lors de la planification du rendez-vous: " . $conn->error;
            header("Location: $previous_page");
            exit();
        }
    } else {
        // Display errors
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: $previous_page");
        exit();
    }
} else {
    // Not a POST request
    header("Location: $previous_page");
    exit();
}
?>
