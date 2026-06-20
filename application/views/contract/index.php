<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- action bar -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-2 mb-3">
                <form method="GET" action="<?= current_url() ?>" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                    <select name="client_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Clients</option>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c->id ?>" <?= (isset($filters['client_id']) && $filters['client_id'] == $c->id) ? 'selected' : '' ?>><?= htmlspecialchars($c->company_name) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search contracts..." value="<?= html_escape($filters['q'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    
                    <?php if(!empty($filters['q']) || !empty($filters['client_id'])): ?>
                        <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </form>

                <a href="<?= base_url('contract/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Contract
                </a>
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

            <!-- data table card -->
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 36px;"></th>
                                <th scope="col">Contract Number</th>
                                <th scope="col">Client</th>
                                <th scope="col">Value</th>
                                <th scope="col">Start Date</th>
                                <th scope="col">End Date</th>
                                <th scope="col">Document</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract):
                                $campaigns = $campaigns_by_contract[$contract->id] ?? [];
                                $campaign_count = count($campaigns);
                            ?>
                                <!-- Contract row -->
                                <tr class="contract-row <?= $contract->terminated_at ? 'table-danger bg-opacity-10' : '' ?>">
                                    <td class="text-center">
                                        <?php if ($campaign_count > 0): ?>
                                            <button class="btn btn-sm btn-link p-0 text-secondary toggle-campaigns"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#campaigns-<?= $contract->id ?>"
                                                    aria-expanded="false"
                                                    title="Show Campaigns">
                                                <i class="bi bi-chevron-right toggle-icon" style="transition: transform 0.2s;"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.75rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-medium">
                                        <?= htmlspecialchars($contract->contract_number) ?>
                                        <?php if ($contract->terminated_at): ?>
                                            <div class="text-danger mt-1" style="font-size: 0.75rem;" title="Terminated Reason: <?= htmlspecialchars($contract->termination_reason) ?>">
                                                <i class="bi bi-slash-circle me-1"></i> Terminated
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($campaign_count > 0): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.7rem;">
                                                    <i class="bi bi-megaphone me-1"></i><?= $campaign_count ?> campaign<?= $campaign_count > 1 ? 's' : '' ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(ucwords($contract->client_name)) ?></td>
                                    <td>
                                        Rp <?= number_format($contract->value, 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?= date('Y-m-d', strtotime($contract->start_date)) ?>
                                    </td>
                                    <td>
                                        <?= date('Y-m-d', strtotime($contract->end_date)) ?>
                                    </td>
                                    <td>
                                        <?php if ($contract->document_path): ?>
                                            <a href="<?= base_url('contract/download/' . $contract->id) ?>" 
                                               class="btn btn-sm btn-outline-primary py-0 px-2 text-decoration-none" 
                                               style="font-size: 0.75rem;"
                                               title="Download Contract Document">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i> Download File
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.75rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('contract/edit/' . $contract->id) ?>"
                                            class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="<?= $contract->id ?>" 
                                            data-number="<?= htmlspecialchars($contract->contract_number) ?>"
                                            data-client="<?= htmlspecialchars($contract->client_name) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Campaign sub-rows (collapsible) -->
                                <?php if ($campaign_count > 0): ?>
                                    <tr class="collapse campaigns-collapse" id="campaigns-<?= $contract->id ?>">
                                        <td colspan="8" class="p-0 border-top-0">
                                            <div class="bg-light border-start border-4 border-primary ms-3 my-0">
                                                <div class="px-3 pt-2 pb-1">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-muted fw-semibold" style="font-size: 0.78rem; letter-spacing: 0.04em; text-transform: uppercase;">
                                                            <i class="bi bi-megaphone me-1"></i> Campaigns
                                                        </span>
                                                        <a href="<?= base_url('campaign/create?contract_id=' . $contract->id) ?>"
                                                           class="btn btn-sm btn-outline-primary py-0 px-2"
                                                           style="font-size: 0.73rem;" title="Add Campaign to this Contract">
                                                            <i class="bi bi-plus me-1"></i>Add
                                                        </a>
                                                    </div>
                                                    <table class="table table-sm table-borderless mb-0 align-middle">
                                                        <thead>
                                                            <tr style="font-size: 0.78rem;" class="text-muted">
                                                                <th class="fw-semibold ps-0" style="width: 35%;">Name</th>
                                                                <th class="fw-semibold" style="width: 12%;">Status</th>
                                                                <th class="fw-semibold" style="width: 16%;">Start</th>
                                                                <th class="fw-semibold" style="width: 16%;">End</th>
                                                                <th class="fw-semibold text-end pe-0"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($campaigns as $campaign):
                                                                $is_active = $campaign->is_active;
                                                                $today = date('Y-m-d');
                                                                $is_running = $is_active && $campaign->end_date >= $today && $campaign->start_date <= $today;
                                                                $is_upcoming = $is_active && $campaign->start_date > $today;
                                                                $is_ended = $campaign->end_date < $today;
                                                            ?>
                                                                <tr style="font-size: 0.82rem;">
                                                                    <td class="ps-0 fw-medium">
                                                                        <?= htmlspecialchars(ucwords($campaign->name)) ?>
                                                                        <?php if ($campaign->description): ?>
                                                                            <div class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                                                <?= htmlspecialchars($campaign->description) ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!$is_active): ?>
                                                                            <span class="badge bg-secondary bg-opacity-75" style="font-size: 0.68rem;">Inactive</span>
                                                                        <?php elseif ($is_ended): ?>
                                                                            <span class="badge bg-danger bg-opacity-75" style="font-size: 0.68rem;">Ended</span>
                                                                        <?php elseif ($is_running): ?>
                                                                            <span class="badge bg-success bg-opacity-75" style="font-size: 0.68rem;">Running</span>
                                                                        <?php elseif ($is_upcoming): ?>
                                                                            <span class="badge bg-info bg-opacity-75 text-dark" style="font-size: 0.68rem;">Upcoming</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-secondary bg-opacity-75" style="font-size: 0.68rem;">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td><?= date('Y-m-d', strtotime($campaign->start_date)) ?></td>
                                                                    <td><?= date('Y-m-d', strtotime($campaign->end_date)) ?></td>
                                                                    <td class="text-end pe-0">
                                                                        <a href="<?= base_url('campaign/edit/' . $campaign->id) ?>"
                                                                           class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                                           style="font-size: 0.73rem;" title="Edit Campaign">
                                                                            <i class="bi bi-pencil"></i>
                                                                        </a>
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
                            <?php if (empty($contracts)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No contracts found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($contracts) ?> contract(s)</small>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete contract <strong id="deleteContractNumber" class="text-danger"></strong> for <strong id="deleteClientName"></strong>?
                This action will perform a soft delete.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('deleteContractNumber').textContent = btn.getAttribute('data-number');
        document.getElementById('deleteClientName').textContent = btn.getAttribute('data-client');
        document.getElementById('deleteForm').action = '<?= base_url('contract/delete/') ?>' + btn.getAttribute('data-id');
    });

    // Rotate chevron icon when collapsing/expanding
    document.querySelectorAll('.toggle-campaigns').forEach(function (btn) {
        const target = document.querySelector(btn.getAttribute('data-bs-target'));
        if (target) {
            target.addEventListener('show.bs.collapse', function () {
                btn.querySelector('.toggle-icon').style.transform = 'rotate(90deg)';
            });
            target.addEventListener('hide.bs.collapse', function () {
                btn.querySelector('.toggle-icon').style.transform = 'rotate(0deg)';
            });
        }
    });
</script>

<?php $this->load->view('templates/footer'); ?>
