<?php
/**
 * Dashboard recent consultations
 */
?>
<!-- Recent Consultations -->
<div class="col-xxl-4 col-sm-12">
  <div class="card mb-3" style="height: 100%;">
    <div class="card-header">
      <h5 class="card-title">Consultations Récentes</h5>
    </div>
    <div class="card-body p-0">
      <ul class="list-group list-group-flush">
        <?php
        if (mysqli_num_rows($recentConsultationsResult) > 0) {
          while ($consultation = mysqli_fetch_assoc($recentConsultationsResult)) {
            $consultDate = new DateTime($consultation['date_consultation']);
            $formattedDate = $consultDate->format('d/m/Y');
        ?>
        <li class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0"><?php echo htmlspecialchars($consultation['prenom'] . ' ' . $consultation['nom']); ?></h6>
              <small class="text-muted"><?php echo htmlspecialchars(substr($consultation['motif'], 0, 50)) . (strlen($consultation['motif']) > 50 ? '...' : ''); ?></small>
            </div>
            <div class="text-end">
              <div class="text-primary"><?php echo number_format($consultation['cout'], 2); ?> MAD</div>
              <small class="text-muted"><?php echo $formattedDate; ?></small>
            </div>
          </div>
        </li>
        <?php
          }
        } else {
          echo '<li class="list-group-item text-center">Aucune consultation récente</li>';
        }
        ?>
      </ul>
    </div>
  </div>
</div>
