<?php
/**
 * Dashboard practice activity
 */
?>
<!-- Practice Activity -->
<div class="col-xxl-4 col-md-12 col-sm-12">
  <div class="card mb-3" style="height: 100%;">
    <div class="card-header">
      <h5 class="card-title">Activité du Cabinet</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-6">
          <div class="border rounded p-3 text-center">
            <div class="icon-box md bg-primary-subtle text-primary rounded-circle mx-auto mb-2">
              <i class="ri-file-line"></i>
            </div>
            <h3 class="mb-0"><?php echo $monthlyDocuments; ?></h3>
            <p class="mb-0 small">Documents Ajoutés</p>
            <small class="text-muted">Ce mois</small>
          </div>
        </div>
        <div class="col-6">
          <div class="border rounded p-3 text-center">
            <div class="icon-box md bg-success-subtle text-success rounded-circle mx-auto mb-2">
              <i class="ri-user-add-line"></i>
            </div>
            <h3 class="mb-0"><?php echo $newPatients; ?></h3>
            <p class="mb-0 small">Nouveaux Patients</p>
            <small class="text-muted">Ce mois</small>
          </div>
        </div>
        <div class="col-12">
          <div class="border rounded p-3 text-center">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Taux de Complétion</h6>
                <p class="mb-0 small text-muted">Rendez-vous terminés</p>
              </div>
              <h3 class="mb-0 text-success"><?php echo $completionRate; ?>%</h3>
            </div>
            <div class="progress mt-2" style="height: 8px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionRate; ?>%" aria-valuenow="<?php echo $completionRate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
