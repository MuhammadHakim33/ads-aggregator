<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row g-3 mb-4">
                <!-- Contract Active -->
                <div class="col-sm-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                <i class="bi bi-file-earmark-check fs-4 text-success"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_contracts_active ?></div>
                                <div class="text-muted small mt-1">Contract Active</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Ongoing Campaign -->
                <div class="col-sm-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-megaphone fs-4 text-warning"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_campaigns_running ?></div>
                                <div class="text-muted small mt-1">Ongoing Campaign</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Clients Handled -->
                <div class="col-sm-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_clients_handled ?></div>
                                <div class="text-muted small mt-1">Client Handled</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert unconnected ads -->
            <?php if ($total_unconnected_ads > 0): ?>
                <div class="alert alert-warning alert-dismissible d-flex align-items-center justify-content-between gap-3 fade show mb-4"
                    role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>
                            <strong><?= $total_unconnected_ads ?> ads</strong>
                            are not connected to any campaign and will not be reported.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('ads') ?>" class="btn btn-warning btn-sm text-nowrap">
                            <i class="bi bi-link-45deg me-1"></i> Connect Now
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>