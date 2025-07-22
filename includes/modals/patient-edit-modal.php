<?php
/**
 * Patient edit modal
 */
?>
<!-- Modal Edit Patient -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPatientModalLabel">Modifier les Informations du Patient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editPatientForm" method="post" action="includes/patients/patient-update.php">
        <div class="modal-body">
          <input type="hidden" id="editPatientId" name="patient_id" value="">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="editNom" class="form-label">Nom</label>
              <input type="text" class="form-control" id="editNom" name="nom" required>
            </div>
            <div class="col-md-6">
              <label for="editPrenom" class="form-label">Prénom</label>
              <input type="text" class="form-control" id="editPrenom" name="prenom" required>
            </div>
            <div class="col-md-6">
              <label for="editGenre" class="form-label">Genre</label>
              <select class="form-select" id="editGenre" name="genre" required>
                <option value="M">Homme</option>
                <option value="F">Femme</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editDateNaissance" class="form-label">Date de Naissance</label>
              <input type="date" class="form-control" id="editDateNaissance" name="date_naissance" required>
            </div>
            <div class="col-md-6">
              <label for="editCin" class="form-label">CIN</label>
              <input type="text" class="form-control" id="editCin" name="cin" required>
            </div>
            <div class="col-md-6">
              <label for="editTelephone" class="form-label">Téléphone</label>
              <input type="text" class="form-control" id="editTelephone" name="telephone" required>
            </div>
            <div class="col-12">
              <label for="editAdresse" class="form-label">Adresse</label>
              <input type="text" class="form-control" id="editAdresse" name="adresse" required>
            </div>
            <div class="col-12">
              <label for="editRemarque" class="form-label">Remarque</label>
              <textarea class="form-control" id="editRemarque" name="remarque" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
