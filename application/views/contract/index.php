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
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c->id ?>" <?= (isset($filters['client_id']) && $filters['client_id'] == $c->id) ? 'selected' : '' ?>><?= htmlspecialchars($c->company_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0"
                            placeholder="Search contracts..." value="<?= html_escape($filters['q'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <?php if (!empty($filters['q']) || !empty($filters['client_id'])): ?>
                        <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i
                                class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </form>
                <?php if (in_array($this->session->userdata('role'), ['manajemen', 'client'])): ?>
                    <a href="<?= base_url('contract/create') ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create Contract
                    </a>
                <?php endif; ?>
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
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract):
                                $campaigns = $campaigns_by_contract[$contract->id] ?? [];
                                $campaign_count = count($campaigns);
                                ?>
                                <tr class="contract-row">
                                    <td class="fw-medium">
                                        <?php if ($contract->terminated_at): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                                    Terminated
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?= $contract->contract_number ?>
                                        <?php if ($campaign_count > 0): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-normal">
                                                    <?= $campaign_count ?> Campaign
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= ucwords($contract->client_name) ?></td>
                                    <td class="font-monospace">
                                        Rp <?= number_format($contract->value, 0, ',', '.') ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($contract->start_date)) ?></td>
                                    <td><?= date('d M Y', strtotime($contract->end_date)) ?></td>
                                    <td>
                                        <?php if ($contract->status === 'approved'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-check-circle me-1"></i> Approved
                                            </span>
                                        <?php elseif ($contract->status === 'rejected'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger"
                                                title="Click to view reason">
                                                <i class="bi bi-x-circle me-1"></i> Rejected
                                            </span>
                                            <?php if ($contract->rejection_reason): ?>
                                                <div class="small text-danger mt-1" style="font-size: 0.75rem;">
                                                    <strong>Reason:</strong> <?= htmlspecialchars($contract->rejection_reason) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis">
                                                <i class="bi bi-clock-history me-1"></i> Pending Approval
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="View Details"
                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                            data-id="<?= $contract->id ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if ($contract->document_path): ?>
                                            <a href="<?= base_url('contract/download/' . $contract->id) ?>"
                                                class="btn btn-sm btn-outline-primary ms-1" title="Download Contract Document">
                                                <i class="bi bi-file-earmark-arrow-down"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Management Approvals -->
                                        <?php if ($this->session->userdata('role') === 'manajemen' && $contract->status === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success ms-1" title="Approve"
                                                data-bs-toggle="modal" data-bs-target="#approveModal"
                                                data-id="<?= $contract->id ?>"
                                                data-number="<?= htmlspecialchars($contract->contract_number) ?>">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Reject"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                data-id="<?= $contract->id ?>"
                                                data-number="<?= htmlspecialchars($contract->contract_number) ?>">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Edit action -->
                                        <?php if ($this->session->userdata('role') === 'manajemen' || ($this->session->userdata('role') === 'client' && in_array($contract->status, ['pending', 'rejected']))): ?>
                                            <a href="<?= base_url('contract/edit/' . $contract->id) ?>"
                                                class="btn btn-sm btn-outline-secondary ms-1" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Delete action -->
                                        <?php if ($this->session->userdata('role') === 'manajemen'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="<?= $contract->id ?>"
                                                data-number="<?= htmlspecialchars($contract->contract_number) ?>"
                                                data-client="<?= htmlspecialchars($contract->client_name) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
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
                    <small class="text-muted">Showing <?= count($contracts) ?> contract</small>
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
                Are you sure you want to delete contract <strong id="deleteContractNumber" class="text-danger"></strong>
                for <strong id="deleteClientName"></strong>?
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

<!-- Details Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                    Contract Details: <span id="detailContractNumber" class="fw-semibold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0 text-secondary">
                            <tr>
                                <td style="width: 100px;">Client:</td>
                                <td class="text-dark fw-medium" id="detailClientName"></td>
                            </tr>
                            <tr>
                                <td>Start Date:</td>
                                <td class="text-dark fw-medium" id="detailStartDate"></td>
                            </tr>
                            <tr>
                                <td>End Date:</td>
                                <td class="text-dark fw-medium" id="detailEndDate"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0 text-secondary">
                            <tr>
                                <td style="width: 100px;">Status:</td>
                                <td id="detailStatus"></td>
                            </tr>
                            <tr>
                                <td>Total Value:</td>
                                <td class="text-dark fw-medium font-monospace" id="detailValue"></td>
                            </tr>
                            <tr id="detailRejectRow" class="d-none">
                                <td>Reason:</td>
                                <td class="text-danger fw-medium" id="detailRejectionReason"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <h6 class="border-bottom pb-2 mb-2 text-primary">Selected Products / Rate Card Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detailItemsBody">
                            <!-- Items loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Confirm Approve
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to approve contract <strong id="approveContractNumber"
                    class="text-success"></strong>?
                This will activate the contract and make it available for campaigns.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form id="approveForm" method="POST">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Confirmation Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="rejectForm" method="POST">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                        Confirm Reject
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject contract <strong id="rejectContractNumber"
                            class="text-danger"></strong>?</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-medium">Reason for Rejection <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="3"
                            placeholder="e.g. Please upload document with signatures" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Delete Modal
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('deleteContractNumber').textContent = btn.getAttribute('data-number');
        document.getElementById('deleteClientName').textContent = btn.getAttribute('data-client');
        document.getElementById('deleteForm').action = '<?= base_url('contract/delete/') ?>' + btn.getAttribute('data-id');
    });

    // Detail Modal AJAX
    document.getElementById('detailModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const id = btn.getAttribute('data-id');

        document.getElementById('detailContractNumber').textContent = 'Loading...';
        document.getElementById('detailClientName').textContent = 'Loading...';
        document.getElementById('detailStartDate').textContent = 'Loading...';
        document.getElementById('detailEndDate').textContent = 'Loading...';
        document.getElementById('detailStatus').innerHTML = '';
        document.getElementById('detailValue').textContent = 'Loading...';
        document.getElementById('detailItemsBody').innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
        document.getElementById('detailRejectRow').classList.add('d-none');

        fetch('<?= base_url('contract/get_items_json/') ?>' + id)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                const c = data.contract;
                const items = data.items;

                document.getElementById('detailContractNumber').textContent = c.contract_number;
                document.getElementById('detailClientName').textContent = c.client_name;
                document.getElementById('detailStartDate').textContent = new Date(c.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                document.getElementById('detailEndDate').textContent = new Date(c.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                document.getElementById('detailValue').textContent = 'Rp ' + Number(c.value).toLocaleString('id-ID');

                let statusBadge = '';
                if (c.status === 'approved') {
                    statusBadge = '<span class="badge bg-success bg-opacity-10 text-success fw-normal"><i class="bi bi-check-circle me-1"></i> Approved</span>';
                } else if (c.status === 'rejected') {
                    statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger fw-normal"><i class="bi bi-x-circle me-1"></i> Rejected</span>';
                    document.getElementById('detailRejectionReason').textContent = c.rejection_reason;
                    document.getElementById('detailRejectRow').classList.remove('d-none');
                } else {
                    statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis fw-normal"><i class="bi bi-clock-history me-1"></i> Pending Approval</span>';
                }
                document.getElementById('detailStatus').innerHTML = statusBadge;

                let html = '';
                if (items.length === 0) {
                    html = '<tr><td colspan="5" class="text-center text-muted">No products selected.</td></tr>';
                } else {
                    items.forEach(item => {
                        let catName = item.product_category.replace('_', ' ').toUpperCase();
                        let priceFormatted = 'Rp ' + Number(item.price).toLocaleString('id-ID');
                        let subtotalFormatted = 'Rp ' + Number(item.subtotal).toLocaleString('id-ID');
                        let qtyDisplay = item.quantity;
                        if (item.product_price_model === 'cpm') {
                            qtyDisplay = Number(item.quantity).toLocaleString('id-ID') + ' Imp (CPM)';
                        } else {
                            qtyDisplay = item.quantity + ' Unit';
                        }
                        html += `<tr>
                            <td class="fw-semibold text-dark">${item.product_name}</td>
                            <td><span class="badge bg-light text-secondary">${catName}</span></td>
                            <td>${qtyDisplay}</td>
                            <td>${priceFormatted}</td>
                            <td class="text-end fw-semibold text-dark">${subtotalFormatted}</td>
                        </tr>`;
                    });
                }
                document.getElementById('detailItemsBody').innerHTML = html;
            });
    });

    // Approve Modal
    document.getElementById('approveModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('approveContractNumber').textContent = btn.getAttribute('data-number');
        document.getElementById('approveForm').action = '<?= base_url('contract/approve/') ?>' + btn.getAttribute('data-id');
    });

    // Reject Modal
    document.getElementById('rejectModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('rejectContractNumber').textContent = btn.getAttribute('data-number');
        document.getElementById('rejectForm').action = '<?= base_url('contract/reject/') ?>' + btn.getAttribute('data-id');
    });
</script>

<?php $this->load->view('templates/footer'); ?>