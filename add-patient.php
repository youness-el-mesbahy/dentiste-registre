<?php
session_start();
ob_start();
$title = "Ajouter Patient";
include "connection.php";
include "includes/auth/auth.php";
include "includes/utils/error_handler.php";
include "includes/utils/validation.php";
include "includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Require user to be logged in
requireLogin();

// Get current user ID
$currentUserId = $_SESSION['user_id'] ?? 0;

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/patients/';
if (!file_exists($uploadDir)) {
    // Use more secure permissions (0755 instead of 0777)
    mkdir($uploadDir, 0755, true);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in add-patient.php", __FILE__, __LINE__, 'security');
        $error = "Session expirée ou requête invalide. Veuillez réessayer.";
    } else {
    // Get form data
    $nom = mysqli_real_escape_string($conn, $_POST['nom'] ?? '');
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom'] ?? '');
    $genre = mysqli_real_escape_string($conn, $_POST['genre'] ?? '');
    $cin = mysqli_real_escape_string($conn, $_POST['cin'] ?? '');
    $telephone = mysqli_real_escape_string($conn, $_POST['telephone'] ?? '');
    $date_naissance = mysqli_real_escape_string($conn, $_POST['date_naissance'] ?? '');
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse'] ?? '');
    $remarque = isset($_POST['remarque']) ? mysqli_real_escape_string($conn, $_POST['remarque']) : NULL;
    
    // Validate required fields
    $requiredFields = ['nom', 'prenom', 'genre', 'cin', 'telephone', 'date_naissance', 'adresse'];
    $errors = validateRequired($_POST, $requiredFields);
    
    // Validate CIN format
    if (!isset($errors['cin']) && !validateCIN($cin)) {
        $errors['cin'] = "Le format du CIN n'est pas valide (exemple: AB123456)";
    }
    
    // Validate phone number
    if (!isset($errors['telephone']) && !validatePhone($telephone)) {
        $errors['telephone'] = "Le numéro de téléphone n'est pas valide";
    }
    
    // Validate date format and value
    if (!isset($errors['date_naissance']) && !validateDate($date_naissance)) {
        $errors['date_naissance'] = "La date de naissance n'est pas valide";
    } else {
        // Check if date is not in the future
        $today = new DateTime();
        $birthDate = new DateTime($date_naissance);
        if ($birthDate > $today) {
            $errors['date_naissance'] = "La date de naissance ne peut pas être dans le futur";
        }
    }
    
    // Check for validation errors
    if (!empty($errors)) {
        $validationErrors = $errors;
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
    
    try {
        // Insert patient into database using prepared statement
        if ($remarque === NULL) {
            $query = "INSERT INTO patients (user_id, nom, prenom, genre, cin, telephone, date_naissance, adresse, remarque) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception(handleDbError($conn, "Erreur lors de la préparation de la requête", $query));
            }
            $stmt->bind_param("isssssss", $currentUserId, $nom, $prenom, $genre, $cin, $telephone, $date_naissance, $adresse);
        } else {
            $query = "INSERT INTO patients (user_id, nom, prenom, genre, cin, telephone, date_naissance, adresse, remarque) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception(handleDbError($conn, "Erreur lors de la préparation de la requête", $query));
            }
            $stmt->bind_param("issssssss", $currentUserId, $nom, $prenom, $genre, $cin, $telephone, $date_naissance, $adresse, $remarque);
        }
        
        if (!$stmt->execute()) {
            throw new Exception(handleDbError($conn, "Erreur lors de l'ajout du patient", $query));
        }
        
        // Get the newly inserted patient ID
        $patient_id = mysqli_insert_id($conn);
        
        // Handle file uploads if files were submitted
        if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
            // Define allowed file types and maximum file size
            $allowedTypes = [
                'application/pdf',                // PDF
                'image/jpeg', 'image/jpg',        // JPEG
                'image/png',                      // PNG
                'application/msword',             // DOC
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // DOCX
            ];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            
            $fileCount = count($_FILES['documents']['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                    $tempName = $_FILES['documents']['tmp_name'][$i];
                    $originalName = $_FILES['documents']['name'][$i];
                    $fileType = $_FILES['documents']['type'][$i];
                    $fileSize = $_FILES['documents']['size'][$i];
                    
                    // Validate file type
                    if (!validateFileType($fileType, $allowedTypes)) {
                        throw new Exception("Type de fichier non autorisé: $originalName ($fileType)");
                    }
                    
                    // Validate file size
                    if (!validateFileSize($fileSize, $maxFileSize)) {
                        throw new Exception("Fichier trop volumineux: $originalName (".round($fileSize/1048576, 2)." MB). Maximum: ".round($maxFileSize/1048576, 2)." MB");
                    }
                    
                    // Generate unique filename
                    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newFileName = uniqid('doc_') . '.' . $fileExtension;
                    $destination = $uploadDir . $newFileName;
                    
                    // Move uploaded file to destination
                    if (move_uploaded_file($tempName, $destination)) {
                        // Insert document info into database
                        $file_name = mysqli_real_escape_string($conn, $originalName);
                        $file_type = mysqli_real_escape_string($conn, $fileType);
                        $file_path = mysqli_real_escape_string($conn, $destination);
                        
                        $docQuery = "INSERT INTO patient_documents (patient_id, file_name, file_type, file_path) 
                                    VALUES (?, ?, ?, ?)";
                        $docStmt = $conn->prepare($docQuery);
                        if (!$docStmt) {
                            throw new Exception(handleDbError($conn, "Erreur lors de la préparation de la requête de document", $docQuery));
                        }
                        $docStmt->bind_param("isss", $patient_id, $file_name, $file_type, $file_path);
                        
                        if (!$docStmt->execute()) {
                            throw new Exception(handleDbError($conn, "Erreur lors de l'ajout du document", $docQuery));
                        }
                    } else {
                        throw new Exception("Failed to upload file: $originalName");
                    }
                } else if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception("Error uploading file: " . $_FILES['documents']['error'][$i]);
                }
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect to patients list with success message
        header("Location: patients-list.php?status=success");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        logError($e->getMessage(), __FILE__, __LINE__);
        $error = "Une erreur est survenue lors de l'ajout du patient. Veuillez réessayer.";
    }
    }
    }
}
?>

