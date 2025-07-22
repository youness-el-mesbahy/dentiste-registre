<?php
/**
 * Dashboard welcome section
 */
?>
<!-- Row starts - Welcome Section -->
<div class="row gx-3">
  <div class="col-xxl-12 col-sm-12">
    <div class="card mb-3 bg-primary">
      <div class="card-body">
        <div class="py-4 px-3 text-white">
          <h6>Bonjour,</h6>
          <h2><?php echo $_SESSION['nom'] ?? 'Docteur'; ?></h2>
          <h5>Votre programme aujourd'hui</h5>
          <div class="mt-4 d-flex gap-3">
            <div class="d-flex align-items-center">
              <div class="icon-box lg bg-white rounded-3 me-3">
                <i class="ri-calendar-check-line fs-4 text-primary"></i>
              </div>
              <div class="d-flex flex-column">
                <h2 class="m-0 lh-1"><?php echo $todayAppointments; ?></h2>
                <p class="m-0">Rendez-vous</p>
              </div>
            </div>
            <div class="d-flex align-items-center">
              <div class="icon-box lg bg-white rounded-3 me-3">
                <i class="ri-user-add-line fs-4 text-primary"></i>
              </div>
              <div class="d-flex flex-column">
                <h2 class="m-0 lh-1"><?php echo $newPatients; ?></h2>
                <p class="m-0">Nouveaux Patients</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Row ends -->
