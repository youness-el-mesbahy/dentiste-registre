<div class="row gx-3">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">Remarque</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editRemarqueModal">
                    <i class="ri-edit-box-line me-1"></i> Modifier
                </button>
            </div>
            <div class="card-body">
                <div class="p-2">
                    <?php if (!empty($patient['remarque'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($patient['remarque'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">Aucune remarque pour ce patient.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
