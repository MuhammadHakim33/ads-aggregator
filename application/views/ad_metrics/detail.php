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
            <div class="d-flex justify-content-between align-items-center pb-2 mb-4">
                <div>
                    <a href="<?= base_url('ad-metrics') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <h5 class="mb-0 fw-semibold"><?= htmlspecialchars($client->company_name ?? '-') ?></h5>
                    <small class="text-muted">
                        PIC: <?= htmlspecialchars($client->pic_name ?? '-') ?>
                    </small>
                </div>
                <!-- summary badges -->
                <div class="d-flex gap-2">
                    <div class="card border-0 shadow-sm px-3 py-2 text-center" style="min-width:90px">
                        <div class="fs-4 fw-bold text-primary" id="summary-total">0</div>
                        <small class="text-muted">Total Ads</small>
                    </div>
                    <div class="card border-0 shadow-sm px-3 py-2 text-center" style="min-width:90px">
                        <div class="fs-4 fw-bold text-success" id="summary-active">0</div>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="card border-0 shadow-sm px-3 py-2 text-center" style="min-width:90px">
                        <div class="fs-4 fw-bold text-secondary" id="summary-inactive">0</div>
                        <small class="text-muted">Inactive</small>
                    </div>
                </div>
            </div>

            <!-- ad contents table -->
            <?php if (empty($ad_contents)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No ads content for this client.
                </div>
            <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="adTable">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width:30%">Ads</th>
                                <th scope="col">Platform</th>
                                <th scope="col">Type</th>
                                <th scope="col" class="text-center">Metric Count</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Metric Detail</th>
                                <th scope="col" class="text-center">Export</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ad_contents as $i => $ad): ?>
                            <?php
                                $statusClass = $ad->is_active ? 'success' : 'secondary';
                                $statusText = $ad->is_active ? 'Active' : 'Inactive';
                                $collapseId = 'metrics-' . $ad->id;
                                $metricCount = count($ad->metrics ?? []);
                            ?>
                            <tr class="ad-row" data-active="<?= $ad->is_active ? '1' : '0' ?>">
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($ad->title ?? '-') ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($ad->content_identifier) ?></small>
                                </td>
                                <td>
                                    <span class="badge text-bg-light">
                                        <?= htmlspecialchars(ucfirst($ad->platform)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-bg-light">
                                        <?= htmlspecialchars(ucfirst($ad->ad_type)) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($metricCount > 0): ?>
                                        <span class="badge text-bg-light rounded-pill"><?= $metricCount ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($metricCount > 0): ?>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <?php else: ?>
                                        <span class="text-muted small">No metric data.</span>
                                    <?php endif; ?>
                                </td>
                                <!-- export dropdown -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            id="exportDropdown-<?= $ad->id ?>"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            title="Export">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown-<?= $ad->id ?>">
                                            <?php foreach ($this->export_registry->enabled_keys() as $format): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url("ad-metrics/export/{$format}/{$ad->id}") ?>">
                                                        Export <?= strtoupper($format) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php if ($metricCount > 0): ?>
                            <!-- metrics collapse row -->
                            <tr class="ad-metrics-row bg-body-secondary" data-platform="<?= htmlspecialchars($ad->platform) ?>" data-adtype="<?= htmlspecialchars($ad->ad_type) ?>" data-active="<?= $ad->is_active ? '1' : '0' ?>">
                                <td colspan="7" class="p-0 border-top-0">
                                    <div class="collapse" id="<?= $collapseId ?>">
                                        <div class="p-3">
                                            <table class="table table-sm table-bordered mb-0 bg-white rounded">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-muted small fw-semibold" style="width:40%">Metric</th>
                                                        <th class="text-muted small fw-semibold text-end" style="width:30%">Value</th>
                                                        <th class="text-muted small fw-semibold" style="width:30%">Updated</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($ad->metrics as $metric): ?>
                                                    <tr>
                                                        <td class="fw-medium small">
                                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $metric->metric_name))) ?>
                                                        </td>
                                                        <td class="text-end font-monospace small">
                                                            <?= number_format($metric->metric_value, 2, ',', '.') ?>
                                                        </td>
                                                        <td class="text-muted small">
                                                            <?= date('d M Y H:i', strtotime($metric->updated_at)) ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="tableCount">
                        Show all <?= count($ad_contents) ?> contents
                    </small>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
(function () {
    const rows = document.querySelectorAll('.ad-row');
    const summaryTotal = document.getElementById('summary-total');
    const summaryActive = document.getElementById('summary-active');
    const summaryInactive = document.getElementById('summary-inactive');

    // Update summary counters
    function updateSummary() {
        let total = 0, active = 0, inactive = 0;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                total++;
                if (row.dataset.active === '1') active++;
                else inactive++;
            }
        });
        summaryTotal.textContent = total;
        summaryActive.textContent = active;
        summaryInactive.textContent = inactive;
    }

    // rotate chevron icon on collapse toggle
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
        const targetId = btn.getAttribute('data-bs-target');
        const collapseEl = document.querySelector(targetId);
        if (!collapseEl) return;
        collapseEl.addEventListener('show.bs.collapse', () => {
            btn.querySelector('i')?.classList.replace('bi-chevron-down', 'bi-chevron-up');
        });
        collapseEl.addEventListener('hide.bs.collapse', () => {
            btn.querySelector('i')?.classList.replace('bi-chevron-up', 'bi-chevron-down');
        });
    });

    // init summary on load
    updateSummary();
})();
</script>

<?php $this->load->view('templates/footer'); ?>
