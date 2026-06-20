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
                <form method="GET" action="<?= current_url() ?>" class="d-flex gap-2 align-items-center mb-0">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" <?= (isset($filters['status']) && $filters['status'] === '1') ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (isset($filters['status']) && $filters['status'] === '0') ? 'selected' : '' ?>>Inactive</option>
                    </select>

                    <select name="platform" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Platforms</option>
                        <?php foreach($platform_labels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= (isset($filters['platform']) && $filters['platform'] == $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="has_campaign" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Campaign Status</option>
                        <option value="1" <?= (isset($filters['has_campaign']) && $filters['has_campaign'] === '1') ? 'selected' : '' ?>>Connected</option>
                        <option value="0" <?= (isset($filters['has_campaign']) && $filters['has_campaign'] === '0') ? 'selected' : '' ?>>Unconnected</option>
                    </select>

                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search ads..." value="<?= html_escape($filters['q'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    
                    <?php if(!empty($filters['q']) || (isset($filters['status']) && $filters['status'] !== '') || !empty($filters['platform']) || (isset($filters['has_campaign']) && $filters['has_campaign'] !== '')): ?>
                        <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </form>
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
                                    <th scope="col">Campaign</th>
                                    <th scope="col">Platform</th>
                                    <th scope="col" class="text-center">Metric</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-center">Link</th>
                                    <th class="text-center" style="width: 80px;">Detail</th>
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
                                            <div class="fw-medium"><?= $ad->title ?? '-' ?></div>
                                            <small class="text-muted font-monospace"><?= $ad->content_identifier ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($ad->campaign_name)): ?>
                                                <div class="fw-medium text-dark"><?= $ad->campaign_name ?></div>
                                                <div class="text-muted small"><?= $ad->company_name ?></div>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">unconnected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-light">
                                                <?= $platform_labels[strtolower($ad->platform)] ?? ucfirst($ad->platform) ?>
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
                                            } elseif ($platform === 'ga4') {
                                                $post_url = 'https://' . $ad->content_identifier;
                                            }
                                            ?>
                                            <?php if ($post_url !== '#'): ?>
                                                <a href="<?= $post_url ?>" target="_blank" class="btn btn-sm btn-outline-primary"
                                                    title="Buka postingan asli">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($metricCount > 0): ?>
                                                <button class="btn btn-sm btn-outline-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>"
                                                    aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">No metrics</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($metricCount > 0): ?>
                                        <!-- metrics collapse row -->
                                        <tr class="ad-metrics-row bg-body-secondary" data-platform="<?= ($ad->platform) ?>"
                                            data-active="<?= $ad->is_active ? '1' : '0' ?>">
                                            <td colspan="7" class="p-0 border-top-0">
                                                <div class="collapse" id="<?= $collapseId ?>">
                                                    <div class="p-3">
                                                        <table class="table table-sm table-bordered mb-0 bg-white rounded">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th class="text-muted small fw-semibold" style="width:40%">
                                                                        Metric</th>
                                                                    <th class="text-muted small fw-semibold text-end"
                                                                        style="width:30%">Value</th>
                                                                    <th class="text-muted small fw-semibold" style="width:30%">
                                                                        Updated</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($ad->metrics as $metric): ?>
                                                                    <tr>
                                                                        <td class="fw-medium small">
                                                                            <?= (ucwords(str_replace('_', ' ', $metric->metric_name))) ?>
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