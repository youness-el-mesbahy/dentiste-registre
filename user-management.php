<?php
session_start();
ob_start();
$title = "Gestion des Utilisateurs";
include "connection.php";
include "includes/auth/auth.php";
include "includes/utils/error_handler.php";
include "includes/utils/validation.php";
include "includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Require admin role to access this page
if (!hasRole('admin')) {
    handleError("Accès non autorisé", "Tentative d'accès à la gestion utilisateurs sans droits admin", "error", "index.php");
    exit();
}

// Process form submission for adding new user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in user-management.php (add)", __FILE__, __LINE__, 'security');
        $error = "Session expirée ou requête invalide. Veuillez réessayer.";
    } else {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $nom = mysqli_real_escape_string($conn, $_POST['nom'] ?? '');
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? '');
    
    // Validate required fields
    $requiredFields = ['email', 'password', 'role'];
    $errors = validateRequired($_POST, $requiredFields);
    
    // Validate email format
    if (!isset($errors['email']) && !validateEmail($email)) {
        $errors['email'] = "L'adresse email n'est pas valide";
    }
    
    // Validate password strength
    if (!isset($errors['password']) && !validatePassword($password, 6)) {
        $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères, dont une majuscule, une minuscule et un chiffre";
    }
    
    // Check if email already exists
    if (!isset($errors['email'])) {
        $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $count = $checkResult->fetch_assoc()['count'];
        
        if ($count > 0) {
            $errors['email'] = "Cet email est déjà utilisé";
        }
    }
    
    if (empty($errors)) {
        // Hash password with secure algorithm
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (email, nom, prenom, password, role, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $email, $nom, $prenom, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            $success = "Utilisateur ajouté avec succès.";
        } else {
            logError("Erreur lors de l'ajout de l'utilisateur: " . $stmt->error, __FILE__, __LINE__);
            $error = "Erreur lors de l'ajout de l'utilisateur. Veuillez réessayer.";
        }
    } else {
        $addUserErrors = $errors;
    }
    }
}

// Process form submission for editing user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in user-management.php (edit)", __FILE__, __LINE__, 'security');
        $error = "Session expirée ou requête invalide. Veuillez réessayer.";
    } else {
    $userId = $_POST['user_id'] ?? 0;
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $nom = mysqli_real_escape_string($conn, $_POST['nom'] ?? '');
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom'] ?? '');
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate required fields
    $requiredFields = ['email', 'user_id', 'role'];
    $errors = validateRequired($_POST, $requiredFields);
    
    // Validate email format
    if (!isset($errors['email']) && !validateEmail($email)) {
        $errors['email'] = "L'adresse email n'est pas valide";
    }
    
    // Validate user ID is numeric
    if (!isset($errors['user_id']) && !is_numeric($userId)) {
        $errors['user_id'] = "ID utilisateur invalide";
    }
    
    // Check if email already exists for other users
    if (!isset($errors['email'])) {
        $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $email, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $count = $checkResult->fetch_assoc()['count'];
        
        if ($count > 0) {
            $errors['email'] = "Cet email est déjà utilisé par un autre utilisateur";
        }
    }
    
    // Validate password if provided
    if (!empty($password) && !validatePassword($password, 6)) {
        $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères, dont une majuscule, une minuscule et un chiffre";
    }
    
    if (empty($errors)) {
        // Check if password should be updated
        if (!empty($password)) {
            // Hash password with secure algorithm
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET email = ?, nom = ?, prenom = ?, password = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $email, $nom, $prenom, $hashedPassword, $role, $userId);
        } else {
            $query = "UPDATE users SET email = ?, nom = ?, prenom = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $email, $nom, $prenom, $role, $userId);
        }
        
        if ($stmt->execute()) {
            $success = "Utilisateur mis à jour avec succès.";
        } else {
            logError("Erreur lors de la mise à jour de l'utilisateur: " . $stmt->error, __FILE__, __LINE__);
            $error = "Erreur lors de la mise à jour de l'utilisateur. Veuillez réessayer.";
        }
    } else {
        $editUserErrors = $errors;
    }
    }
}

// Process form submission for deleting user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in user-management.php (delete)", __FILE__, __LINE__, 'security');
        $error = "Session expirée ou requête invalide. Veuillez réessayer.";
    } else {
    $userId = $_POST['user_id'] ?? 0;
    
    // Validate user ID
    if (empty($userId) || !is_numeric($userId)) {
        $error = "ID utilisateur invalide";
    } else {
        // Check if attempting to delete current user
        if ($userId == $_SESSION['user_id']) {
            $error = "Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            // Check if user has patients
            $checkQuery = "SELECT COUNT(*) as count FROM patients WHERE user_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $patientCount = $checkResult->fetch_assoc()['count'];
            
            if ($patientCount > 0) {
                $error = "Impossible de supprimer cet utilisateur car il possède des patients. Veuillez d'abord réaffecter ses patients.";
            } else {
                $query = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $userId);
                
                if ($stmt->execute()) {
                    $success = "Utilisateur supprimé avec succès.";
                } else {
                    logError("Erreur lors de la suppression de l'utilisateur: " . $stmt->error, __FILE__, __LINE__);
                    $error = "Erreur lors de la suppression de l'utilisateur. Veuillez réessayer.";
                }
            }
        }
    }
    }
}

