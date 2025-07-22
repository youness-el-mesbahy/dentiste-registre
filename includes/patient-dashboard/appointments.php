<!-- Appointments Section -->
<div class="row mb-3">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title">Rendez-vous</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
          <i class="ri-add-line"></i> Nouveau Rendez-vous
        </button>
      </div>
      <div class="card-body">
        <?php
        // Récupérer les rendez-vous du patient
        $appointmentsQuery = "SELECT a.*, u.nom as dentist_nom, u.prenom as dentist_prenom 
                              FROM appointments a 
                              LEFT JOIN users u ON a.user_id = u.id 
                              WHERE a.patient_id = $patient_id 
                              ORDER BY a.appointment_date DESC";
        $appointmentsResult = mysqli_query($conn, $appointmentsQuery);
        
        if (mysqli_num_rows($appointmentsResult) > 0) {
        ?>
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Date</th>
                <th>Statut</th>
                <th>Notes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($appointment = mysqli_fetch_assoc($appointmentsResult)) { 
                // Format date and time
                $appointmentDateTime = new DateTime($appointment['appointment_date']);
                $appointmentDate = $appointmentDateTime->format('d/m/Y');
                $appointmentTime = $appointmentDateTime->format('H:i');
                
                // Format duration
                $duration = $appointment['duration_minutes'];
                if ($duration >= 60) {
                  $hours = floor($duration / 60);
                  $minutes = $duration % 60;
                  $durationFormatted = $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '');
                } else {
                  $durationFormatted = $duration . ' min';
                }
                
                // Determine status badge class
                switch ($appointment['status']) {
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
              <tr>
                <td><?php echo $appointmentDate; ?></td>
                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                <td>
                  <?php 
                  if (!empty($appointment['notes'])) {
                    $words = explode(' ', $appointment['notes']);
                    if (count($words) > 5) {
                      echo htmlspecialchars(implode(' ', array_slice($words, 0, 5))) . '...';
                    } else {
                      echo htmlspecialchars($appointment['notes']);
                    }
                  } else {
                    echo 'N/A';
                  }
                  ?>
                </td>
                <td>
                  <div class="d-inline-flex gap-1">
                    <button class="btn btn-outline-primary btn-sm update-status-btn" data-bs-toggle="modal" 
                      data-bs-target="#updateAppointmentStatusModal" data-appointment-id="<?php echo $appointment['id']; ?>" 
                      data-current-status="<?php echo $appointment['status']; ?>">
                      <i class="ri-refresh-line"></i>
                    </button>
                    <?php if ($appointment['status'] === 'scheduled') { ?>
                    <button class="btn btn-outline-success btn-sm edit-appointment-btn" data-bs-toggle="modal"
                      data-bs-target="#editAppointmentModal" data-appointment-id="<?php echo $appointment['id']; ?>"
                      data-appointment-date="<?php echo $appointmentDateTime->format('Y-m-d\TH:i'); ?>" 
                      data-duration="<?php echo $appointment['duration_minutes']; ?>" 
                      data-notes="<?php echo htmlspecialchars($appointment['notes'] ?? ''); ?>">
                      <i class="ri-edit-box-line"></i>
                    </button>
                    <?php } ?>
                    <button class="btn btn-outline-info btn-sm view-notes-btn" data-bs-toggle="modal" 
                      data-bs-target="#viewAppointmentNotesModal" data-notes="<?php echo htmlspecialchars($appointment['notes'] ?? ''); ?>">
                      <i class="ri-file-text-line"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm delete-appointment-btn" data-bs-toggle="modal" 
                      data-bs-target="#deleteAppointmentModal" data-appointment-id="<?php echo $appointment['id']; ?>">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="alert alert-info">
          Aucun rendez-vous trouvé pour ce patient.
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
