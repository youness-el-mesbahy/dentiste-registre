<?php
/**
 * Dashboard appointment status distribution chart
 */
?>
<!-- Appointment Status Distribution -->
<div class="col-xxl-4 col-md-6 col-sm-12">
  <div class="card mb-3" style="height: 100%;">
    <div class="card-header">
      <h5 class="card-title">Statut des Rendez-vous</h5>
    </div>
    <div class="card-body">
      <div style="height: 150px; position: relative;">
        <canvas id="statusChart"></canvas>
      </div>
      <div class="mt-3">
        <div class="d-flex justify-content-between text-center">
          <div>
            <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mx-auto mb-2" style="width: 30px; height: 30px;">
              <i class="ri-calendar-line"></i>
            </div>
            <p class="mb-0 small">Planifiés</p>
            <h6><?php echo $scheduled; ?></h6>
          </div>
          <div>
            <div class="d-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mx-auto mb-2" style="width: 30px; height: 30px;">
              <i class="ri-check-line"></i>
            </div>
            <p class="mb-0 small">Terminés</p>
            <h6><?php echo $completed; ?></h6>
          </div>
          <div>
            <div class="d-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mx-auto mb-2" style="width: 30px; height: 30px;">
              <i class="ri-close-line"></i>
            </div>
            <p class="mb-0 small">Annulés</p>
            <h6><?php echo $cancelled; ?></h6>
          </div>
          <div>
            <div class="d-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mx-auto mb-2" style="width: 30px; height: 30px;">
              <i class="ri-error-warning-line"></i>
            </div>
            <p class="mb-0 small">Absences</p>
            <h6><?php echo $noShow; ?></h6>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
