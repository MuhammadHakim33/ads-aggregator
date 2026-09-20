<?php $this->load->view('templates/header'); ?>

<?php
$platform_conf = [
    'meta'    => ['label' => 'Meta',    'icon' => 'bi-meta',          'url' => 'config/platforms/meta'],
    'ga4'     => ['label' => 'GA4',     'icon' => 'bi-bar-chart-line', 'url' => 'config/platforms/ga4'],
    'youtube' => ['label' => 'YouTube', 'icon' => 'bi-youtube',        'url' => 'config/platforms/youtube'],
    'gam'     => ['label' => 'GAM',     'icon' => 'bi-google',         'url' => 'config/platforms/gam'],
];

$sync_platforms = [
    'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram'],
    'youtube'  => ['label' => 'YouTube',  'icon' => 'bi-youtube'],
    'ga4'      => ['label' => 'GA4',      'icon' => 'bi-bar-chart-line'],
];

$role_labels = [
    'superadmin' => ['label' => 'Superadmin', 'color' => 'danger'],
    'manajemen'  => ['label' => 'Manajemen',  'color' => 'primary'],
    'ae'         => ['label' => 'AE',          'color' => 'info'],
    'client'     => ['label' => 'Client',      'color' => 'secondary'],
];
?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- ── Platform Connection Status ── -->
            <div class="row g-3 mb-4">
                <?php foreach ($platform_conf as $key => $conf): ?>
                    <?php 
                        $connected = $platform_credentials_status[$key] ?? false; 
                        $needs_oauth = ($key === 'youtube' && $connected && isset($youtube_needs_oauth) && $youtube_needs_oauth);
                        
                        $bg_class = 'bg-secondary bg-opacity-10';
                        $text_class = 'text-secondary';
                        if ($needs_oauth) {
                            $bg_class = 'bg-warning bg-opacity-10';
                            $text_class = 'text-warning';
                        } elseif ($connected) {
                            $bg_class = 'bg-success bg-opacity-10';
                            $text_class = 'text-success';
                        }
                    ?>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card border-1 h-100 <?= $needs_oauth ? 'border-warning' : '' ?>">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="rounded-3 p-3 <?= $bg_class ?>">
                                    <i class="bi <?= $conf['icon'] ?> fs-4 <?= $text_class ?>"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-semibold lh-1"><?= $conf['label'] ?></div>
                                    <div class="mt-1">
                                        <?php if ($needs_oauth): ?>
                                            <span class="badge <?= $bg_class ?> <?= $text_class ?>">
                                                <i class="bi bi-exclamation-triangle-fill me-1" style="font-size: 0.45rem;"></i>Needs OAuth
                                            </span>
                                        <?php elseif ($connected): ?>
                                            <span class="badge <?= $bg_class ?> <?= $text_class ?>">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Connected
                                            </span>
                                        <?php else: ?>
                                            <span class="badge <?= $bg_class ?> <?= $text_class ?>">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Not Set
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Stat Cards ── -->
            <div class="row g-3 mb-4">
                <!-- Active Users -->
                <div class="col-12 col-sm-6">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_users_active ?></div>
                                <div class="text-muted small mt-1">Active Users</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unconnected Ads -->
                <div class="col-12 col-sm-6">
                    <div class="card border-1 h-100 <?= $total_unconnected_ads > 0 ? 'border-warning' : '' ?>">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 <?= $total_unconnected_ads > 0 ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
                                <i class="bi bi-link-45deg fs-4 <?= $total_unconnected_ads > 0 ? 'text-warning' : 'text-success' ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fs-2 fw-bold lh-1"><?= $total_unconnected_ads ?></div>
                                <div class="text-muted small mt-1">Unconnected Ads</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Two Column: User by Role | Platform Sync Status ── -->
            <div class="row g-3 mb-4">
                <!-- User by Role -->
                <div class="col-12 col-md-5">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-people text-secondary"></i>
                            <span class="fw-medium">User by Role</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Role</th>
                                        <th scope="col" class="text-center">Active</th>
                                        <th scope="col" class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($user_by_role)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox d-block mb-1"></i> No data
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($role_labels as $role_key => $role_conf): ?>
                                            <?php if (!isset($user_by_role[$role_key])) continue; ?>
                                            <?php $row = $user_by_role[$role_key]; ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?= $role_conf['color'] ?> bg-opacity-10 text-<?= $role_conf['color'] ?>">
                                                        <?= $role_conf['label'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center fw-medium"><?= (int) $row->active ?></td>
                                                <td class="text-center text-muted"><?= (int) $row->total ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                <a href="<?= base_url('account') ?>" class="text-decoration-none">
                                    <i class="bi bi-arrow-right me-1"></i>Manage Accounts
                                </a>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Platform Sync Status -->
                <div class="col-12 col-md-7">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-repeat text-secondary"></i>
                            <span class="fw-medium">Platform Sync Status</span>
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
                                    <?php foreach ($sync_platforms as $key => $conf): ?>
                                        <?php
                                        $fetch = $cron_last_per_platform[$key . '|fetch'] ?? null;
                                        $sync  = $cron_last_per_platform[$key . '|sync'] ?? null;
                                        $last  = $fetch ?? $sync;
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
                                                    <span class="text-muted small"><?= date('d M Y H:i', strtotime($last->finished_at)) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic small">Never synced</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($last): ?>
                                                    <span class="font-monospace small"><?= $last->rows_affected ?></span>
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
                </div>
            </div>

            <!-- ── Cron History ── -->
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
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
                                    <th scope="col" colspan="2">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cron_recent as $log): ?>
                                    <?php
                                    $badgeMap = ['success' => 'success', 'failed' => 'danger', 'partial' => 'warning'];
                                    $badge    = $badgeMap[$log->status] ?? 'secondary';
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
                                        <td><?= ucfirst($log->platform) ?></td>
                                        <td class="text-center">
                                            <span class="badge text-bg-<?= $badge ?>"><?= $log->status ?></span>
                                        </td>
                                        <td class="text-end font-monospace small"><?= $log->rows_affected ?></td>
                                        <td class="text-end font-monospace small text-muted"><?= $duration ?></td>
                                        <td class="small text-muted text-nowrap" colspan="2">
                                            <?= date('d M Y H:i', strtotime($log->started_at)) ?>
                                        </td>
                                    </tr>
                                    <?php if ($log->error_message): ?>
                                        <tr class="table-danger">
                                            <td colspan="7" class="py-2 px-3 border-top-0">
                                                <span class="small text-danger font-monospace">
                                                    <?= $log->error_message ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing <?= count($cron_recent) ?> logs</small>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>