<?php
/**
 * Patient delete confirmation modal
 */
?>
<!-- Modal Delete Patient -->
<div class="modal fade" id="delRow" tabindex="-1" aria-labelledby="delRowLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="delRowLabel">
          Confirmer
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Êtes-vous sûr de vouloir supprimer ce patient ?
      </div>
      <div class="modal-footer">
        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Non</button>
          <form id="deletePatientForm" method="post" action="includes/patients/delete-patient.php">
            <input type="hidden" id="patientIdToDelete" name="patient_id" value="">
            <button type="submit" class="btn btn-danger">Oui</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
