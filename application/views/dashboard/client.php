<?php $this->load->view('templates/header'); ?>

<?php
$today = date('Y-m-d');
$totalOpenComplaints = $total_open_complaints ?? 0;
$contracts_with_campaigns = $contracts_with_campaigns ?? [];

$cStatusMap = [
    'pending' => ['color' => 'warning', 'label' => 'Pending', 'icon' => 'bi-hourglass-split'],
    'approved' => ['color' => 'success', 'label' => 'Approved', 'icon' => 'bi-check-circle'],
    'rejected' => ['color' => 'danger', 'label' => 'Rejected', 'icon' => 'bi-x-circle'],
    'terminated' => ['color' => 'secondary', 'label' => 'Terminated', 'icon' => 'bi-slash-circle'],
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
                                <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                    <i class="bi bi-file-earmark-check fs-4 text-primary"></i>
                                </div>
                                <div>
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
                                <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                    <i class="bi bi-megaphone fs-4 text-success"></i>
                                </div>
                                <div>
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
                <!-- Open Complaints -->
                <div class="col-6 col-md-3">
                    <a href="<?= base_url('complaint') ?>" class="text-decoration-none">
                        <div class="card border-1 h-100 <?= $totalOpenComplaints > 0 ? 'border-warning' : '' ?>">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div
                                    class="rounded-3 p-3 <?= $totalOpenComplaints > 0 ? 'bg-warning bg-opacity-10' : 'bg-secondary bg-opacity-10' ?>">
                                    <i
                                        class="bi bi-exclamation-octagon fs-4 <?= $totalOpenComplaints > 0 ? 'text-warning' : 'text-secondary' ?>"></i>
                                </div>
                                <div>
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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-file-earmark-text me-2"></i>Contracts &amp; Campaigns
                </h6>
                <a href="<?= base_url('contract') ?>" class="btn btn-sm btn-outline-secondary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if (empty($contracts_with_campaigns)): ?>
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-file-earmark fs-1 d-block mb-2 opacity-50"></i>
                        <div>No Contracts</div>
                        <div class="small mt-1">Your contracts will appear here after being approved.</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($contracts_with_campaigns as $idx => $contract):
                        $cStatus = $cStatusMap[$contract->status] ?? ['color' => 'secondary', 'label' => ucfirst($contract->status), 'icon' => 'bi-circle'];
                        $campaign_count = count($contract->campaigns);
                        $running_count = 0;
                        foreach ($contract->campaigns as $cp) {
                            if ($cp->start_date <= $today && $cp->end_date >= $today)
                                $running_count++;
                        }
                        $collapse_id = 'contract-collapse-' . $contract->id;
                        $is_first = $idx === 0;
                        ?>
                        <div class="card">

                            <!-- Contract Header (accordion toggle) -->
                            <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between"
                                role="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapse_id ?>"
                                aria-expanded="<?= $is_first ? 'true' : 'false' ?>" aria-controls="<?= $collapse_id ?>">

                                <div class="d-flex align-items-center gap-3 flex-grow-1 overflow-hidden">
                                    <div>
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                            <span class="fw-medium">
                                                <?= htmlspecialchars($contract->contract_number) ?>
                                            </span>
                                            <span
                                                class="badge bg-<?= $cStatus['color'] ?> bg-opacity-10 text-<?= $cStatus['color'] ?> fw-normal">
                                                <i class="bi <?= $cStatus['icon'] ?> me-1"></i><?= $cStatus['label'] ?>
                                            </span>
                                            <?php if (!empty($contract->terminated_at)): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger fw-normal">
                                                    <i class="bi bi-slash-circle me-1"></i>Terminated
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= date('d M Y', strtotime($contract->start_date)) ?> &mdash;
                                            <?= date('d M Y', strtotime($contract->end_date)) ?>
                                            <?php if (!empty($contract->value)): ?>
                                                &nbsp;&bull;&nbsp;
                                                <i class="bi bi-cash-coin me-1"></i>Rp
                                                <?= number_format($contract->value, 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="badge bg-light text-dark border fw-normal">
                                        <i class="bi bi-megaphone me-1"></i><?= $campaign_count ?>
                                        Campaign<?= $campaign_count !== 1 ? 's' : '' ?>
                                    </span>
                                    <?php if ($running_count > 0): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success fw-normal">
                                            <i class="bi bi-play-circle me-1"></i><?= $running_count ?> Running
                                        </span>
                                    <?php endif; ?>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </div>
                            </div>

                            <!-- Collapse body: Campaign list -->
                            <div class="collapse <?= $is_first ? 'show' : '' ?>" id="<?= $collapse_id ?>">
                                <?php if (empty($contract->campaigns)): ?>
                                    <div class="card-body text-center text-muted py-4">
                                        <i class="bi bi-megaphone fs-2 d-block mb-2 opacity-50"></i>
                                        <div class="small">No campaigns for this contract.</div>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($contract->campaigns as $ci => $campaign):
                                            $is_running = $campaign->start_date <= $today && $campaign->end_date >= $today;
                                            $is_upcoming = $campaign->start_date > $today;
                                            $is_ended = $campaign->end_date < $today;

                                            if ($is_ended) {
                                                $camp_label = 'Ended';
                                                $camp_color = 'danger';
                                                $camp_icon = 'bi-stop-circle';
                                            } elseif ($is_running) {
                                                $camp_label = 'Running';
                                                $camp_color = 'success';
                                                $camp_icon = 'bi-play-circle-fill';
                                            } elseif ($is_upcoming) {
                                                $camp_label = 'Upcoming';
                                                $camp_color = 'info';
                                                $camp_icon = 'bi-clock';
                                            } else {
                                                $camp_label = 'Inactive';
                                                $camp_color = 'secondary';
                                                $camp_icon = 'bi-pause-circle';
                                            }
                                            ?>
                                            <a href="<?= base_url('campaign/detail/' . $campaign->id) ?>"
                                                class="list-group-item list-group-item-action px-3 py-3 border-0 <?= $ci > 0 ? 'border-top' : '' ?>">
                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                    <div class="overflow-hidden">
                                                        <div class="fw-medium text-truncate mb-1">
                                                            <?= htmlspecialchars(ucwords($campaign->name)) ?>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            <?= date('d M Y', strtotime($campaign->start_date)) ?>
                                                            &rarr;
                                                            <?= date('d M Y', strtotime($campaign->end_date)) ?>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="badge bg-<?= $camp_color ?> bg-opacity-10 text-<?= $camp_color ?> fw-normal flex-shrink-0">
                                                        <i class="bi <?= $camp_icon ?> me-1"></i><?= $camp_label ?>
                                                    </span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">
                                            Showing <?= $campaign_count ?> campaigns
                                            &bull; <?= $running_count ?> running
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>