<?php
session_start();
// Include database connection
include "../../connection.php";

// Get the previous page URL
$previous_page = $_SERVER['HTTP_REFERER'] ?? '../../dashboard.php';

// Check if document ID and patient ID are set and numeric
if (isset($_POST['doc_id']) && is_numeric($_POST['doc_id']) && isset($_POST['patient_id']) && is_numeric($_POST['patient_id'])) {
    $doc_id = $_POST['doc_id'];
    $patient_id = $_POST['patient_id'];
    
    // First, get the file path to delete the actual file
    $query = "SELECT file_path FROM patient_documents WHERE id = $doc_id AND patient_id = $patient_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $document = mysqli_fetch_assoc($result);
        $file_path = $document['file_path'];
        
        // Delete the file from the filesystem if it exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete the document record from the database
        $deleteQuery = "DELETE FROM patient_documents WHERE id = $doc_id AND patient_id = $patient_id";
        $deleteResult = mysqli_query($conn, $deleteQuery);
        
        if ($deleteResult) {
            // Deletion successful
            $_SESSION['success_message'] = "Document supprimé avec succès.";
            header("Location: $previous_page");
            exit();
        } else {
            // Error in database deletion
            $_SESSION['error_message'] = "Erreur lors de la suppression du document: " . mysqli_error($conn);
            header("Location: $previous_page");
            exit();
        }
    } else {
        // Document not found
        $_SESSION['error_message'] = "Document non trouvé.";
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
