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
                            <?php foreach ($contracts as $contract): ?>
                                <tr>
                                    <td class="fw-medium">
                                        <?= htmlspecialchars($contract->contract_number) ?>
                                        <?php if ($contract->terminated_at): ?>
                                            <div class="text-danger mt-1" style="font-size: 0.75rem;" title="Terminated Reason: <?= htmlspecialchars($contract->termination_reason) ?>">
                                                <i class="bi bi-slash-circle me-1"></i> Terminated
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
                            <?php endforeach; ?>
                            <?php if (empty($contracts)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No contracts found.</td>
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
</script>

<?php $this->load->view('templates/footer'); ?>
