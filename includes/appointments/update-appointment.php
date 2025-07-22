<?php
/**
 * Update appointment handler
 * This file processes appointment updates from the appointment edit form
 */
session_start();
require_once dirname(dirname(__DIR__)) . '/connection.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth/auth.php';

// Require user to be logged in
requireLogin();

// Process appointment update if submitted
if (isset($_POST['appointment_id']) && isset($_POST['appointment_date']) && !isset($_POST['update_status'])) {
  $appointment_id = intval($_POST['appointment_id']);
  $appointment_date = $_POST['appointment_date'];
  $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '09:00';
  
  // Combine date and time
  if (!empty($appointment_date)) {
    $appointment_date = $appointment_date . ' ' . $appointment_time . ':00';
  }
  
  // Handle custom duration or selected preset
  if (isset($_POST['custom_duration']) && !empty($_POST['custom_duration']) && $_POST['duration'] === 'custom') {
    $duration_minutes = intval($_POST['custom_duration']);
  } else {
    $duration_minutes = isset($_POST['duration']) ? intval($_POST['duration']) : 30;
  }
  
  $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
  
  // Validate inputs
  $errors = [];
  
  if (empty($appointment_date)) {
    $errors[] = "Date de rendez-vous non valide.";
  }
  
  // Duration validation removed - using fixed duration value
  // Ensure duration is set to default 30 minutes
  $duration_minutes = 30;
  
  // Check if appointment exists and user has permission
  $checkQuery = "SELECT a.*, p.user_id as patient_user_id FROM appointments a 
                JOIN patients p ON a.patient_id = p.id 
                WHERE a.id = ?";
  $stmt = $conn->prepare($checkQuery);
  $stmt->bind_param("i", $appointment_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $errors[] = "Rendez-vous non trouvé.";
  } else {
    $appointment = $result->fetch_assoc();
    
    // Check permissions - allow admin, or the dentist who owns the appointment, or dentist for their patients
    if (!hasRole('admin') && 
        !hasRole('dentiste') && 
        $appointment['patient_user_id'] != $_SESSION['user_id'] && 
        $appointment['user_id'] != $_SESSION['user_id']) {
      $errors[] = "Vous n'êtes pas autorisé à modifier ce rendez-vous.";
    }
    
    // Check status
    if ($appointment['status'] !== 'scheduled') {
      $errors[] = "Seuls les rendez-vous planifiés peuvent être modifiés.";
    }
  }
  
  // If no errors, update the appointment
  if (empty($errors)) {
    // Format the appointment date
    $formatted_date = date('Y-m-d H:i:s', strtotime($appointment_date));
    
    // Update query
    $updateQuery = "UPDATE appointments SET appointment_date = ?, duration_minutes = ?, notes = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sisi", $formatted_date, $duration_minutes, $notes, $appointment_id);
    
    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Rendez-vous mis à jour avec succès.";
    } else {
      $_SESSION['error_message'] = "Erreur lors de la mise à jour du rendez-vous: " . $conn->error;
    }
  } else {
    $_SESSION['error_message'] = implode("<br>", $errors);
  }
  
  // Redirect to previous page or appointments list
  if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
  } else {
    header("Location: " . dirname(dirname(__DIR__)) . "/appointments-list.php");
  }
  exit();
}
?>
