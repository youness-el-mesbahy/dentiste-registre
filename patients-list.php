<?php
session_start();
$title="Liste des Patients";
include "connection.php";
include "includes/auth/auth.php";
include "includes/utils/error_handler.php";
include "includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Require user to be logged in
requireLogin();
ob_start();
?>
<!-- Row starts -->
<div class="row gx-3">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title">Liste des Patients</h5>
        <a href="add-patient.php" class="btn btn-primary ms-auto">Ajouter Patient</a>
      </div>
      <div class="card-body">
        <?php echo displaySessionErrors(); ?>

        <!-- Table starts -->
        <div class="table-responsive">
          <table id="basicExample" class="table truncate m-0 align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nom du Patient</th>
                <th>Genre</th>
                <th>Date de Naissance</th>
                <th>CIN</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Remarque</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Get the current user ID
              $currentUserId = $_SESSION['user_id'] ?? 0;
              
              // Filter patients by user_id if not admin
              if (hasRole('admin')) {
                // Admins can see all patients
                $query = "SELECT p.*, u.email as user_email FROM patients p 
                          LEFT JOIN users u ON p.user_id = u.id 
                          ORDER BY p.id DESC";
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                  logError("Erreur de préparation de requête patients (admin): " . $conn->error, __FILE__, __LINE__);
                  echo '<div class="alert alert-danger">Une erreur est survenue lors du chargement des patients</div>';
                } else if (!$stmt->execute()) {
                  logError("Erreur d'exécution de requête patients (admin): " . $stmt->error, __FILE__, __LINE__);
                  echo '<div class="alert alert-danger">Une erreur est survenue lors du chargement des patients</div>';
                }
              } else {
                // Regular users only see their own patients
                $query = "SELECT * FROM patients WHERE user_id = ? ORDER BY id DESC";
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                  logError("Erreur de préparation de requête patients (user): " . $conn->error, __FILE__, __LINE__);
                  echo '<div class="alert alert-danger">Une erreur est survenue lors du chargement des patients</div>';
                } else {
                  $stmt->bind_param("i", $currentUserId);
                  if (!$stmt->execute()) {
                    logError("Erreur d'exécution de requête patients (user): " . $stmt->error, __FILE__, __LINE__);
                    echo '<div class="alert alert-danger">Une erreur est survenue lors du chargement des patients</div>';
                  }
                }
              }
              
              $result = $stmt->get_result();
              
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  // Calculate age from date_naissance
                  $dob = new DateTime($row['date_naissance']);
                  $today = new DateTime();
                  $age = $dob->diff($today)->y;
                  
                  // Determine gender badge class
                  $genderClass = ($row['genre'] == 'M') ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning';
                  $genderText = ($row['genre'] == 'M') ? 'Homme' : 'Femme';
                  
                  // Patient image removed as requested
              ?>
              <tr>
                <td>#<?php h($row['id']); ?></td>
                <td>
                  <?php h($row['nom'] . ' ' . $row['prenom']); ?>
                </td>
                <td><span class="badge <?php echo $genderClass; ?>"><?php h($genderText); ?></span></td>
                <td><?php h($row['date_naissance']); ?></td>
                <td><?php h($row['cin']); ?></td>
                <td><?php h($row['telephone']); ?></td>
                <td><?php h($row['adresse']); ?></td>
                <td><?php 
                    if ($row['remarque']) {
                        $words = explode(' ', $row['remarque']);
                        if (count($words) > 5) {
                            h(implode(' ', array_slice($words, 0, 5)) . '...');
                        } else {
                            h($row['remarque']);
                        }
                    } else {
                        echo 'N/A';
                    }
                ?></td>
                <td>
                  <div class="d-inline-flex gap-1">
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                      data-bs-target="#delRow" data-patient-id="<?php h($row['id']); ?>">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                    <button class="btn btn-outline-success btn-sm edit-btn" data-bs-toggle="modal"
                      data-bs-target="#editPatientModal" data-patient-id="<?php h($row['id']); ?>"
                      data-patient-nom="<?php h($row['nom']); ?>" data-patient-prenom="<?php h($row['prenom']); ?>"
                      data-patient-genre="<?php h($row['genre']); ?>" data-patient-dob="<?php h($row['date_naissance']); ?>"
                      data-patient-cin="<?php h($row['cin']); ?>" data-patient-tel="<?php h($row['telephone']); ?>"
                      data-patient-adresse="<?php h($row['adresse']); ?>" data-patient-remarque="<?php h($row['remarque']); ?>">
                      <i class="ri-edit-box-line"></i>
                    </button>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                      data-bs-target="#appointmentModal" data-patient-id="<?php h($row['id']); ?>"
                      data-patient-name="<?php h($row['nom'] . ' ' . $row['prenom']); ?>">
                      <i class="ri-calendar-line"></i>
                    </button>
                    <a href="patient-dashboard.php?id=<?php h($row['id']); ?>" class="btn btn-outline-info btn-sm"
                      data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Dashboard">
                      <i class="ri-eye-line"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php
                }
              } else {
              ?>
              <tr>
                <td colspan="9" class="text-center">Aucun patient trouvé</td>
              </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
        <!-- Table ends -->

        <!-- Patient Delete Modal -->
        <?php include "includes/modals/patient-delete-modal.php"; ?>

        <!-- Patient Appointment Modal -->
        <?php include "includes/modals/patient-appointment-modal.php"; ?>

        <!-- Patient Edit Modal -->
        <?php include "includes/modals/patient-edit-modal.php"; ?>

      </div>
    </div>
  </div>
</div>
<!-- Row ends -->

<?php
// Get CSS and JS content
$css = @file_get_contents("includes/patients/css.php") ?: '';
$js = @file_get_contents("includes/patients/js.php") ?: '';

// Add custom JavaScript for delete functionality and DataTables with CIN search
$js .= <<<'JAVASCRIPT'
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set patient ID in delete modal when button is clicked
    var deleteModal = document.getElementById('delRow');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var patientId = button.getAttribute('data-patient-id');
        document.getElementById('patientIdToDelete').value = patientId;
      });
    }
    
    // Set patient data in edit modal when button is clicked
    var editModal = document.getElementById('editPatientModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        
        // Get patient data from data attributes
        var patientId = button.getAttribute('data-patient-id');
        var nom = button.getAttribute('data-patient-nom');
        var prenom = button.getAttribute('data-patient-prenom');
        var genre = button.getAttribute('data-patient-genre');
        var dob = button.getAttribute('data-patient-dob');
        var cin = button.getAttribute('data-patient-cin');
        var tel = button.getAttribute('data-patient-tel');
        var adresse = button.getAttribute('data-patient-adresse');
        var remarque = button.getAttribute('data-patient-remarque');
        
        // Set values in the form
        document.getElementById('editPatientId').value = patientId;
        document.getElementById('editNom').value = nom;
        document.getElementById('editPrenom').value = prenom;
        document.getElementById('editGenre').value = genre;
        document.getElementById('editDateNaissance').value = dob;
        document.getElementById('editCin').value = cin;
        document.getElementById('editTelephone').value = tel;
        document.getElementById('editAdresse').value = adresse;
        document.getElementById('editRemarque').value = remarque;
      });
    }
  });
