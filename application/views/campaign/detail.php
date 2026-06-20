<?php $this->load->view('templates/header'); ?>

<?php
$today = date('Y-m-d');
$is_active = $campaign->is_active;
$is_running = $is_active && $campaign->start_date <= $today && $campaign->end_date >= $today;
$is_upcoming = $is_active && $campaign->start_date > $today;
$is_ended = $campaign->end_date < $today;

if (!$is_active) {
    $status_label = 'Inactive';
    $status_color = 'secondary';
} elseif ($is_ended) {
    $status_label = 'Ended';
    $status_color = 'danger';
} elseif ($is_running) {
    $status_label = 'Running';
    $status_color = 'success';
} elseif ($is_upcoming) {
    $status_label = 'Upcoming';
    $status_color = 'info';
} else {
    $status_label = '-';
    $status_color = 'secondary';
}

$ads = $campaign->ads ?? [];
?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- breadcrumb / back -->
            <div class="mb-3 d-flex align-items-center gap-2">
                <a href="<?= base_url('campaign') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <span class="text-muted small">/</span>
                <span class="text-muted small">Campaign Detail</span>
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

            <div class="row g-4">

                <!-- LEFT: Campaign Info -->
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Campaign Information</h6>
                            <a href="<?= base_url('campaign/edit/' . $campaign->id) ?>"
                                class="btn btn-sm btn-outline-secondary" title="Edit Campaign">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                        </div>
                        <div class="card-body">

                            <!-- Status badge -->
                            <div class="mb-4">
                                <span class="badge bg-<?= $status_color ?> fs-6 px-3 py-2">
                                    <?php if ($is_running): ?>
                                        <i class="bi bi-play-circle me-1"></i>
                                    <?php elseif ($is_upcoming): ?>
                                        <i class="bi bi-clock me-1"></i>
                                    <?php elseif ($is_ended): ?>
                                        <i class="bi bi-stop-circle me-1"></i>
                                    <?php else: ?>
                                        <i class="bi bi-pause-circle me-1"></i>
                                    <?php endif; ?>
                                    <?= $status_label ?>
                                </span>
                            </div>

                            <!-- Campaign name -->
                            <div class="mb-3">
                                <div class="text-muted"
                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Campaign Name</div>
                                <div class="fw-semibold fs-5 mt-1"><?= htmlspecialchars(ucwords($campaign->name)) ?>
                                </div>
                            </div>

                            <!-- Description -->
                            <?php if ($campaign->description): ?>
                                <div class="mb-3">
                                    <div class="text-muted"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                        Description</div>
                                    <div class="mt-1 text-secondary" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($campaign->description) ?></div>
                                </div>
                            <?php endif; ?>

                            <hr class="my-3">

                            <!-- Contract -->
                            <div class="mb-3">
                                <div class="text-muted"
                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Contract</div>
                                <div class="mt-1 fw-medium"><?= htmlspecialchars($campaign->contract_number) ?></div>
                                <?php if (isset($campaign->contract_value)): ?>
                                    <div class="text-muted small">Rp
                                        <?= number_format($campaign->contract_value, 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Client -->
                            <div class="mb-3">
                                <div class="text-muted"
                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Client</div>
                                <div class="mt-1 fw-medium"><?= htmlspecialchars(ucwords($campaign->client_name)) ?>
                                </div>
                                <?php if (isset($campaign->client_pic) && $campaign->client_pic): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($campaign->client_pic) ?></div>
                                <?php endif; ?>
                            </div>

                            <hr class="my-3">

                            <!-- Dates -->
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="text-muted"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                        Start Date</div>
                                    <div class="mt-1 fw-medium"><?= date('d M Y', strtotime($campaign->start_date)) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                        End Date</div>
                                    <div class="mt-1 fw-medium"><?= date('d M Y', strtotime($campaign->end_date)) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Duration progress bar -->
                            <?php
                            $start_ts = strtotime($campaign->start_date);
                            $end_ts = strtotime($campaign->end_date);
                            $today_ts = strtotime($today);
                            $total_days = max(1, ($end_ts - $start_ts) / 86400);
                            $elapsed = ($today_ts - $start_ts) / 86400;
                            $pct = max(0, min(100, round(($elapsed / $total_days) * 100)));
                            ?>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted" style="font-size: 0.75rem;">Duration Progress</span>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= $pct ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $status_color ?>" style="width: <?= $pct ?>%"
                                        role="progressbar"></div>
                                </div>
                            </div>

                        </div>

                        <!-- Export actions -->
                        <div class="card-footer d-flex gap-2">
                            <a href="<?= base_url('campaign/export/pdf/' . $campaign->id) ?>"
                                class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                            </a>
                            <a href="<?= base_url('campaign/export/excel/' . $campaign->id) ?>"
                                class="btn btn-sm btn-outline-success">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Ads & Metrics -->
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-collection-play me-2 text-primary"></i>
                                Ad Contents
                                <span class="badge bg-primary bg-opacity-10 text-primary ms-2"><?= count($ads) ?></span>
                            </h6>
                        </div>

                        <?php if (empty($ads)): ?>
                            <div class="card-body text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                <div>No ad contents linked to this campaign.</div>
                            </div>
                        <?php else: ?>
                            <!-- Ads flat list -->
                            <?php foreach ($ads as $i => $ad): ?>
                                <?php
                                $metrics = $ad->metrics ?? [];
                                ?>
                                <!-- Ad item -->
                                <div class="<?= $i > 0 ? 'border-top' : '' ?> px-3 py-3">
                                    <!-- Ad header -->
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 flex-shrink-0">
                                            <i class="bi bi-play-btn text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($ad->title ?? ('Ad #' . $ad->id)) ?>
                                            </div>
                                            <?php if (!empty($ad->platform)): ?>
                                                <?php
                                                $picons = [
                                                    'facebook' => 'bi-facebook text-primary',
                                                    'instagram' => 'bi-instagram text-danger',
                                                    'youtube' => 'bi-youtube text-danger',
                                                    'ga4' => 'bi-bar-chart-line text-warning',
                                                    'gam' => 'bi-google text-secondary',
                                                ];
                                                $picon = $picons[strtolower($ad->platform)] ?? 'bi-broadcast text-secondary';
                                                ?>
                                                <div class="text-muted mt-1" style="font-size: 0.78rem;">
                                                    <i
                                                        class="bi <?= $picon ?> me-1"></i><?= htmlspecialchars(ucfirst($ad->platform)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($ad->content_identifier)): ?>
                                                <div class="text-muted font-monospace mt-1" style="font-size: 0.72rem;">
                                                    <?= htmlspecialchars($ad->content_identifier) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-shrink-0 text-end">
                                            <?php if (!empty($ad->is_active)): ?>
                                                <span class="badge bg-success bg-opacity-75"
                                                    style="font-size: 0.68rem;">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-75"
                                                    style="font-size: 0.68rem;">Inactive</span>
                                            <?php endif; ?>
                                            <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                                <?= count($metrics) ?> metric<?= count($metrics) !== 1 ? 's' : '' ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metrics table -->
                                    <?php if (empty($metrics)): ?>
                                        <div class="text-muted text-center py-2 bg-light rounded-2" style="font-size: 0.85rem;">
                                            <i class="bi bi-bar-chart opacity-50 me-1"></i> No metrics available.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover table-bordered align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr style="font-size: 0.78rem;">
                                                        <th scope="col">Metric</th>
                                                        <th scope="col" class="text-end">Value</th>
                                                        <th scope="col">Updated</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($metrics as $metric): ?>
                                                        <tr style="font-size: 0.82rem;">
                                                            <td>
                                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border"
                                                                    style="font-size: 0.72rem;">
                                                                    <?= htmlspecialchars($metric->metric_name) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-end font-monospace fw-semibold">
                                                                <?= number_format($metric->metric_value, (floor($metric->metric_value) != $metric->metric_value) ? 2 : 0, '.', ',') ?>
                                                            </td>
                                                            <td class="text-muted small text-nowrap">
                                                                <?php if (!empty($metric->updated_at)): ?>
                                                                    <?= date('d M Y H:i', strtotime($metric->updated_at)) ?>
                                                                <?php elseif (!empty($metric->created_at)): ?>
                                                                    <?= date('d M Y H:i', strtotime($metric->created_at)) ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Showing <?= count($ads) ?> ad(s),
                                    <?= array_sum(array_map(fn($a) => count($a->metrics ?? []), $ads)) ?> metric(s) total
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.row -->
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>