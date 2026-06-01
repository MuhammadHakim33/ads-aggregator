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
                <div></div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('ads/connect') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-link-45deg"></i> Connect Ads
                    </a>
                </div>
            </div>

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

            <!-- ad contents table -->
            <?php if (empty($ad_contents)): ?>
                <div class="text-center text-muted py-5 card">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No ads content found.
                </div>
            <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="adTable">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width:25%">Ads</th>
                                <th scope="col">Client</th>
                                <th scope="col">Platform</th>
                                <th scope="col" class="text-center">Metric</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Link</th>
                                <th scope="col" class="text-center">Detail</th>
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
                                    <?php if (!empty($ad->company_name)): ?>
                                        <?= htmlspecialchars($ad->company_name) ?>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">unconnected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-light">
                                        <?= htmlspecialchars(ucfirst($ad->platform)) ?>
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
                                    <?php
                                        $post_url = '#';
                                        $platform = strtolower($ad->platform);
                                        if ($platform === 'facebook') {
                                            $post_url = 'https://www.facebook.com/' . $ad->content_identifier;
                                        } elseif ($platform === 'instagram') {
                                            $post_url = 'https://www.instagram.com/p/' . $ad->content_identifier . '/';
                                        } elseif ($platform === 'youtube') {
                                            $post_url = 'https://www.youtube.com/watch?v=' . $ad->content_identifier;
                                        }
                                    ?>
                                    <?php if ($post_url !== '#'): ?>
                                        <a href="<?= $post_url ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka postingan asli">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($metricCount > 0): ?>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <?php else: ?>
                                        <span class="text-muted small">No metrics</span>
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
                                            <li>
                                                <a class="dropdown-item"
                                                   href="<?= base_url('ads/export/pdf/' . $ad->id) ?>"
                                                   target="_blank">
                                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                    Export PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="<?= base_url('ads/export/excel/' . $ad->id) ?>">
                                                    <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>
                                                    Export Excel
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php if ($metricCount > 0): ?>
                            <!-- metrics collapse row -->
                            <tr class="ad-metrics-row bg-body-secondary" data-platform="<?= htmlspecialchars($ad->platform) ?>" data-adtype="<?= htmlspecialchars($ad->ad_type) ?>" data-active="<?= $ad->is_active ? '1' : '0' ?>">
                                <td colspan="8" class="p-0 border-top-0">
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
                <!-- Pagination Footer -->
                <?php if (!empty($pagination)): ?>
                <div class="card-footer d-flex justify-content-end align-items-center">
                    <?= $pagination ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
(function () {
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
})();
</script>

<?php $this->load->view('templates/footer'); ?>
