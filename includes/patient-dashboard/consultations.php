<div class="row gx-3">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">Consultations</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addConsultationModal">
                    Ajouter Consultation
                </button>
            </div>
            <div class="card-body">
                <!-- Consultations starts -->
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table align-middle truncate m-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Motif</th>
                                <th>Diagnostic</th>
                                <th>Traitement</th>
                                <th>Coût</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($consultResult) > 0) {
                                while ($consult = mysqli_fetch_assoc($consultResult)) {
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($consult['date_consultation'])); ?></td>
                                <td><?php echo $consult['motif']; ?></td>
                                <td><?php echo $consult['diagnostic']; ?></td>
                                <td><?php echo $consult['traitement']; ?></td>
                                <td><?php echo number_format($consult['cout'], 2); ?> DH</td>
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#deleteConsultationModal" data-consult-id="<?php echo $consult['id']; ?>">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editConsultationModal" 
                                            data-consult-id="<?php echo $consult['id']; ?>"
                                            data-date="<?php echo $consult['date_consultation']; ?>"
                                            data-motif="<?php echo htmlspecialchars($consult['motif']); ?>"
                                            data-diagnostic="<?php echo htmlspecialchars($consult['diagnostic']); ?>"
                                            data-traitement="<?php echo htmlspecialchars($consult['traitement']); ?>"
                                            data-cout="<?php echo $consult['cout']; ?>">
                                            <i class="ri-edit-box-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucune consultation trouvée</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Consultations ends -->
            </div>
        </div>
    </div>
</div>
