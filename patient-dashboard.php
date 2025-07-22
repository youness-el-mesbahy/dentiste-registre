<?php
session_start();
ob_start();
$title = "Tableau de Bord Patient";
include "connection.php";
include "includes/auth/auth.php";
include "includes/utils/error_handler.php";
include "includes/utils/validation.php";
include "includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Require user to be logged in
requireLogin();

// Vérifier si l'ID du patient est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    handleError("ID patient invalide", "Tentative d'accès à patient-dashboard.php sans ID valide", "error", "patients-list.php");
    exit();
}

$patient_id = $_GET['id'];
$currentUserId = $_SESSION['user_id'] ?? 0;

// Récupérer les informations du patient avec vérification de propriété
if (hasRole('admin')) {
    // Les administrateurs peuvent voir tous les patients
    $query = "SELECT p.*, u.email as user_email, u.nom as user_nom, u.prenom as user_prenom 
              FROM patients p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patient_id);
} else {
    // Les utilisateurs normaux ne peuvent voir que leurs propres patients
    $query = "SELECT * FROM patients WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $patient_id, $currentUserId);
}

if (!$stmt->execute()) {
    handleDbError($conn, "Erreur lors de la récupération des données du patient", $query, "patients-list.php");
    exit();
}
$result = $stmt->get_result();

if (mysqli_num_rows($result) == 0) {
    handleError("Patient non trouvé", "Tentative d'accès au patient ID: $patient_id qui n'existe pas ou n'appartient pas à l'utilisateur", "error", "patients-list.php");
    exit();
}

$patient = mysqli_fetch_assoc($result);

// Calculer l'âge à partir de la date de naissance
$dob = new DateTime($patient['date_naissance']);
$today = new DateTime();
$age = $dob->diff($today)->y;

// Récupérer les documents du patient
$docsQuery = "SELECT * FROM patient_documents WHERE patient_id = ? ORDER BY uploaded_at DESC";
$docsStmt = $conn->prepare($docsQuery);
if (!$docsStmt) {
    logError("Erreur de préparation de requête document: " . $conn->error, __FILE__, __LINE__);
    $docsResult = false;
} else {
    $docsStmt->bind_param("i", $patient_id);
    if (!$docsStmt->execute()) {
        logError("Erreur d'exécution de requête document: " . $docsStmt->error, __FILE__, __LINE__);
        $docsResult = false;
    } else {
        $docsResult = $docsStmt->get_result();
    }
}

// Récupérer les consultations du patient
$consultQuery = "SELECT * FROM consultations WHERE patient_id = ? ORDER BY date_consultation DESC";
$consultStmt = $conn->prepare($consultQuery);
if (!$consultStmt) {
    logError("Erreur de préparation de requête consultation: " . $conn->error, __FILE__, __LINE__);
    $consultResult = false;
} else {
    $consultStmt->bind_param("i", $patient_id);
    if (!$consultStmt->execute()) {
        logError("Erreur d'exécution de requête consultation: " . $consultStmt->error, __FILE__, __LINE__);
        $consultResult = false;
    } else {
        $consultResult = $consultStmt->get_result();
    }
}

// Récupérer les rendez-vous du patient
$appointmentsQuery = "SELECT a.*, u.nom as dentist_nom, u.prenom as dentist_prenom 
                  FROM appointments a 
                  LEFT JOIN users u ON a.user_id = u.id 
                  WHERE a.patient_id = ? 
                  ORDER BY a.appointment_date DESC";
$appointmentsStmt = $conn->prepare($appointmentsQuery);
if (!$appointmentsStmt) {
    logError("Erreur de préparation de requête rendez-vous: " . $conn->error, __FILE__, __LINE__);
    $appointmentsResult = false;
} else {
    $appointmentsStmt->bind_param("i", $patient_id);
    if (!$appointmentsStmt->execute()) {
        logError("Erreur d'exécution de requête rendez-vous: " . $appointmentsStmt->error, __FILE__, __LINE__);
        $appointmentsResult = false;
    } else {
        $appointmentsResult = $appointmentsStmt->get_result();
    }
}
?>
<!-- Début du corps de l'application -->

<!-- Composant d'informations du patient -->
<?php include "includes/patient-dashboard/patient-info.php"; ?>

<!-- Composant de remarques -->
<?php include "includes/patient-dashboard/remarque.php"; ?>

<!-- Composant de rendez-vous -->
<?php include "includes/patient-dashboard/appointments.php"; ?>

<!-- Composant de documents -->
<?php include "includes/patient-dashboard/documents.php"; ?>

<!-- Composant de consultations -->
<?php include "includes/patient-dashboard/consultations.php"; ?>

<!-- Composant de modales spécifiques -->
<!-- Document modals -->
<?php include "includes/modals/document-modals.php"; ?>

<!-- Appointment modals -->
<?php include "includes/modals/appointment-modals.php"; ?>

<!-- Consultation modals -->
<?php include "includes/modals/consultation-modals.php"; ?>

<!-- Patient modals -->
<?php include "includes/modals/patient-modals.php"; ?>

<?php
$content = ob_get_clean();
$css = file_get_contents("includes/patient-dashboard/css.php");
$js = file_get_contents("includes/patient-dashboard/js.php");
$js .= file_get_contents("includes/patient-dashboard/appointment-js.php");
include "layout.php";
?>
