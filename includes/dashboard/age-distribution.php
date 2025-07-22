<?php
/**
 * Dashboard age distribution chart
 */
?>
<!-- Age Distribution Row -->
<div class="row gx-3">
  <div class="col-xxl-12 col-sm-12">
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="card-title">Répartition par Âge</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <div style="height: 180px; position: relative;">
              <canvas id="ageChart"></canvas>
            </div>
          </div>
          <div class="col-md-4">
            <div class="d-flex flex-column justify-content-center h-100">
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span><i class="ri-user-line text-info me-2"></i> Moins de 18 ans</span>
                  <span class="fw-bold"><?php echo $ageDistribution['under_18']; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo ($totalPatients > 0) ? ($ageDistribution['under_18'] / $totalPatients) * 100 : 0; ?>%" aria-valuenow="<?php echo $ageDistribution['under_18']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalPatients; ?>"></div>
                </div>
              </div>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span><i class="ri-user-line text-primary me-2"></i> 18-40 ans</span>
                  <span class="fw-bold"><?php echo $ageDistribution['age_18_40']; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($totalPatients > 0) ? ($ageDistribution['age_18_40'] / $totalPatients) * 100 : 0; ?>%" aria-valuenow="<?php echo $ageDistribution['age_18_40']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalPatients; ?>"></div>
                </div>
              </div>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span><i class="ri-user-line text-success me-2"></i> 41-60 ans</span>
                  <span class="fw-bold"><?php echo $ageDistribution['age_41_60']; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($totalPatients > 0) ? ($ageDistribution['age_41_60'] / $totalPatients) * 100 : 0; ?>%" aria-valuenow="<?php echo $ageDistribution['age_41_60']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalPatients; ?>"></div>
                </div>
              </div>
              <div>
                <div class="d-flex justify-content-between mb-1">
                  <span><i class="ri-user-line text-warning me-2"></i> Plus de 60 ans</span>
                  <span class="fw-bold"><?php echo $ageDistribution['over_60']; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($totalPatients > 0) ? ($ageDistribution['over_60'] / $totalPatients) * 100 : 0; ?>%" aria-valuenow="<?php echo $ageDistribution['over_60']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalPatients; ?>"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
