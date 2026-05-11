<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="mb-4">
                <h5 class="mb-0">Dashboard</h5>
                <small class="text-muted">Welcome back, <?= htmlspecialchars($current_account['name']) ?></small>
            </div>
            <!-- Summary Cards -->
            <div class="row g-3">
                <div class="col-sm-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-person-check fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= number_format($total_active_clients) ?></div>
                                <div class="text-muted small mt-1">Client Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                <i class="bi bi-collection fs-4 text-success"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= number_format($total_active_ads) ?></div>
                                <div class="text-muted small mt-1">Ads Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>
