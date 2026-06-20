<?php $this->load->view('templates/header'); ?>

<?php
$platforms = [
    'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram'],
    'youtube' => ['label' => 'YouTube', 'icon' => 'bi-youtube'],
    'ga4' => ['label' => 'GA4', 'icon' => 'bi-bar-chart-line'],
    // 'gam' => ['label' => 'GAM', 'icon' => 'bi-google'],
];
?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <!-- page heading -->
            <div class="mb-4">
                <h5 class="mb-0">Welcome back, <?= $current_account['name'] ?></h5>
            </div>
            <div class="row g-3 mb-4">
                <!-- card client active -->
                <div class="col-sm-6 col-md-4">
                    <div class="card border-1 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-person-check fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_clients_active ?></div>
                                <div class="text-muted small mt-1">client Active</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    from <?= $total_clients ?> total
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- card contract active -->
                <div class="col-sm-6 col-md-4">
                    <div class="card border-1 shadow-sm h-100">
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
                <!-- card ongoing campaign -->
                <div class="col-sm-6 col-md-4">
                    <div class="card border-1 shadow-sm h-100">
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
                <!-- card total ads -->
                <div class="col-sm-6 col-md-4">
                    <div class="card border-1 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-info bg-opacity-10 p-3">
                                <i class="bi bi-collection-play fs-4 text-info"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_ads ?></div>
                                <div class="text-muted small mt-1">Total Ads</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- card unconnected ads -->
                <div class="col-sm-6 col-md-4">
                    <div
                        class="card border-1 shadow-sm h-100<?= $total_unconnected_ads > 0 ? ' border border-warning' : '' ?>">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div
                                class="rounded-3 p-3 <?= $total_unconnected_ads > 0 ? 'bg-warning bg-opacity-10' : 'bg-secondary bg-opacity-10' ?>">
                                <i
                                    class="bi bi-link-45deg fs-4 <?= $total_unconnected_ads > 0 ? 'text-warning' : 'text-secondary' ?>"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1<?= $total_unconnected_ads > 0 ? ' text-warning' : '' ?>">
                                    <?= $total_unconnected_ads ?>
                                </div>
                                <div class="text-muted small mt-1">Unconnected Ads</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                        <a href="<?= base_url('ads/connect') ?>" class="btn btn-warning btn-sm text-nowrap">
                            <i class="bi bi-link-45deg me-1"></i> Connect Now
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            <div class="card mb-4">
                <div class="card-body border-bottom pb-2 mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat text-primary"></i>
                    <span class="fw-medium">Status Sync Platform</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 25%">Platform</th>
                                <th scope="col" class="text-center" style="width: 15%">Fetch</th>
                                <th scope="col" class="text-center" style="width: 15%">Sync</th>
                                <th scope="col" style="width: 30%">Last Running</th>
                                <th scope="col" class="text-end" style="width: 15%">Rows</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($platforms as $key => $conf): ?>
                                <?php
                                $fetch = $cron_last_per_platform[$key . '|fetch'] ?? null;
                                $sync = $cron_last_per_platform[$key . '|sync'] ?? null;
                                $last = $fetch ?? $sync;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi <?= $conf['icon'] ?>"></i>
                                            <span class="fw-medium"><?= $conf['label'] ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($fetch): ?>
                                            <?php $fc = $fetch->status === 'success' ? 'success' : ($fetch->status === 'partial' ? 'warning' : 'danger'); ?>
                                            <span class="badge text-bg-<?= $fc ?>"><?= $fetch->status ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sync): ?>
                                            <?php $sc = $sync->status === 'success' ? 'success' : ($sync->status === 'partial' ? 'warning' : 'danger'); ?>
                                            <span class="badge text-bg-<?= $sc ?>"><?= $sync->status ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($last): ?>
                                            <span class="text-muted small">
                                                <?= date('d M Y H:i', strtotime($last->finished_at)) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic small">Never synced</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($last): ?>
                                            <span class="font-monospace small">
                                                <?= $last->rows_affected ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-body border-bottom pb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <span class="fw-medium">Cron History (Last 10)</span>
                </div>
                <?php if (empty($cron_recent)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        There is no cron history yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Job</th>
                                    <th scope="col">Platform</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-end">Rows</th>
                                    <th scope="col" class="text-end">Duration</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cron_recent as $log): ?>
                                    <?php
                                    $pconf = $platforms[strtolower($log->platform)] ?? null;
                                    $badgeMap = ['success' => 'success', 'failed' => 'danger', 'partial' => 'warning'];
                                    $badge = $badgeMap[$log->status] ?? 'secondary';
                                    $duration = $log->duration_ms >= 1000
                                        ? number_format($log->duration_ms / 1000, 1) . 's'
                                        : $log->duration_ms . 'ms';
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge text-bg-light border">
                                                <?= htmlspecialchars($log->job_name) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($pconf): ?>
                                                    <i class="bi <?= $pconf['icon'] ?> text-<?= $pconf['color'] ?>"></i>
                                                <?php endif; ?>
                                                <span><?= $log->platform ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-<?= $badge ?>"><?= $log->status ?></span>
                                        </td>
                                        <td class="text-end font-monospace small">
                                            <?= $log->rows_affected ?>
                                        </td>
                                        <td class="text-end font-monospace small text-muted">
                                            <?= $duration ?>
                                        </td>
                                        <td class="small text-muted text-nowrap">
                                            <?= date('d M Y H:i', strtotime($log->started_at)) ?>
                                        </td>
                                        <td>
                                            <?php if ($log->error_message): ?>
                                                <span class="small text-danger" title="<?= $log->error_message ?>">
                                                    <i
                                                        class="bi bi-exclamation-circle me-1"></i><?= mb_strimwidth($log->error_message, 0, 40, '…') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing <?= count($cron_recent) ?> log(s)</small>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>