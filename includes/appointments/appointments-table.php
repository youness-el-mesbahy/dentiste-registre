<?php
/**
 * Appointments table component
 * Displays the list of appointments based on user permissions
 */

// Get the current user ID
$currentUserId = $_SESSION['user_id'] ?? 0;

// Prepare query based on user role
if (hasRole('admin')) {
  // Admins can see all appointments
  $query = "SELECT a.*, p.nom, p.prenom, p.date_naissance, u.email as dentist_email, 
            u.nom as dentist_nom, u.prenom as dentist_prenom 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.id 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.appointment_date DESC";
} else {
  // Regular users only see their own appointments
  $query = "SELECT a.*, p.nom, p.prenom, p.date_naissance 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.id 
            WHERE a.user_id = ? 
            ORDER BY a.appointment_date DESC";
}

$stmt = $conn->prepare($query);

// Bind parameters if needed
if (!hasRole('admin')) {
  $stmt->bind_param("i", $currentUserId);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Table starts -->
<div class="table-responsive">
  <table id="basicExample" class="table table-striped m-0 align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Patient</th>
        <th>Date</th>
        <th>Statut</th>
        <th>Notes</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          // Format date and time
          $appointmentDateTime = new DateTime($row['appointment_date']);
          $appointmentDate = $appointmentDateTime->format('d/m/Y');
          $appointmentTime = $appointmentDateTime->format('H:i');
          
          // Format duration
          $duration = $row['duration_minutes'];
          if ($duration >= 60) {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            $durationFormatted = $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '');
          } else {
            $durationFormatted = $duration . ' min';
          }
          
          // Determine status badge class
          switch ($row['status']) {
            case 'completed':
              $statusClass = 'bg-success';
              $statusText = 'Terminé';
              break;
            case 'cancelled':
              $statusClass = 'bg-danger';
              $statusText = 'Annulé';
              break;
            case 'no_show':
              $statusClass = 'bg-warning';
              $statusText = 'Absence';
              break;
            default: // scheduled
              $statusClass = 'bg-primary';
              $statusText = 'Planifié';
          }
      ?>
      <tr class="appointment-row" data-status="<?php echo $row['status']; ?>" data-date="<?php echo $appointmentDateTime->format('Y-m-d'); ?>">
        <td>#<?php echo $row['id']; ?></td>
        <td>
          <?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?>
        </td>
        <td><?php echo $appointmentDate; ?></td>
        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
        <td>
          <?php 
          if (!empty($row['notes'])) {
            $words = explode(' ', $row['notes']);
            if (count($words) > 5) {
              echo htmlspecialchars(implode(' ', array_slice($words, 0, 5))) . '...';
            } else {
              echo htmlspecialchars($row['notes']);
            }
          } else {
            echo 'N/A';
          }
          ?>
        </td>
        <td>
          <div class="d-inline-flex gap-1">
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal"
              data-appointment-id="<?php echo $row['id']; ?>" data-current-status="<?php echo $row['status']; ?>">
              <i class="ri-refresh-line"></i>
            </button>
            <?php if ($row['status'] === 'scheduled') { ?>
            <button class="btn btn-outline-success btn-sm edit-appointment-btn" data-bs-toggle="modal"
              data-bs-target="#editAppointmentModal" data-appointment-id="<?php echo $row['id']; ?>"
              data-patient-id="<?php echo $row['patient_id']; ?>" data-patient-name="<?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?>"
              data-appointment-date="<?php echo $appointmentDateTime->format('Y-m-d\TH:i'); ?>" 
              data-duration="<?php echo $row['duration_minutes']; ?>" data-notes="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>">
              <i class="ri-edit-box-line"></i>
            </button>
            <?php } ?>
            <button class="btn btn-outline-info btn-sm view-notes" data-bs-toggle="modal" data-bs-target="#viewNotesModal"
              data-notes="<?php echo htmlspecialchars($row['notes']); ?>" data-patient="<?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?>">
              <i class="ri-file-text-line"></i>
            </button>
            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAppointmentModal"
              data-appointment-id="<?php echo $row['id']; ?>" data-patient="<?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?>">
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php
        }
      } else {
      ?>
      <tr>
        <td colspan="6" class="text-center">Aucun rendez-vous trouvé</td>
      </tr>
      <?php
      }
      ?>
    </tbody>
  </table>
</div>
<!-- Table ends -->
