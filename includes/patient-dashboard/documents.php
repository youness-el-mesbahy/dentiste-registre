<div class="row gx-3">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">Documents</h5>
                <form action="upload-document.php" method="post" enctype="multipart/form-data" class="d-flex gap-2">
                    <input type="hidden" name="patient_id" value="<?php h($patient_id); ?>">
                    <?php echo generateCSRFToken(); ?>
                    <input type="file" name="document" class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-primary btn-sm">Téléverser</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-outer">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table align-middle truncate m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fichier</th>
                                    <th>Nom du Fichier</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($docsResult) > 0) {
                                    $count = 1;
                                    while ($doc = mysqli_fetch_assoc($docsResult)) {
                                        // Determine icon based on file type
                                        $icon = 'ri-file-line';
                                        if (strpos($doc['file_type'], 'pdf') !== false) {
                                            $icon = 'ri-file-pdf-line';
                                        } elseif (strpos($doc['file_type'], 'word') !== false || strpos($doc['file_type'], 'doc') !== false) {
                                            $icon = 'ri-file-word-line';
                                        } elseif (strpos($doc['file_type'], 'excel') !== false || strpos($doc['file_type'], 'sheet') !== false || strpos($doc['file_type'], 'csv') !== false) {
                                            $icon = 'ri-file-excel-2-line';
                                        } elseif (strpos($doc['file_type'], 'image') !== false) {
                                            $icon = 'ri-image-line';
                                        }
                                ?>
                                <tr>
                                    <td><?php h($count++); ?></td>
                                    <td>
                                        <div class="icon-box md bg-primary rounded-2">
                                            <i class="<?php echo $icon; ?>"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?php h($doc['file_path']); ?>" class="link-primary text-truncate" target="_blank">
                                            <?php h($doc['file_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php h(date('M-d, Y', strtotime($doc['uploaded_at']))); ?></td>
                                    <td>
                                        <div class="d-inline-flex gap-1">
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#delRow" data-doc-id="<?php echo $doc['id']; ?>">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            <a href="<?php echo $doc['file_path']; ?>" download class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-title="Télécharger le Document">
                                                <i class="ri-file-download-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucun document trouvé pour ce patient</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