<!-- App body starts -->

<!-- Row starts -->
<div class="row gx-3">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Ajouter un nouveau patient</h5>
            </div>
            <div class="card-body">

                <!-- Row starts -->
                <?php 
                // Display session errors if any
                echo displaySessionErrors();
                
                // Display validation errors
                if (isset($validationErrors)) {
                    echo displayValidationErrors($validationErrors);
                }
                
                // Display current page errors
                if (isset($error)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php } ?>
                
                <form method="post" action="" enctype="multipart/form-data">
                    <?php echo generateCSRFToken(); ?>
                <div class="row gx-3">
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" placeholder="Entrez le nom" required>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label" for="prenom">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Entrez le prénom" required>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label" for="date_naissance">Date de naissance <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label">Genre <span class="text-danger">*</span></label>
                            <div class="m-0">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genre"
                                        id="genreM" value="M" required>
                                    <label class="form-check-label" for="genreM">Homme</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genre"
                                        id="genreF" value="F">
                                    <label class="form-check-label" for="genreF">Femme</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label" for="cin">CIN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cin" name="cin" placeholder="Entrez le CIN" required>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label" for="telephone">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Entrez le numéro de téléphone" required>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-lg-8 col-sm-12">
                        <div class="mb-3">
                            <label class="form-label" for="adresse">Adresse <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Entrez l'adresse complète" required>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-lg-8 col-sm-12">
                        <div class="mb-3">
                            <label class="form-label" for="remarque">Remarque</label>
                            <textarea class="form-control" id="remarque" name="remarque" rows="3" placeholder="Entrez des remarques ou notes supplémentaires"></textarea>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="mb-3">
                            <label class="form-label" for="documents">Documents du patient</label>
                            <input type="file" class="form-control" id="documents" name="documents[]" multiple>
                            <div class="form-text">Vous pouvez sélectionner plusieurs fichiers. Formats acceptés: PDF, JPG, PNG</div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="patients-list.php" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Créer le profil patient
                            </button>
                        </div>
                    </div>
                </div>
                </form>
                <!-- Row ends -->

            </div>
        </div>
    </div>
</div>
<!-- Row ends -->

<?php
$content = ob_get_clean();
$css=file_get_contents("includes/add-patient/css.php");
$js=file_get_contents("includes/add-patient/js.php");
include "layout.php";
?>