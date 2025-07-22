<?php
/**
 * Consultation-related modals
 */
?>
<!-- Modal Add Consultation -->
<div class="modal fade" id="addConsultationModal" tabindex="-1" aria-labelledby="addConsultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addConsultationModalLabel">Ajouter une Nouvelle Consultation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="includes/consultations/add-consultation.php">
                <div class="modal-body">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    <?php echo generateCSRFToken(); ?>
                    <div class="mb-3">
                        <label for="date_consultation" class="form-label">Date de consultation</label>
                        <input type="datetime-local" class="form-control" id="date_consultation" name="date_consultation" required>
                    </div>
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif</label>
                        <textarea class="form-control" id="motif" name="motif" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="diagnostic" class="form-label">Diagnostic</label>
                        <textarea class="form-control" id="diagnostic" name="diagnostic" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="traitement" class="form-label">Traitement</label>
                        <textarea class="form-control" id="traitement" name="traitement" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="cout" class="form-label">Coût (DH)</label>
                        <input type="number" step="0.01" class="form-control" id="cout" name="cout" required>
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

<!-- Modal Edit Consultation -->
<div class="modal fade" id="editConsultationModal" tabindex="-1" aria-labelledby="editConsultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editConsultationModalLabel">Modifier la Consultation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="includes/consultations/edit-consultation.php">
                <div class="modal-body">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    <input type="hidden" id="edit_consult_id" name="consult_id" value="">
                    <?php echo generateCSRFToken(); ?>
                    <div class="mb-3">
                        <label for="edit_date_consultation" class="form-label">Date de consultation</label>
                        <input type="datetime-local" class="form-control" id="edit_date_consultation" name="date_consultation" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_motif" class="form-label">Motif</label>
                        <textarea class="form-control" id="edit_motif" name="motif" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_diagnostic" class="form-label">Diagnostic</label>
                        <textarea class="form-control" id="edit_diagnostic" name="diagnostic" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_traitement" class="form-label">Traitement</label>
                        <textarea class="form-control" id="edit_traitement" name="traitement" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cout" class="form-label">Coût (DH)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_cout" name="cout" required>
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

<!-- Modal Delete Consultation -->
<div class="modal fade" id="deleteConsultationModal" tabindex="-1" aria-labelledby="deleteConsultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConsultationModalLabel">Confirmer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer cette consultation ?
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Non</button>
                    <form id="deleteConsultForm" method="post" action="includes/consultations/delete-consultation.php">
                        <input type="hidden" id="consultIdToDelete" name="consult_id" value="">
                        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                        <?php echo generateCSRFToken(); ?>
                        <button type="submit" class="btn btn-danger">Oui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
