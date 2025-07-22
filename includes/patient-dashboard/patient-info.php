<div class="row gx-3">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">Informations du Patient</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPatientModal">
                    <i class="ri-edit-box-line me-1"></i> Modifier les Informations
                </button>
            </div>
            <div class="card-body">
                <div class="d-flex">
                    <!-- Stats starts -->
                    <div class="d-flex align-items-center flex-wrap gap-1"><!-- Further reduced gap to gap-1 -->
                        <div class="d-flex align-items-center">
                            <div class="icon-box md bg-primary rounded-5 me-2">
                                <i class="ri-account-circle-line fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.1rem;"><?php h($patient['nom'] . ' ' . $patient['prenom']); ?></h4>
                                <p class="m-0">Nom du Patient</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="icon-box md bg-primary rounded-5 me-2">
                                <i class="ri-<?php echo ($patient['genre'] == 'M') ? 'men' : 'women'; ?>-line fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.1rem;"><?php h(($patient['genre'] == 'M') ? 'Homme' : 'Femme'); ?></h4>
                                <p class="m-0">Genre</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="icon-box md bg-primary rounded-5 me-2">
                                <i class="ri-calendar-line fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.1rem;"><?php h($age); ?></h4>
                                <p class="m-0">Âge du Patient</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="icon-box md bg-primary rounded-5 me-2">
                                <i class="ri-phone-line fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.1rem;"><?php echo isset($patient['telephone']) ? $patient['telephone'] : '-'; ?></h4>
                                <p class="m-0">Téléphone</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="icon-box md bg-primary rounded-5 me-2">
                                <i class="ri-profile-line fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.1rem;"><?php echo isset($patient['cin']) ? $patient['cin'] : '-'; ?></h4>
                                <p class="m-0">CIN</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="icon-box md bg-secondary rounded-5 me-2">
                                <i class="ri-map-pin-line fs-4 text-body"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.1rem;">Adresse</h4>
                                <p class="m-0"><?php echo isset($patient['adresse']) ? $patient['adresse'] : '-'; ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Stats ends -->

                    <!-- Profile image removed as requested -->
                </div>
            </div>
        </div>
    </div>
</div>
