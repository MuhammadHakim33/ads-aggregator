<?php $this->load->view('templates/header'); ?>

<?php
$statusMap = [
    'approved' => ['color' => 'success', 'label' => 'Approved', 'icon' => 'bi-check-circle'],
    'pending' => ['color' => 'warning', 'label' => 'Pending', 'icon' => 'bi-hourglass-split'],
    'rejected' => ['color' => 'danger', 'label' => 'Rejected', 'icon' => 'bi-x-circle'],
];
?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- alert unconnected ads -->
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- filter bar -->
            <form method="GET" action="<?= current_url() ?>" class="d-flex flex-wrap gap-2 align-items-end pb-2 mb-4">
                <div>
                    <label class="form-label small mb-1">From Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm"
                        value="<?= html_escape($filters['start_date']) ?>">
                </div>
                <div>
                    <label class="form-label small mb-1">To Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm"
                        value="<?= html_escape($filters['end_date']) ?>">
                </div>
                <div>
                    <label class="form-label small mb-1">Client</label>
                    <select name="client_id" class="form-select form-select-sm">
                        <option value="">All Clients</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c->id ?>" <?= $filters['client_id'] == $c->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c->company_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Search</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                        placeholder="Contract No. / client..." value="<?= html_escape($filters['q'] ?? '') ?>">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary"
                        title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>

            <!-- stat cards -->
            <div class="row g-3 mb-4">
                <!-- total contracts -->
                <div class="col-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_contracts ?></div>
                                <div class="text-muted small mt-1">Total Contracts</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- total value -->
                <div class="col-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                <i class="bi bi-cash-coin fs-4 text-success"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold lh-1">Rp <?= number_format($total_value, 0, ',', '.') ?></div>
                                <div class="text-muted small mt-1">Total Contract Value</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- approved -->
                <div class="col-6 col-md-3">
                    <div class="card border-1 h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                <i class="bi bi-check-circle fs-4 text-success"></i>
                            </div>
                            <div>
                                <div class="fs-2 fw-bold lh-1"><?= $total_approved ?></div>
                                <div class="text-muted small mt-1">Approved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- contract list table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-medium">
                        <i class="bi bi-file-earmark-text me-2"></i>Contract List
                    </span>
                    <small class="text-muted">
                        <?= $filters['start_date'] ? date('d M Y', strtotime($filters['start_date'])) : '—' ?>
                        -
                        <?= $filters['end_date'] ? date('d M Y', strtotime($filters['end_date'])) : '—' ?>
                    </small>
                </div>

                <?php if (empty($contract_list)): ?>
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        <div>No contracts found.</div>
                        <div class="small mt-1">Try changing the date range or other filters.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Contract Number</th>
                                    <th scope="col">Client</th>
                                    <th scope="col">Contract Value</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">End Date</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contract_list as $contract): ?>
                                    <?php
                                    $isTerminated = !empty($contract->terminated_at);
                                    if ($isTerminated) {
                                        $badge = ['color' => 'secondary', 'label' => 'Terminated', 'icon' => 'bi-slash-circle'];
                                    } else {
                                        $badge = $statusMap[$contract->status] ?? ['color' => 'secondary', 'label' => ucfirst($contract->status), 'icon' => 'bi-circle'];
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-medium">
                                            <?= htmlspecialchars($contract->contract_number) ?>
                                        </td>
                                        <td><?= htmlspecialchars(ucwords($contract->client_name)) ?></td>
                                        <td class="font-monospace">
                                            <?php if (!empty($contract->value)): ?>
                                                Rp <?= number_format($contract->value, 0, ',', '.') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($contract->start_date)) ?></td>
                                        <td><?= date('d M Y', strtotime($contract->end_date)) ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?= $badge['color'] ?> bg-opacity-10 text-<?= $badge['color'] ?> fw-normal">
                                                <i class="bi <?= $badge['icon'] ?> me-1"></i><?= $badge['label'] ?>
                                            </span>
                                            <?php if ($contract->status === 'rejected' && !empty($contract->rejection_reason)): ?>
                                                <div class="small text-danger mt-1">
                                                    <strong>Reason:</strong> <?= htmlspecialchars($contract->rejection_reason) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">
                            Showing <?= $total_contracts ?> contract<?= $total_contracts !== 1 ? 's' : '' ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>