<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- page heading -->
            <!-- <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
                <div>
                    <h5 class="mb-0 fw-semibold">Ad Metrics</h5>
                    <small class="text-muted">Pilih klien untuk melihat metrik iklan</small>
                </div>
            </div> -->

            <!-- flash alerts -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('errors') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- data table card -->
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Company Name</th>
                                <th scope="col">PIC</th>
                                <th scope="col">Platform</th>
                                <th scope="col" class="text-center">Total Ads</th>
                                <th scope="col" class="text-center">Active Ads</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No clients available.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($client->company_name) ?></td>
                                <td><?= htmlspecialchars($client->pic_name ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($client->platforms)): ?>
                                        <?php foreach (explode(',', $client->platforms) as $platform): ?>
                                            <span class="badge text-bg-secondary me-1">
                                                <?= htmlspecialchars(trim($platform)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold"><?= (int)($client->total_ads ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php $active = (int)($client->active_ads ?? 0); ?>
                                    <span class="badge text-bg-<?= $active > 0 ? 'success' : 'secondary' ?>">
                                        <?= $active ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('ad-metrics/detail/' . $client->id) ?>"
                                       class="btn btn-sm btn-primary"
                                       title="See Metrics">
                                        <i class="bi bi-bar-chart-line me-1"></i> See Metrics
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($clients ?? []) ?> client(s)</small>
                </div>
            </div>
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>
