<?php
/**
 * Dentiste Registre - Dashboard
 * 
 * Main dashboard page showing statistics, charts, and recent activities
 */

// Session start and authentication check
session_start();


include 'connection.php';
include 'includes/auth/auth.php';
requireLogin();
$title='Tableau de bord';
// Start output buffering for layout management
ob_start();

// Include statistics queries (sets up all the variables needed for the dashboard)
include 'includes/dashboard/statistics.php';
?>

<div class="container-fluid">
  
  <!-- Welcome Section -->
  <?php include 'includes/dashboard/welcome-section.php'; ?>
  
  <!-- Statistics Cards -->
  <?php include 'includes/dashboard/statistics-cards.php'; ?>
  
  <!-- Today's Appointments and Recent Consultations -->
  <div class="row gx-3">
    <!-- Today's Appointments -->
    <?php include 'includes/dashboard/todays-appointments.php'; ?>
    
    <!-- Recent Consultations -->
    <?php include 'includes/dashboard/recent-consultations.php'; ?>
  </div>
  
  <!-- Charts Section -->
  <div class="row gx-3 my-3">
    <!-- Gender Distribution -->
    <?php include 'includes/dashboard/gender-distribution.php'; ?>
    
    <!-- Appointment Status Distribution -->
    <?php include 'includes/dashboard/appointment-status.php'; ?>
    
    <!-- Practice Activity -->
    <?php include 'includes/dashboard/practice-activity.php'; ?>
  </div>
  
  <!-- Age Distribution -->
  <?php include 'includes/dashboard/age-distribution.php'; ?>
</div>

<!-- Include Appointment Modals -->
<?php include 'includes/modals/appointment-modals.php'; ?>

<?php
// Set page content and include layout
$content = ob_get_clean();

// Include CSS and JS
$css = file_get_contents("includes/dashboard/css.php");
$js = file_get_contents("includes/dashboard/js.php");

// Add the charts JavaScript with direct output to ensure proper variable interpolation
ob_start();
include "includes/dashboard/charts-js.php";
$chartJs = ob_get_clean();
$js .= $chartJs;

// Add the appointments handling JavaScript
ob_start();
include "includes/dashboard/dashboard-appointments.js.php";
$appointmentsJs = ob_get_clean();
$js .= $appointmentsJs;

include "layout.php";
?>
