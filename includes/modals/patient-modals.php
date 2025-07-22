<?php
/**
 * Patient-related modals
 */
?>

<!-- Modal Edit Patient Remarque -->
<div class="modal fade" id="editRemarqueModal" tabindex="-1" aria-labelledby="editRemarqueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRemarqueModalLabel">Modifier la remarque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="includes/patients/edit-remarque.php">
                <div class="modal-body">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    <div class="mb-3">
                        <label for="remarque" class="form-label">Remarque</label>
                        <textarea class="form-control" id="remarque" name="remarque" rows="5"><?php echo htmlspecialchars($patient['remarque']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Patient Information -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPatientModalLabel">Modifier les informations du patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="includes/patients/patient-update.php">
                <div class="modal-body">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo isset($patient['nom']) ? htmlspecialchars($patient['nom']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo isset($patient['prenom']) ? htmlspecialchars($patient['prenom']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo isset($patient['date_naissance']) ? htmlspecialchars($patient['date_naissance']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="genre" class="form-label">Genre</label>
                            <select class="form-select" id="genre" name="genre" required>
                                <option value="M" <?php echo (isset($patient['genre']) && $patient['genre'] == 'M') ? 'selected' : ''; ?>>Homme</option>
                                <option value="F" <?php echo (isset($patient['genre']) && $patient['genre'] == 'F') ? 'selected' : ''; ?>>Femme</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="cin" class="form-label">CIN</label>
                            <input type="text" class="form-control" id="cin" name="cin" value="<?php echo isset($patient['cin']) ? htmlspecialchars($patient['cin']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo isset($patient['telephone']) ? htmlspecialchars($patient['telephone']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($patient['email']) ? htmlspecialchars($patient['email']) : ''; ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" name="adresse" rows="2"><?php echo isset($patient['adresse']) ? htmlspecialchars($patient['adresse']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
