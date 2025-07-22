<?php
/**
 * Dashboard statistics cards
 */
?>
<!-- Row starts - Statistics Cards -->
<div class="row gx-3">
  <!-- Total Patients Card -->
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="p-2 border border-success rounded-circle me-3">
            <div class="icon-box md bg-success-subtle rounded-5">
              <i class="ri-user-line fs-4 text-success"></i>
            </div>
          </div>
          <div class="d-flex flex-column">
            <h2 class="lh-1"><?php echo $totalPatients; ?></h2>
            <p class="m-0">Patients Totaux</p>
          </div>
        </div>
        <div class="d-flex align-items-end justify-content-between mt-1">
          <a class="text-success" href="patients-list.php">
            <span>Voir Tous</span>
            <i class="ri-arrow-right-line text-success ms-1"></i>
          </a>
          <div class="text-end">
            <p class="mb-0 text-success">+<?php echo $newPatients; ?></p>
            <span class="badge bg-success-subtle text-success small">ce mois</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Upcoming Appointments Card -->
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="p-2 border border-primary rounded-circle me-3">
            <div class="icon-box md bg-primary-subtle rounded-5">
              <i class="ri-calendar-line fs-4 text-primary"></i>
            </div>
          </div>
          <div class="d-flex flex-column">
            <h2 class="lh-1"><?php echo $upcomingAppointments; ?></h2>
            <p class="m-0">Rendez-vous à venir</p>
          </div>
        </div>
        <div class="d-flex align-items-end justify-content-between mt-1">
          <a class="text-primary" href="appointments-list.php">
            <span>Voir Tous</span>
            <i class="ri-arrow-right-line ms-1"></i>
          </a>
          <div class="text-end">
            <p class="mb-0 text-primary"><?php echo $todayAppointments; ?> aujourd'hui</p>
            <span class="badge bg-primary-subtle text-primary small">prochains 7 jours</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Completed Appointments Card -->
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="p-2 border border-info rounded-circle me-3">
            <div class="icon-box md bg-info-subtle rounded-5">
              <i class="ri-task-line fs-4 text-info"></i>
            </div>
          </div>
          <div class="d-flex flex-column">
            <h2 class="lh-1"><?php echo $completed; ?></h2>
            <p class="m-0">Rendez-vous Terminés</p>
          </div>
        </div>
        <div class="d-flex align-items-end justify-content-between mt-1">
          <a class="text-info" href="appointments-list.php?status=completed">
            <span>Voir Tous</span>
            <i class="ri-arrow-right-line ms-1"></i>
          </a>
          <div class="text-end">
            <p class="mb-0 text-info"><?php echo $completionRate; ?>%</p>
            <span class="badge bg-info-subtle text-info small">taux de complétion</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Revenue Card -->
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="p-2 border border-warning rounded-circle me-3">
            <div class="icon-box md bg-warning-subtle rounded-5">
              <i class="ri-money-euro-circle-line fs-4 text-warning"></i>
            </div>
          </div>
          <div class="d-flex flex-column">
            <h2 class="lh-1"><?php echo number_format($monthlyRevenue, 2); ?> MAD</h2>
            <p class="m-0">Revenus Mensuels</p>
          </div>
        </div>
        <div class="d-flex align-items-end justify-content-between mt-1">
          <span class="text-muted">Coût moyen</span>
          <div class="text-end">
            <p class="mb-0 text-warning"><?php echo number_format($avgCost, 2); ?> MAD</p>
            <span class="badge bg-warning-subtle text-warning small">par consultation</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Row ends -->
