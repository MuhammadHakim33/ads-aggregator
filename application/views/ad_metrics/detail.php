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

            <!-- filter bar -->
            <div class="card mb-4">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <i class="bi bi-funnel text-muted me-1"></i>
                            <span class="text-muted small fw-medium">Filter:</span>
                        </div>
                        <div class="col-auto">
                            <select id="filterPlatform" class="form-select form-select-sm">
                                <option value="">All Platform</option>
                                <?php
                                    $platforms = array_unique(array_column($ad_contents ?? [], 'platform'));
                                    sort($platforms);
                                    foreach ($platforms as $p):
                                ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars(ucfirst($p)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select id="filterAdType" class="form-select form-select-sm">
                                <option value="">Semua Tipe</option>
                                <option value="article">Article</option>
                                <option value="banner">Banner</option>
                                <option value="video">Video</option>
                                <option value="social">Social</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-auto ms-auto">
                            <button id="btnResetFilter" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ad contents table -->
            <?php if (empty($ad_contents)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Tidak ada data iklan untuk klien ini.
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ad_contents as $i => $ad): ?>
                            <?php
                                $statusClass = $ad->is_active ? 'success' : 'secondary';
                                $statusText = $ad->is_active ? 'Active' : 'Inactive';
                                $collapseId = 'metrics-' . $ad->id;
                                $metricCount = count($ad->metrics ?? []);

                                // badge color per platform
                                // $platformColors = [
                                //     'facebook' => 'primary',
                                //     'instagram' => 'danger',
                                //     'youtube' => 'danger',
                                //     'gam' => 'warning',
                                //     'ga4' => 'info',
                                //     'tiktok' => 'dark',
                                // ];
                                // $pColor = $platformColors[strtolower($ad->platform)] ?? 'secondary';

                                // badge color per ad_type
                                // $typeColors = [
                                //     'article' => 'info',
                                //     'banner' => 'warning',
                                //     'video' => 'danger',
                                //     'social' => 'primary',
                                // ];
                                // $tColor = $typeColors[$ad->ad_type] ?? 'secondary';
                            ?>
                            <tr class="ad-row"
                                data-platform="<?= htmlspecialchars($ad->platform) ?>"
                                data-adtype="<?= htmlspecialchars($ad->ad_type) ?>"
                                data-active="<?= $ad->is_active ? '1' : '0' ?>">
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
                                    <button class="btn btn-sm btn-outline-primary"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?= $collapseId ?>"
                                            aria-expanded="false"
                                            aria-controls="<?= $collapseId ?>">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <?php else: ?>
                                        <span class="text-muted small">No metric data.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($metricCount > 0): ?>
                            <!-- metrics collapse row -->
                            <tr class="ad-metrics-row bg-body-secondary"
                                data-platform="<?= htmlspecialchars($ad->platform) ?>"
                                data-adtype="<?= htmlspecialchars($ad->ad_type) ?>"
                                data-active="<?= $ad->is_active ? '1' : '0' ?>">
                                <td colspan="6" class="p-0 border-top-0">
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
                        Menampilkan <?= count($ad_contents) ?> konten iklan
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
    const metricsRows = document.querySelectorAll('.ad-metrics-row');
    const filterPlatform = document.getElementById('filterPlatform');
    const filterAdType = document.getElementById('filterAdType');
    const filterStatus = document.getElementById('filterStatus');
    const tableCount = document.getElementById('tableCount');
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
        summaryTotal.textContent   = total;
        summaryActive.textContent  = active;
        summaryInactive.textContent= inactive;
        if (tableCount) tableCount.textContent = `Menampilkan ${total} konten iklan`;
    }

    function applyFilter() {
        const platform = filterPlatform.value;
        const adType   = filterAdType.value;
        const status   = filterStatus.value;

        rows.forEach((row, idx) => {
            const matchPlatform = !platform || row.dataset.platform === platform;
            const matchType     = !adType   || row.dataset.adtype   === adType;
            const matchStatus   = !status   || row.dataset.active    === status;
            const show          = matchPlatform && matchType && matchStatus;

            row.style.display = show ? '' : 'none';

            // also hide the paired metrics collapse row
            if (metricsRows[idx]) {
                if (!show) {
                    // close collapse if hidden
                    const collapseEl = metricsRows[idx].querySelector('.collapse');
                    if (collapseEl && collapseEl.classList.contains('show')) {
                        bootstrap.Collapse.getInstance(collapseEl)?.hide();
                    }
                }
                metricsRows[idx].style.display = show ? '' : 'none';
            }
        });

        updateSummary();
    }

    filterPlatform.addEventListener('change', applyFilter);
    filterAdType.addEventListener('change', applyFilter);
    filterStatus.addEventListener('change', applyFilter);

    document.getElementById('btnResetFilter').addEventListener('click', function () {
        filterPlatform.value = '';
        filterAdType.value   = '';
        filterStatus.value   = '';
        applyFilter();
    });

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
