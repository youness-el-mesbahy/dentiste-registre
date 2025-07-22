<?php
/**
 * Patient appointment scheduling modal
 */
?>
<!-- Modal Appointment -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="appointmentModalLabel">Planifier un Rendez-vous</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="appointmentForm" method="post" action="includes/appointments/add-appointment.php">
        <div class="modal-body">
          <input type="hidden" id="appointmentPatientId" name="patient_id">
          <?php echo generateCSRFToken(); ?>
          <div class="mb-3">
            <label for="patientName" class="form-label">Patient</label>
            <input type="text" class="form-control" id="patientName" disabled>
          </div>
          <div class="mb-3">
            <label for="appointmentDate" class="form-label">Date du Rendez-vous</label>
            <input type="date" class="form-control" id="appointmentDate" name="appointment_date" required>
            <input type="hidden" name="appointment_time" value="09:00">
          </div>
          <input type="hidden" id="appointmentDuration" name="duration_minutes" value="30">
          <div class="mb-3">
            <label for="appointmentNotes" class="form-label">Notes</label>
            <textarea class="form-control" id="appointmentNotes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Planifier</button>
        </div>
      </form>
    </div>
  </div>
</div>
