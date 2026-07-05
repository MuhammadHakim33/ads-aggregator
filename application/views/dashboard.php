<?php $this->load->view('templates/header'); ?>

<?php
$platforms = [
    'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram'],
    'youtube' => ['label' => 'YouTube', 'icon' => 'bi-youtube'],
    'ga4' => ['label' => 'GA4', 'icon' => 'bi-bar-chart-line'],
];

$today = date('Y-m-d');
$role = $current_account['role'];
?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <?php if ($role === 'client'): ?>
                <?php $totalOpenComplaints = $total_open_complaints ?? 0; ?>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <!-- active Contracts -->
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('contract') ?>" class="text-decoration-none">
                            <div class="card border-1 h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 flex-shrink-0">
                                        <i class="bi bi-file-earmark-check fs-4 text-primary"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fs-2 fw-bold lh-1"><?= $total_contracts_active ?></div>
                                        <div class="text-muted small mt-1">Active Contracts</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- running Campaigns -->
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('campaign') ?>" class="text-decoration-none">
                            <div class="card border-1 h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-success bg-opacity-10 p-3 flex-shrink-0">
                                        <i class="bi bi-megaphone fs-4 text-success"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fs-2 fw-bold lh-1"><?= $total_campaigns_running ?></div>
                                        <div class="text-muted small mt-1">Running Campaigns</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- total ads -->
                    <div class="col-6 col-md-3">
                        <div class="card border-1 h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-info bg-opacity-10 p-3 flex-shrink-0">
                                    <i class="bi bi-collection-play fs-4 text-info"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="fs-2 fw-bold lh-1"><?= $total_ads ?></div>
                                    <div class="text-muted small mt-1">Total Ads</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- open complaints -->
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('complaint') ?>" class="text-decoration-none">
                            <div class="card border-1 h-100 <?= $totalOpenComplaints > 0 ? 'border border-warning' : '' ?>">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div
                                        class="rounded-3 p-3 flex-shrink-0 <?= $totalOpenComplaints > 0 ? 'bg-warning bg-opacity-10' : 'bg-secondary bg-opacity-10' ?>">
                                        <i
                                            class="bi bi-exclamation-octagon fs-4 <?= $totalOpenComplaints > 0 ? 'text-warning' : 'text-secondary' ?>"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="fs-2 fw-bold lh-1 <?= $totalOpenComplaints > 0 ? 'text-warning' : '' ?>">
                                            <?= $totalOpenComplaints ?>
                                        </div>
                                        <div class="text-muted small mt-1">Open Complaints</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row g-4">
                    <!-- left: active campaigns -->
                    <div class="col-12 col-lg-7">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-megaphone me-2 text-success"></i>Active Campaigns
                                </h6>
                                <a href="<?= base_url('campaign') ?>" class="btn btn-sm btn-outline-secondary">
                                    View All <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>

                            <?php if (empty($campaigns)): ?>
                                <div class="card-body text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    <div>No campaigns found.</div>
                                    <div class="small mt-1">Your campaigns will appear here once they are created.</div>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($campaigns as $i => $campaign):
                                        $is_running = $campaign->start_date <= $today && $campaign->end_date >= $today;
                                        $is_upcoming = $campaign->start_date > $today;
                                        $is_ended = $campaign->end_date < $today;

                                        if ($is_ended) {
                                            $status_label = 'Ended';
                                            $status_color = 'danger';
                                            $status_icon = 'bi-stop-circle';
                                        } elseif ($is_running) {
                                            $status_label = 'Running';
                                            $status_color = 'success';
                                            $status_icon = 'bi-play-circle';
                                        } elseif ($is_upcoming) {
                                            $status_label = 'Upcoming';
                                            $status_color = 'info';
                                            $status_icon = 'bi-clock';
                                        } else {
                                            $status_label = 'Inactive';
                                            $status_color = 'secondary';
                                            $status_icon = 'bi-pause-circle';
                                        }
                                        ?>

                                        <a href="<?= base_url('campaign/detail/' . $campaign->id) ?>"
                                            class="list-group-item list-group-item-action px-3 py-3 border-0 <?= $i > 0 ? 'border-top' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="min-w-0 me-3">
                                                    <div class="fw-semibold text-truncate">
                                                        <?= htmlspecialchars(ucwords($campaign->name)) ?>
                                                    </div>
                                                    <div class="text-muted small mt-1">
                                                        <?= htmlspecialchars($campaign->contract_number ?? '—') ?>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge bg-<?= $status_color ?> bg-opacity-10 text-<?= $status_color ?> border border-<?= $status_color ?> border-opacity-25 flex-shrink-0">
                                                    <i class="bi <?= $status_icon ?> me-1"></i><?= $status_label ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                                                <span>
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    <?= date('d M Y', strtotime($campaign->start_date)) ?> -
                                                    <?= date('d M Y', strtotime($campaign->end_date)) ?>
                                                </span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">Showing <?= count($campaigns) ?> campaigns</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- right: contracts + quick actions -->
                    <div class="col-12 col-lg-5 d-flex flex-column gap-4">
                        <!-- contracts Card -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Contracts
                                </h6>
                                <a href="<?= base_url('contract') ?>" class="btn btn-sm btn-outline-secondary">
                                    View All <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <?php if (empty($contracts)): ?>
                                <div class="card-body text-center text-muted py-4">
                                    <i class="bi bi-file-earmark fs-2 d-block mb-2 opacity-50"></i>
                                    <div class="small">No contracts found.</div>
                                </div>
                            <?php else: ?>
                                <?php
                                $cStatusMap = [
                                    'pending' => ['color' => 'warning', 'label' => 'Pending'],
                                    'approved' => ['color' => 'success', 'label' => 'Approved'],
                                    'rejected' => ['color' => 'danger', 'label' => 'Rejected'],
                                    'terminated' => ['color' => 'secondary', 'label' => 'Terminated'],
                                ];
                                foreach ($contracts as $i => $contract):
                                    $cStatus = $cStatusMap[$contract->status] ?? ['color' => 'secondary', 'label' => ucfirst($contract->status)];
                                    ?>
                                    <div class="px-3 py-3 <?= $i > 0 ? 'border-top' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-medium text-truncate me-2">
                                                <?= htmlspecialchars($contract->contract_number) ?>
                                            </div>
                                            <span
                                                class="badge bg-<?= $cStatus['color'] ?> bg-opacity-10 text-<?= $cStatus['color'] ?> border border-<?= $cStatus['color'] ?> border-opacity-25 flex-shrink-0 small">
                                                <?= $cStatus['label'] ?>
                                            </span>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= date('d M Y', strtotime($contract->start_date)) ?> -
                                            <?= date('d M Y', strtotime($contract->end_date)) ?>
                                        </div>
                                        <?php if (!empty($contract->value)): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-cash-coin me-1"></i>
                                                Rp <?= number_format($contract->value, 0, ',', '.') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                <div class="card-footer">
                                    <small class="text-muted">Showing <?= count($contracts) ?> contracts</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="row g-3 mb-4">
                    <?php if (in_array($role, ['manajemen'])): ?>
                        <!-- card client active -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-1 h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                        <i class="bi bi-person-check fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fs-2 fw-bold lh-1"><?= $total_clients_active ?></div>
                                        <div class="text-muted small mt-1">Client Active</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- card contract active -->
                        <div class="col-sm-6 col-md-4">
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
                        <!-- card ongoing campaign -->
                        <div class="col-sm-6 col-md-4">
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
                        <!-- card total ads -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-1 h-100">
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
                    <?php endif; ?>

                    <?php if (in_array($role, ['ae', 'manajemen'])): ?>
                        <!-- card unconnected ads -->
                        <div class="col-sm-6 col-md-4">
                            <div class="card border-1 h-100<?= $total_unconnected_ads > 0 ? ' border border-warning' : '' ?>">
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
                    <?php endif; ?>
                </div>

                <?php if (in_array($role, ['ae', 'manajemen']) && $total_unconnected_ads > 0): ?>
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
                            <?php if ($role === 'ae' || $role === 'manajemen'): ?>
                                <a href="<?= base_url('ads') ?>" class="btn btn-warning btn-sm text-nowrap">
                                    <i class="bi bi-link-45deg me-1"></i> Connect Now
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === 'superadmin'): ?>
                    <div class="card mb-4">
                        <div class="card-body border-bottom d-flex align-items-center gap-2">
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
                                                    <span
                                                        class="text-muted small"><?= date('d M Y H:i', strtotime($last->finished_at)) ?></span>
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
                                            <th scope="col" colspan="2">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cron_recent as $log): ?>
                                            <?php
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
                                                        <span><?= ucfirst($log->platform) ?></span>
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
                                                <td class="small text-muted text-nowrap" colspan="2">
                                                    <?= date('d M Y H:i', strtotime($log->started_at)) ?>
                                                </td>
                                            </tr>
                                            <?php if ($log->error_message): ?>
                                                <tr class="table-danger">
                                                    <td colspan="7" class="py-2 px-3 border-top-0">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="small text-danger font-monospace">
                                                                <?= $log->error_message ?>
                                                            </span>
                                                        </div>
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
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>