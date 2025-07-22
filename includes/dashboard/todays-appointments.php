<?php
/**
 * Dashboard today's appointments
 */
?>
<!-- Today's Appointments -->
<div class="col-xxl-8 col-sm-12">
  <div class="card mb-3" style="height: 100%;">
    <div class="card-header">
      <h5 class="card-title">Rendez-vous d'Aujourd'hui</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Patient</th>
              <th>Statut</th>
              <th>Notes</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (mysqli_num_rows($todayAppointmentsDetailResult) > 0) {
              while ($appointment = mysqli_fetch_assoc($todayAppointmentsDetailResult)) {
                $appointmentTime = date('H:i', strtotime($appointment['appointment_date']));
                $durationFormatted = $appointment['duration_minutes'] . ' min';
                
                // Status format
                switch ($appointment['status']) {
                  case 'scheduled':
                    $statusClass = 'bg-primary';
                    $statusText = 'Planifié';
                    break;
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
                  default:
                    $statusClass = 'bg-secondary';
                    $statusText = 'Inconnu';
                }
            ?>
            <tr>
              <td><?php echo htmlspecialchars($appointment['prenom'] . ' ' . $appointment['nom']); ?></td>
              <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
              <td>
                <?php 
                if (isset($appointment['notes']) && !empty($appointment['notes'])) {
                  $words = explode(' ', $appointment['notes']);
                  if (count($words) > 5) {
                    echo htmlspecialchars(implode(' ', array_slice($words, 0, 5))) . '...';
                  } else {
                    echo htmlspecialchars($appointment['notes']);
                  }
                } else {
                  echo '<span class="text-muted">N/A</span>';
                }
                ?>
              </td>
              <td>
                <div class="d-inline-flex gap-1">
                  <a href="patient-dashboard.php?id=<?php echo $appointment['patient_id']; ?>" class="btn btn-outline-primary btn-sm" title="Voir le patient">
                    <i class="ri-eye-line"></i>
                  </a>
                  <?php if ($appointment['status'] === 'scheduled') { ?>
                  <button class="btn btn-outline-success btn-sm update-status-btn" data-bs-toggle="modal" 
                    data-bs-target="#updateAppointmentStatusModal" data-appointment-id="<?php echo $appointment['id']; ?>" 
                    data-current-status="<?php echo $appointment['status']; ?>">
                    <i class="ri-refresh-line"></i>
                  </button>
                  <?php } ?>
                  <?php if (isset($appointment['notes']) && !empty($appointment['notes'])) { ?>
                  <button class="btn btn-outline-info btn-sm view-notes-btn" data-bs-toggle="modal" 
                    data-bs-target="#viewAppointmentNotesModal" data-appointment-id="<?php echo $appointment['id']; ?>"
                    data-notes="<?php echo htmlspecialchars($appointment['notes']); ?>">
                    <i class="ri-file-text-line"></i>
                  </button>
                  <?php } ?>
                </div>
              </td>
            </tr>
            <?php
              }
            } else {
              echo '<tr><td colspan="6" class="text-center">Aucun rendez-vous prévu pour aujourd\'hui</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
