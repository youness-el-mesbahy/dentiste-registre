<?php
session_start();
include "../../connection.php";
include "../auth/auth.php";

// Require user to be logged in
requireLogin();
$previous_page = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
// Check if appointment ID is provided
if (!isset($_POST['appointment_id']) || empty($_POST['appointment_id'])) {
    $_SESSION['error_message'] = "ID de rendez-vous non spécifié.";
    header("Location: $previous_page");
    exit();
}

$appointment_id = intval($_POST['appointment_id']);

// Get appointment details to check permissions
$query = "SELECT * FROM appointments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Rendez-vous non trouvé.";
    header("Location: appointments.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Check if user has permission to delete this appointment
$currentUserId = $_SESSION['user_id'] ?? 0;
if (!hasRole('admin') && $appointment['user_id'] != $currentUserId) {
    $_SESSION['error_message'] = "Vous n'êtes pas autorisé à supprimer ce rendez-vous.";
    header("Location: $previous_page");
    exit();
}

// Delete the appointment
$query = "DELETE FROM appointments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Rendez-vous supprimé avec succès.";
} else {
    $_SESSION['error_message'] = "Erreur lors de la suppression du rendez-vous: " . $conn->error;
}

// Redirect back to appointments list
header("Location: $previous_page");
exit();
?>
