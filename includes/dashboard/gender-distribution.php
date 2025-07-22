<?php
/**
 * Dashboard gender distribution chart
 */
?>
<!-- Gender Distribution -->
<div class="col-xxl-4 col-md-6 col-sm-12">
  <div class="card mb-3" style="height: 100%;">
    <div class="card-header">
      <h5 class="card-title">Répartition par Genre</h5>
    </div>
    <div class="card-body">
      <!-- Chart Row -->
      <div class="row mb-3">
        <div class="col-12">
          <div style="height: 180px; position: relative;">
            <canvas id="genderChart"></canvas>
          </div>
        </div>
      </div>
      <!-- Stats Row -->
      <div class="row">
        <div class="col-12">
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span><i class="ri-men-line text-primary me-2"></i> Hommes</span>
              <span class="fw-bold"><?php echo $maleCount; ?></span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($totalPatients > 0) ? ($maleCount / $totalPatients) * 100 : 0; ?>%" aria-valuenow="<?php echo $maleCount; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalPatients; ?>"></div>
            </div>
          </div>
          <div>
            <div class="d-flex justify-content-between mb-1">
              <span><i class="ri-women-line text-danger me-2"></i> Femmes</span>
              <span class="fw-bold"><?php echo $femaleCount; ?></span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($totalPatients > 0) ? ($femaleCount / $totalPatients) * 100 : 0; ?>%" aria-valuenow="<?php echo $femaleCount; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalPatients; ?>"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
