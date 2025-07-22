<?php
/**
 * Update appointment status handler
 * This file processes status updates for appointments
 */
session_start();
require_once dirname(dirname(__DIR__)) . '/connection.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth/auth.php';

// Require user to be logged in
requireLogin();

// Process status update if submitted
if (isset($_POST['update_status']) && isset($_POST['appointment_id']) && isset($_POST['status'])) {
  $appointment_id = intval($_POST['appointment_id']);
  $status = $_POST['status'];
  
  // Validate status
  $valid_statuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
  if (in_array($status, $valid_statuses)) {
    
    // Check if appointment exists and user has permission
    $checkQuery = "SELECT a.*, p.user_id as patient_user_id FROM appointments a 
                   JOIN patients p ON a.patient_id = p.id 
                   WHERE a.id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
      $_SESSION['error_message'] = "Rendez-vous non trouvé.";
    } else {
      $appointment = $result->fetch_assoc();
      
      // Check permissions
      if (!hasRole('dentiste') && $appointment['user_id'] != $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à modifier ce rendez-vous.";
      } else {
        // Update the status
        $updateQuery = "UPDATE appointments SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $status, $appointment_id);
        
        if ($stmt->execute()) {
          $_SESSION['success_message'] = "Statut du rendez-vous mis à jour avec succès.";
        } else {
          $_SESSION['error_message'] = "Erreur lors de la mise à jour du statut: " . $conn->error;
        }
      }
    }
  } else {
    $_SESSION['error_message'] = "Statut non valide.";
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
