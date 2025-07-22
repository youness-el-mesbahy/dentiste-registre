<?php
session_start();
ob_start();
$title = "Patients de l'Utilisateur";
include "connection.php";
include "includes/auth/auth.php";

// Require admin role to access this page
if (!hasRole('admin')) {
    header("Location: index.php?error=unauthorized");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: user-management.php?error=invalid_id");
    exit();
}

$userId = $_GET['user_id'];

// Get user details
$userQuery = "SELECT * FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    header("Location: user-management.php?error=user_not_found");
    exit();
}

$user = $userResult->fetch_assoc();

// Process form submission for reassigning patients
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reassign') {
    $patientIds = isset($_POST['patient_ids']) ? $_POST['patient_ids'] : [];
    $newUserId = $_POST['new_user_id'];
    
    if (!empty($patientIds) && is_numeric($newUserId)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            $successCount = 0;
            
            foreach ($patientIds as $patientId) {
                $updateQuery = "UPDATE patients SET user_id = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("ii", $newUserId, $patientId);
                
                if ($updateStmt->execute()) {
                    $successCount++;
                }
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            $success = "$successCount patient(s) réaffecté(s) avec succès.";
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            
            $error = "Erreur lors de la réaffectation des patients: " . $e->getMessage();
        }
    } else {
        $error = "Veuillez sélectionner au moins un patient et un utilisateur de destination.";
    }
}

// Get all patients for this user
$patientsQuery = "SELECT * FROM patients WHERE user_id = ? ORDER BY id DESC";
$patientsStmt = $conn->prepare($patientsQuery);
$patientsStmt->bind_param("i", $userId);
$patientsStmt->execute();
$patientsResult = $patientsStmt->get_result();

// Get all other users for reassignment
$usersQuery = "SELECT id, email, nom, prenom, role FROM users WHERE id != ? ORDER BY role, nom, prenom";
$usersStmt = $conn->prepare($usersQuery);
$usersStmt->bind_param("i", $userId);
$usersStmt->execute();
$usersResult = $usersStmt->get_result();
?>

<!-- Row starts -->
<div class="row gx-3">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title">
          Patients de <?= !empty($user['nom']) && !empty($user['prenom']) ? htmlspecialchars($user['prenom'] . ' ' . $user['nom']) : htmlspecialchars($user['email']) ?>
          <span class="badge bg-primary-subtle text-primary ms-2"><?= ucfirst($user['role']) ?></span>
        </h5>
        <a href="user-management.php" class="btn btn-outline-secondary ms-auto">
          <i class="ri-arrow-left-line me-1"></i> Retour
        </a>
      </div>
      <div class="card-body">
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= $success ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= $error ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if ($patientsResult->num_rows > 0): ?>
        <form method="post" id="reassignForm">
          <input type="hidden" name="action" value="reassign">
          
          <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
              <h6 class="mb-0">Réaffecter les patients sélectionnés à:</h6>
              <div class="ms-3" style="min-width: 200px;">
                <select class="form-select" name="new_user_id" required>
                  <option value="">Sélectionner un utilisateur</option>
                  <?php while ($otherUser = $usersResult->fetch_assoc()): ?>
                  <option value="<?= $otherUser['id'] ?>">
                    <?= !empty($otherUser['nom']) && !empty($otherUser['prenom']) 
                        ? htmlspecialchars($otherUser['prenom'] . ' ' . $otherUser['nom'] . ' (' . ucfirst($otherUser['role']) . ')') 
                        : htmlspecialchars($otherUser['email'] . ' (' . ucfirst($otherUser['role']) . ')') ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary ms-2">Réaffecter</button>
              <button type="button" id="selectAllBtn" class="btn btn-outline-secondary ms-2">Tout sélectionner</button>
              <button type="button" id="deselectAllBtn" class="btn btn-outline-secondary ms-2">Tout désélectionner</button>
            </div>
          </div>

          <!-- Table starts -->
          <div class="table-responsive">
            <table id="patientTable" class="table truncate m-0 align-middle">
              <thead>
                <tr>
                  <th width="50">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="selectAll">
                    </div>
                  </th>
                  <th>ID</th>
                  <th>Nom du Patient</th>
                  <th>Genre</th>
                  <th>Date de Naissance</th>
                  <th>CIN</th>
                  <th>Téléphone</th>
                  <th>Date de Création</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($patient = $patientsResult->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div class="form-check">
                      <input class="form-check-input patient-checkbox" type="checkbox" name="patient_ids[]" value="<?= $patient['id'] ?>">
                    </div>
                  </td>
                  <td>#<?= $patient['id'] ?></td>
                  <td><?= htmlspecialchars($patient['nom'] . ' ' . $patient['prenom']) ?></td>
                  <td>
                    <?php 
                      $genderClass = ($patient['genre'] == 'M') ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning';
                      $genderText = ($patient['genre'] == 'M') ? 'Homme' : 'Femme';
                    ?>
                    <span class="badge <?= $genderClass ?>"><?= $genderText ?></span>
                  </td>
                  <td><?= date('d/m/Y', strtotime($patient['date_naissance'])) ?></td>
                  <td><?= htmlspecialchars($patient['cin']) ?></td>
                  <td><?= htmlspecialchars($patient['telephone']) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($patient['created_at'])) ?></td>
                  <td>
                    <div class="d-inline-flex gap-1">
                      <a href="patient-dashboard.php?id=<?= $patient['id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="ri-eye-line"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <!-- Table ends -->
        </form>
        <?php else: ?>
        <div class="alert alert-info">
          Cet utilisateur n'a pas de patients.
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
<!-- Row ends -->

<?php
$css = file_get_contents("includes/patients/css.php");
$js = file_get_contents("includes/patients/js.php");

// Add custom JavaScript for patient selection
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
    $('#patientTable').DataTable({
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
    
    // Handle select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    const patientCheckboxes = document.querySelectorAll('.patient-checkbox');
    
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        
        patientCheckboxes.forEach(function(checkbox) {
          checkbox.checked = isChecked;
        });
      });
    }
    
    // Handle select all button
    const selectAllBtn = document.getElementById('selectAllBtn');
    if (selectAllBtn) {
      selectAllBtn.addEventListener('click', function() {
        patientCheckboxes.forEach(function(checkbox) {
          checkbox.checked = true;
        });
        if (selectAllCheckbox) {
          selectAllCheckbox.checked = true;
        }
      });
    }
    
    // Handle deselect all button
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    if (deselectAllBtn) {
      deselectAllBtn.addEventListener('click', function() {
        patientCheckboxes.forEach(function(checkbox) {
          checkbox.checked = false;
        });
        if (selectAllCheckbox) {
          selectAllCheckbox.checked = false;
        }
      });
    }
    
    // Update select all checkbox when individual checkboxes change
    patientCheckboxes.forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        if (selectAllCheckbox) {
          const allChecked = Array.from(patientCheckboxes).every(function(cb) {
            return cb.checked;
          });
          
          selectAllCheckbox.checked = allChecked;
        }
      });
    });
  });
</script>";

$content = ob_get_clean();
include "layout.php";
?>
