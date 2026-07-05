<?php $this->load->view('templates/header'); ?>

<?php
$today = date('Y-m-d');
$totalOpenComplaints = $total_open_complaints ?? 0;

$cStatusMap = [
    'pending' => ['color' => 'warning', 'label' => 'Pending'],
    'approved' => ['color' => 'success', 'label' => 'Approved'],
    'rejected' => ['color' => 'danger', 'label' => 'Rejected'],
    'terminated' => ['color' => 'secondary', 'label' => 'Terminated'],
];
?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- Flash Alerts -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- ── Stat Cards ── -->
            <div class="row g-3 mb-4">
                <!-- Active Contracts -->
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
                <!-- Running Campaigns -->
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
                <!-- Total Ads -->
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
                <!-- Open Complaints -->
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
                <!-- LEFT: Active Campaigns -->
                <div class="col-12 col-lg-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-megaphone me-2"></i>Active Campaigns
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
                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= date('d M Y', strtotime($campaign->start_date)) ?> -
                                            <?= date('d M Y', strtotime($campaign->end_date)) ?>
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

                <!-- RIGHT: Contracts + Quick Actions -->
                <div class="col-12 col-lg-5 d-flex flex-column gap-4">
                    <!-- Contracts -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>Contracts
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
                            <?php foreach ($contracts as $i => $contract):
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
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>