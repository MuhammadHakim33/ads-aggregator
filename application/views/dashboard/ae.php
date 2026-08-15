<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- ── Stat Cards ── -->
            <div class="row g-3 mb-4">
                <!-- Client Handled -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_clients_handled ?></div>
                                <div class="text-muted small mt-1">Client Handled</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Active -->
                <div class="col-12 col-sm-6 col-md-3">
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

                <!-- Ongoing Campaign -->
                <div class="col-12 col-sm-6 col-md-3">
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

                <!-- Open Complaints -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card border-1 h-100 <?= $total_open_complaints > 0 ? 'border-danger' : '' ?>">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div
                                class="rounded-3 p-3 <?= $total_open_complaints > 0 ? 'bg-danger bg-opacity-10' : 'bg-secondary bg-opacity-10' ?>">
                                <i
                                    class="bi bi-chat-left-dots fs-4 <?= $total_open_complaints > 0 ? 'text-danger' : 'text-secondary' ?>"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_open_complaints ?></div>
                                <div class="text-muted small mt-1">Open Complaints</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Alert unconnected ads ── -->
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
                        <a href="<?= base_url('ads') ?>" class="btn btn-warning btn-sm text-nowrap">
                            <i class="bi bi-link-45deg me-1"></i>Connect Now
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── Client List ── -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-people text-secondary"></i>
                    <span class="fw-medium">Client List</span>
                </div>
                <?php if (empty($clients)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No clients assigned yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Company</th>
                                    <th scope="col" class="text-center" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($client->company_name) ?></td>
                                        <td class="text-center">
                                            <?php if ($client->is_active): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success">
                                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ── Expiring Contracts ── -->
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-x text-secondary"></i>
                    <span class="fw-medium">Contracts Expiring Soon</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-auto">Next 30 days</span>
                </div>
                <?php if (empty($expiring_contracts)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                        No contracts expiring in the next 30 days.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Client</th>
                                    <th scope="col">Contract No.</th>
                                    <th scope="col" class="text-center" style="width: 140px;">End Date</th>
                                    <th scope="col" class="text-center" style="width: 110px;">Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiring_contracts as $contract): ?>
                                    <?php
                                    $days_left = (int) ceil((strtotime($contract->end_date) - time()) / 86400);
                                    $urgency_class = $days_left <= 7 ? 'danger' : ($days_left <= 14 ? 'warning' : 'secondary');
                                    ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($contract->client_name) ?></td>
                                        <td>
                                            <span class="badge text-bg-light border font-monospace">
                                                <?= htmlspecialchars($contract->contract_number) ?>
                                            </span>
                                            <?php if (!empty($contract->campaigns)): ?>
                                                <ul class="list-unstyled mb-0 mt-2 text-muted" style="font-size: 0.8rem;">
                                                    <?php foreach ($contract->campaigns as $camp): ?>
                                                        <li class="mb-1 text-truncate" style="max-width: 250px;"
                                                            title="<?= htmlspecialchars($camp->name) ?>">
                                                            <i
                                                                class="bi bi-arrow-return-right text-secondary me-1"></i><?= htmlspecialchars($camp->name) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                    <i class="bi bi-info-circle me-1"></i>No campaigns
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center small text-muted text-nowrap">
                                            <?= date('d M Y', strtotime($contract->end_date)) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-<?= $urgency_class ?>">
                                                <?= $days_left ?> day<?= $days_left !== 1 ? 's' : '' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">Showing <?= count($expiring_contracts) ?>
                            contract<?= count($expiring_contracts) !== 1 ? 's' : '' ?> expiring soon</small>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>