// Get all users
$query = "SELECT * FROM users ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Row starts -->
<div class="row gx-3">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title">Gestion des Utilisateurs</h5>
        <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="ri-user-add-line me-1"></i> Ajouter Utilisateur
        </button>
      </div>
      <div class="card-body">
        <?php 
        // Display session errors
        echo displaySessionErrors();
        
        // Display current page success messages
        if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Table starts -->
        <div class="table-responsive">
          <table id="userTable" class="table truncate m-0 align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Rôle</th>
                <th>Date de Création</th>
                <th>Patients</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  // Get patient count for this user
                  $patientQuery = "SELECT COUNT(*) as count FROM patients WHERE user_id = ?";
                  $patientStmt = $conn->prepare($patientQuery);
                  if (!$patientStmt) {
                    logError("Erreur de préparation de requête décompte patients: " . $conn->error, __FILE__, __LINE__);
                    $patientCount = "Erreur";
                  } else {
                    $patientStmt->bind_param("i", $row['id']);
                    if (!$patientStmt->execute()) {
                      logError("Erreur d'exécution de requête décompte patients: " . $patientStmt->error, __FILE__, __LINE__);
                      $patientCount = "Erreur";
                    } else {
                      $patientResult = $patientStmt->get_result();
                      $patientCount = mysqli_fetch_assoc($patientResult)['count'];
                    }
                  }
              ?>
              <tr>
                <td>#<?php h($row['id']); ?></td>
                <td><?php h($row['email']); ?></td>
                <td><?php h($row['nom'] ?? 'N/A'); ?></td>
                <td><?php h($row['prenom'] ?? 'N/A'); ?></td>
                <td>
                  <?php 
                    $roleClass = '';
                    switch ($row['role']) {
                      case 'admin':
                        $roleClass = 'bg-danger-subtle text-danger';
                        break;
                      case 'dentiste':
                        $roleClass = 'bg-primary-subtle text-primary';
                        break;
                      case 'assistant':
                        $roleClass = 'bg-success-subtle text-success';
                        break;
                    }
                  ?>
                  <span class="badge <?= $roleClass ?>"><?php h(ucfirst($row['role'])); ?></span>
                </td>
                <td><?php h(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td>
                <td><?php h($patientCount); ?></td>
                <td>
                  <div class="d-inline-flex gap-1">
                    <button class="btn btn-outline-success btn-sm edit-btn" data-bs-toggle="modal"
                      data-bs-target="#editUserModal" data-user-id="<?php h($row['id']); ?>"
                      data-user-email="<?php h($row['email']); ?>" 
                      data-user-nom="<?php h($row['nom'] ?? ''); ?>" 
                      data-user-prenom="<?php h($row['prenom'] ?? ''); ?>"
                      data-user-role="<?php h($row['role']); ?>">
                      <i class="ri-edit-box-line"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                      data-bs-target="#deleteUserModal" data-user-id="<?php h($row['id']); ?>"
                      data-user-email="<?php h($row['email']); ?>">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                    <?php if ($patientCount > 0): ?>
                    <a href="user-patients.php?user_id=<?php h($row['id']); ?>" class="btn btn-outline-info btn-sm"
                      data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Voir les patients">
                      <i class="ri-eye-line"></i>
                    </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php
                }
              } else {
              ?>
              <tr>
                <td colspan="8" class="text-center">Aucun utilisateur trouvé</td>
              </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
        <!-- Table ends -->

        <!-- Modal Add User -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Ajouter un Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <?php if (isset($addUserErrors)): ?>
                  <?php echo displayValidationErrors($addUserErrors); ?>
                <?php endif; ?>
                
                <form method="post" action="" id="addUserForm">
                  <input type="hidden" name="action" value="add">
                  <?php echo generateCSRFToken(); ?>
                  
                  <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control<?php echo isset($addUserErrors['email']) ? ' is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($_POST['email']) ? h($_POST['email'], false) : ''; ?>" required>
                    <?php if (isset($addUserErrors['email'])): ?>
                      <div class="invalid-feedback"><?php h($addUserErrors['email']); ?></div>
                    <?php endif; ?>
                  </div>
                  
                  <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? h($_POST['nom'], false) : ''; ?>">
                  </div>
                  
                  <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo isset($_POST['prenom']) ? h($_POST['prenom'], false) : ''; ?>">
                  </div>
                  
                  <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" class="form-control<?php echo isset($addUserErrors['password']) ? ' is-invalid' : ''; ?>" id="password" name="password" required>
                    <?php if (isset($addUserErrors['password'])): ?>
                      <div class="invalid-feedback"><?php h($addUserErrors['password']); ?></div>
                    <?php endif; ?>
                    <div class="form-text">Le mot de passe doit contenir au moins 6 caractères, dont une majuscule, une minuscule et un chiffre.</div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select class="form-select<?php echo isset($addUserErrors['role']) ? ' is-invalid' : ''; ?>" id="role" name="role" required>
                      <option value="">Sélectionner un rôle</option>
                      <option value="admin"<?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? ' selected' : ''; ?>>Admin</option>
                      <option value="dentiste"<?php echo (isset($_POST['role']) && $_POST['role'] === 'dentiste') ? ' selected' : ''; ?>>Dentiste</option>
                      <option value="assistant"<?php echo (isset($_POST['role']) && $_POST['role'] === 'assistant') ? ' selected' : ''; ?>>Assistant</option>
                    </select>
                    <?php if (isset($addUserErrors['role'])): ?>
                      <div class="invalid-feedback"><?php h($addUserErrors['role']); ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                  <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal Edit User -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Modifier l'Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="post" action="" id="editUserForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editUserId" name="user_id">
                <?php echo generateCSRFToken(); ?>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="editEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="editEmail" name="email" required>
                  </div>
                  <div class="mb-3">
                    <label for="editNom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="editNom" name="nom">
                  </div>
                  <div class="mb-3">
                    <label for="editPrenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="editPrenom" name="prenom">
                  </div>
                  <div class="mb-3">
                    <label for="editPassword" class="form-label">Mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" class="form-control" id="editPassword" name="password">
                  </div>
                  <div class="mb-3">
                    <label for="editRole" class="form-label">Rôle</label>
                    <select class="form-select" id="editRole" name="role" required>
                      <option value="dentiste">Dentiste</option>
                      <option value="assistant">Assistant</option>
                      <option value="admin">Administrateur</option>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                  <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal Delete User -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirmer la Suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <span id="deleteUserEmail" class="fw-bold"></span>?</p>
                <p class="text-danger small">Cette action est irréversible.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="post" action="" id="deleteUserForm">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" id="deleteUserId" name="user_id">
                  <?php echo generateCSRFToken(); ?>
                  <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- Row ends -->

<?php
$css = file_get_contents("includes/patients/css.php");
$js = file_get_contents("includes/patients/js.php");

// Add custom JavaScript for modals
$js .= "
<script>
  // Disable console errors and alerts
  window.onerror = function(message, source, lineno, colno, error) {
    // Prevent the browser from displaying the error
    return true;
  };
  
  // Suppress console errors and alerts
  console.error = function() {};
  console.warn = function() {};
  
  // Disable alert function
  window.alert = function() {};
  
  // Also disable confirm and prompt if needed
  window.confirm = function() { return true; };
  window.prompt = function() { return null; };

  document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#userTable').DataTable({
      lengthMenu: [
        [10, 25, 50],
        [10, 25, 50, 'All'],
      ],
      language: {
        lengthMenu: 'Afficher _MENU_ entrées par page',
        info: 'Page _PAGE_ sur _PAGES_',
        search: 'Rechercher:',
        paginate: {
          first: 'Premier',
          last: 'Dernier',
          next: 'Suivant',
          previous: 'Précédent'
        }
      }
    });
    
    // Set user data in edit modal when button is clicked
    const editModal = document.getElementById('editUserModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // Get user data from data attributes
        const userId = button.getAttribute('data-user-id');
        const email = button.getAttribute('data-user-email');
        const nom = button.getAttribute('data-user-nom');
        const prenom = button.getAttribute('data-user-prenom');
        const role = button.getAttribute('data-user-role');
        
        // Set values in the form
        document.getElementById('editUserId').value = userId;
        document.getElementById('editEmail').value = email;
        document.getElementById('editNom').value = nom;
        document.getElementById('editPrenom').value = prenom;
        document.getElementById('editRole').value = role;
        document.getElementById('editPassword').value = '';
      });
    }
    
    // Set user data in delete modal when button is clicked
    const deleteModal = document.getElementById('deleteUserModal');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // Get user data from data attributes
        const userId = button.getAttribute('data-user-id');
        const email = button.getAttribute('data-user-email');
        
        // Set values in the form
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserEmail').textContent = email;
      });
    }
  });
</script>";

$content = ob_get_clean();
include "layout.php";
?>
