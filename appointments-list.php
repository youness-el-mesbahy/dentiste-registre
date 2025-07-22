<?php
session_start();
ob_start();
$title = "Liste des Rendez-vous";
include "connection.php";
include "includes/auth/auth.php";

// Require user to be logged in
requireLogin();

// Display success/error messages if any
if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])) {
  echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
  echo $_SESSION['success_message'];
  echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
  echo '</div>';
  // Make sure to completely remove the success message
  $_SESSION['success_message'] = '';
  unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
  echo $_SESSION['error_message'];
  echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
  echo '</div>';
  unset($_SESSION['error_message']);
}
?>
<!-- Row starts -->
<div class="row gx-3">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title">Liste des Rendez-vous</h5>
        <div class="d-flex gap-2">
          <select id="statusFilter" class="form-select form-select-sm">
            <option value="">Tous les statuts</option>
            <option value="scheduled">Planifié</option>
            <option value="completed">Terminé</option>
            <option value="cancelled">Annulé</option>
            <option value="no_show">Absence</option>
          </select>
          <input type="date" id="dateFilter" class="form-control form-control-sm" placeholder="Filtrer par date">
          <button id="resetFilters" class="btn btn-sm btn-outline-secondary">Réinitialiser</button>
        </div>
      </div>
      <div class="card-body">
        <?php include "includes/appointments/appointments-table.php"; ?>
      </div>
    </div>
  </div>
</div>
<!-- Row ends -->

<!-- Include appointment modals -->
<?php include "includes/modals/appointment-list-modals.php"; ?>

<?php
// Include CSS and JS
$css=file_get_contents("includes/appointments/css.php");
$css .= file_get_contents("includes/appointments/appointment-list.css.php");
$js=file_get_contents("includes/appointments/js.php");
$js .= file_get_contents("includes/appointments/appointment-list.js.php");


$content = ob_get_clean();
include "layout.php";
?>