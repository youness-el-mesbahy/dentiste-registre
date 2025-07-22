<?php
session_start();
// Include database connection and authentication
include "../../connection.php";
include "../auth/auth.php";

// Require user to be logged in
requireLogin();

// Get current user ID and previous page
$currentUserId = $_SESSION['user_id'] ?? 0;
$previous_page = $_SERVER['HTTP_REFERER'] ?? '../../dashboard.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (
        isset($_POST['patient_id']) && is_numeric($_POST['patient_id']) &&
        isset($_POST['nom']) && 
        isset($_POST['prenom']) && 
        isset($_POST['genre']) && 
        isset($_POST['cin']) && 
        isset($_POST['telephone']) && 
        isset($_POST['date_naissance']) && 
        isset($_POST['adresse'])
    ) {
        // Get form data
        $patient_id = $_POST['patient_id'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $genre = $_POST['genre'];
        $date_naissance = $_POST['date_naissance'];
        $cin = $_POST['cin'];
        $telephone = $_POST['telephone'];
        $adresse = $_POST['adresse'];
        $remarque = $_POST['remarque'] ?? '';
        
        // Check if user has permission to update this patient
        if (hasRole('admin')) {
            // Admins can update any patient
            $checkQuery = "SELECT id FROM patients WHERE id = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "i", $patient_id);
        } else {
            // Regular users can only update their own patients
            $checkQuery = "SELECT id FROM patients WHERE id = ? AND user_id = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "ii", $patient_id, $currentUserId);
        }
        
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) == 0) {
            // Patient not found or user doesn't have permission
            $_SESSION['error_message'] = "Vous n'êtes pas autorisé à modifier ce patient.";
            header("Location: $previous_page");
            exit();
        }
        
        // Check if CIN is already used by another patient
        $cinQuery = "SELECT id FROM patients WHERE cin = ? AND id != ?";
        $cinStmt = mysqli_prepare($conn, $cinQuery);
        mysqli_stmt_bind_param($cinStmt, "si", $cin, $patient_id);
        mysqli_stmt_execute($cinStmt);
        $cinResult = mysqli_stmt_get_result($cinStmt);
        
        if (mysqli_num_rows($cinResult) > 0) {
            // CIN already exists for another patient
            $_SESSION['error_message'] = "Ce CIN est déjà utilisé par un autre patient.";
            header("Location: $previous_page");
            exit();
        }
        
        // Determine which query to use based on user role
        if (hasRole('admin') && isset($_POST['user_id'])) {
            // Admin changing patient ownership
            $query = "UPDATE patients SET 
                        user_id = ?,
                        nom = ?, 
                        prenom = ?, 
                        genre = ?, 
                        date_naissance = ?, 
                        cin = ?, 
                        telephone = ?, 
                        adresse = ?, 
                        remarque = ? 
                      WHERE id = ?";
            
            // Prepare and bind parameters
            $stmt = mysqli_prepare($conn, $query);
            $user_id = $_POST['user_id'];
            
            mysqli_stmt_bind_param($stmt, "issssssssi", 
                $user_id,
                $nom, 
                $prenom, 
                $genre, 
                $date_naissance, 
                $cin, 
                $telephone, 
                $adresse, 
                $remarque, 
                $patient_id
            );
        } else {
            // Regular update without ownership change
            $query = "UPDATE patients SET 
                        nom = ?, 
                        prenom = ?, 
                        genre = ?, 
                        date_naissance = ?, 
                        cin = ?, 
                        telephone = ?, 
                        adresse = ?, 
                        remarque = ? 
                      WHERE id = ?";
            
            // Prepare and bind parameters
            $stmt = mysqli_prepare($conn, $query);
            
            mysqli_stmt_bind_param($stmt, "ssssssssi", 
                $nom, 
                $prenom, 
                $genre, 
                $date_naissance, 
                $cin, 
                $telephone, 
                $adresse, 
                $remarque, 
                $patient_id
            );
        }
        
        // Execute update
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            // Success
            $_SESSION['success_message'] = "Informations du patient mises à jour avec succès.";
            header("Location: $previous_page");
            exit();
        } else {
            // Error
            $_SESSION['error_message'] = "Erreur lors de la mise à jour du patient: " . mysqli_error($conn);
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
