<?php
/**
 * Appointment list page modals
 */
?>
<!-- Modal Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateStatusModalLabel">Mettre à jour le statut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="includes/appointments/update-status.php">
        <div class="modal-body">
          <input type="hidden" name="appointment_id" id="appointmentId" value="">
          <input type="hidden" name="update_status" value="1">
          <div class="mb-3">
            <label for="statusSelect" class="form-label">Statut</label>
            <select class="form-select" id="statusSelect" name="status">
              <option value="scheduled">Planifié</option>
              <option value="completed">Terminé</option>
              <option value="cancelled">Annulé</option>
              <option value="no_show">Absence</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Notes -->
<div class="modal fade" id="viewNotesModal" tabindex="-1" aria-labelledby="viewNotesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewNotesModalLabel">Notes du rendez-vous</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 id="notesPatientName" class="mb-3"></h6>
        <div id="notesContent" class="p-3 bg-light rounded"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Appointment -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editAppointmentModalLabel">Modifier le Rendez-vous</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editAppointmentForm" method="post" action="includes/appointments/update-appointment.php">
        <div class="modal-body">
          <input type="hidden" id="editAppointmentId" name="appointment_id">
          <div class="mb-3">
            <label for="editPatientName" class="form-label">Patient</label>
            <input type="text" class="form-control" id="editPatientName" disabled>
          </div>
          <div class="mb-3">
            <label for="editAppointmentDate" class="form-label">Date et Heure du Rendez-vous</label>
            <input type="datetime-local" class="form-control" id="editAppointmentDate" name="appointment_date" required>
          </div>
          <div class="mb-3">
            <label for="editAppointmentDuration" class="form-label">Durée (minutes)</label>
            <select class="form-select mb-2" id="editAppointmentDuration" name="duration_minutes">
              <option value="15">15 minutes</option>
              <option value="30">30 minutes</option>
              <option value="45">45 minutes</option>
              <option value="60">1 heure</option>
              <option value="90">1 heure 30 minutes</option>
              <option value="120">2 heures</option>
              <option value="custom">Personnalisé</option>
            </select>
            <div id="editCustomDurationContainer" style="display: none;">
              <label for="editCustomDuration" class="form-label">Durée personnalisée (minutes)</label>
              <input type="number" class="form-control" id="editCustomDuration" name="custom_duration" min="5" max="480" placeholder="Entrez la durée en minutes">
            </div>
          </div>
          <div class="mb-3">
            <label for="editAppointmentNotes" class="form-label">Notes</label>
            <textarea class="form-control" id="editAppointmentNotes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Delete Appointment -->
<div class="modal fade" id="deleteAppointmentModal" tabindex="-1" aria-labelledby="deleteAppointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAppointmentModalLabel">Confirmer la suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Êtes-vous sûr de vouloir supprimer ce rendez-vous pour <strong id="deletePatientName"></strong>?</p>
        <p class="text-danger">Cette action est irréversible.</p>
      </div>
      <div class="modal-footer">
        <form method="post" action="includes/appointments/delete-appointment.php">
          <input type="hidden" id="deleteAppointmentId" name="appointment_id" value="">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>