</script>

<script>
  // DataTables functionality
  $(document).ready(function() {
    // Check if DataTable is already initialized
    var patientsTable;
    if ($.fn.dataTable.isDataTable('#basicExample')) {
      patientsTable = $('#basicExample').DataTable();
    } else {
      // Basic DataTable initialization
      patientsTable = $('#basicExample').DataTable({
        pageLength: 10
      });
    }
    
    // Handle appointment modal
    $('#appointmentModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      $('#appointmentPatientId').val(button.data('patient-id'));
      $('#patientName').val(button.data('patient-name'));
    });
    
    // Set min datetime for appointment
    $('#appointmentModal').on('shown.bs.modal', function() {
      var now = new Date();
      var year = now.getFullYear();
      var month = (now.getMonth() + 1).toString().padStart(2, '0');
      var day = now.getDate().toString().padStart(2, '0');
      var hours = now.getHours().toString().padStart(2, '0');
      var minutes = now.getMinutes().toString().padStart(2, '0');
      var formattedDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
      $('#appointmentDate').attr('min', formattedDateTime);
    });
    
    // Handle duration dropdown
    $('#appointmentDuration').change(function() {
      if ($(this).val() === 'custom') {
        $('#customDurationContainer').show();
      } else {
        $('#customDurationContainer').hide();
      }
    });
    
    // Add CIN search field
    $('.dataTables_filter').append('&nbsp;&nbsp;<label>CIN:&nbsp;</label><input type="text" id="cinSearch" class="form-control form-control-sm d-inline-block" style="width: 150px; margin-left: 5px;">');    
    
    // Add custom search for CIN
    var cinSearchTimeout;
    $('#cinSearch').on('keyup', function() {
      clearTimeout(cinSearchTimeout);
      cinSearchTimeout = setTimeout(function() {
        patientsTable.draw();
      }, 300);
    });
    
    // Custom filtering function for CIN
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      if (settings.nTable.id !== 'basicExample') return true;
      
      var cinValue = $('#cinSearch').val().toLowerCase();
      if (!cinValue) return true;
      
      var cin = data[4].toLowerCase(); // CIN is in column 5 (index 4)
      return cin.includes(cinValue);
    });
  });
</script>
JAVASCRIPT;

$content=ob_get_clean();
include "layout.php";
?>