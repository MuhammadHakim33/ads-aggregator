<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('contract') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
                <!-- form card -->
                <div class="col-12 col-lg-7">
                    <!-- alert message -->
                    <?php if ($this->session->flashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div><?= $this->session->flashdata('errors') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->userdata('role') === 'client' && $contract->status === 'approved'): ?>
                        <div class="alert alert-success border-success mb-3 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>Approved Contract:</strong> Kontrak ini telah disetujui oleh Manajemen dan telah
                                dikunci. Anda tidak dapat melakukan perubahan lagi.
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($contract->status === 'rejected'): ?>
                        <div class="alert alert-danger border-danger mb-3">
                            <h6 class="alert-heading fw-bold d-flex align-items-center"><i
                                    class="bi bi-exclamation-octagon-fill me-2"></i>Kontrak Ditolak (Rejected)</h6>
                            <p class="mb-0 small"><strong>Alasan Penolakan:</strong>
                                <?= htmlspecialchars($contract->rejection_reason) ?></p>
                            <hr class="my-2">
                            <small class="text-muted">Silakan sesuaikan item produk atau dokumen di bawah ini, lalu simpan
                                kembali untuk mengajukan ulang persetujuan.</small>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Contract Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('contract/edit/' . $contract->id) ?>" method="POST"
                                enctype="multipart/form-data">
                                <fieldset <?= ($this->session->userdata('role') === 'client' && $contract->status === 'approved') ? 'disabled' : '' ?>>

                                    <div class="row">
                                        <?php if ($this->session->userdata('role') === 'client'): ?>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-medium">Client</label>
                                                <input type="text" class="form-control"
                                                    value="<?= htmlspecialchars(ucwords($contract->client_name)) ?>"
                                                    readonly>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-md-6 mb-3">
                                                <label for="client_id" class="form-label fw-medium">
                                                    Client <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select" id="client_id" name="client_id">
                                                    <option value="" disabled>Select Client</option>
                                                    <?php foreach ($clients as $client): ?>
                                                        <option value="<?= $client->id ?>" <?= set_select('client_id', $client->id, $contract->client_id == $client->id) ?>>
                                                            <?= ucwords($client->company_name) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?= form_error('client_id', '<div class="form-text text-danger">', '</div>'); ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="col-md-6 mb-3">
                                            <label for="contract_number" class="form-label fw-medium">
                                                Contract Number
                                            </label>
                                            <input type="text"
                                                class="form-control-plaintext fw-semibold text-primary font-monospace"
                                                id="contract_number" name="contract_number"
                                                value="<?= $contract->contract_number ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label fw-medium">
                                                Start Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="start_date" name="start_date"
                                                value="<?= set_value('start_date', $contract->start_date) ?>">
                                            <?= form_error('start_date', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="end_date" class="form-label fw-medium">
                                                End Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="end_date" name="end_date"
                                                value="<?= set_value('end_date', $contract->end_date) ?>">
                                            <?= form_error('end_date', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="document" class="form-label fw-medium">
                                            Contract Document File
                                        </label>
                                        <?php if ($contract->document_path): ?>
                                            <div
                                                class="p-2 border rounded bg-light mb-2 d-flex justify-content-between align-items-center">
                                                <span class="text-secondary" style="font-size: 0.85rem;">
                                                    <i class="bi bi-file-earmark-check text-primary me-2"></i>
                                                    Currently uploaded file exists.
                                                </span>
                                                <a href="<?= base_url('contract/download/' . $contract->id) ?>"
                                                    class="btn btn-sm btn-outline-primary py-0">
                                                    <i class="bi bi-download"></i> Download Current
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <input class="form-control" type="file" id="document" name="document"
                                            accept=".pdf,.doc,.docx">
                                        <div class="form-text text-muted">
                                            Allowed files: <strong>.pdf</strong>, <strong>.doc</strong>,
                                            <strong>.docx</strong>. Max size: 5MB. Select a new file to replace the
                                            current
                                            one.
                                        </div>
                                        <?= form_error('document', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Products Section -->
                                    <div class="card mb-4 border-primary bg-light bg-opacity-10">
                                        <div
                                            class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center py-2">
                                            <h6 class="mb-0 fw-semibold text-primary"><i
                                                    class="bi bi-cart-check me-2"></i>Select Iklan Products / Rate Card
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-primary" id="btn-add-item"
                                                <?= ($this->session->userdata('role') === 'client' && $contract->status === 'approved') ? 'disabled' : '' ?>><i
                                                    class="bi bi-plus-lg me-1"></i>Add Product</button>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-align-middle mb-0" id="table-contract-items">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Product <span class="text-danger">*</span></th>
                                                            <th style="width: 160px;">Price</th>
                                                            <th style="width: 120px;">Price Model</th>
                                                            <th style="width: 160px;">Quantity <span
                                                                    class="text-danger">*</span></th>
                                                            <th style="width: 180px;" class="text-end">Subtotal</th>
                                                            <th style="width: 50px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="items-container">
                                                        <!-- Dynamic rows will be added here -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="mb-4 bg-dark bg-opacity-10 p-3 rounded d-flex justify-content-between align-items-center">
                                        <span class="fs-6 fw-bold text-secondary">Total Contract Value:</span>
                                        <div class="input-group" style="width: 250px;">
                                            <span
                                                class="input-group-text bg-white text-secondary font-monospace fw-bold">Rp</span>
                                            <input type="number" step="0.01"
                                                class="form-control font-monospace fw-bold text-end" id="value"
                                                name="value" value="<?= set_value('value', $contract->value) ?>"
                                                readonly>
                                        </div>
                                    </div>

                                    <!-- termination Panel -->
                                    <div class="card border-warning mb-4 bg-light bg-opacity-50">
                                        <div class="card-body">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="is_terminated" name="is_terminated" value="1"
                                                    <?= set_checkbox('is_terminated', '1', $contract->terminated_at !== null) ?>>
                                                <label class="form-check-label fw-medium text-warning-emphasis"
                                                    for="is_terminated">
                                                    Terminate Contract Early
                                                </label>
                                            </div>

                                            <div id="termination_details"
                                                class="mt-4 <?= $contract->terminated_at === null ? 'd-none' : '' ?>">
                                                <div class="mb-3">
                                                    <label for="terminated_at" class="form-label fw-medium">Termination
                                                        Date</label>
                                                    <input type="date" class="form-control" id="terminated_at"
                                                        name="terminated_at"
                                                        value="<?= set_value('terminated_at', $contract->terminated_at ? date('Y-m-d', strtotime($contract->terminated_at)) : '') ?>">
                                                    <div class="form-text text-muted">Leave blank to default to current
                                                        date.</div>
                                                    <?= form_error('terminated_at', '<div class="form-text text-danger">', '</div>'); ?>
                                                </div>

                                                <div class="mb-2">
                                                    <label for="termination_reason" class="form-label fw-medium">Reason
                                                        for
                                                        Termination</label>
                                                    <textarea class="form-control" id="termination_reason"
                                                        name="termination_reason"
                                                        rows="3"><?= set_value('termination_reason', $contract->termination_reason) ?></textarea>
                                                    <?= form_error('termination_reason', '<div class="form-text text-danger">', '</div>'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= base_url('contract') ?>"
                                            class="btn btn-outline-secondary">Cancel</a>
                                        <?php if (!($this->session->userdata('role') === 'client' && $contract->status === 'approved')): ?>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-lg me-1"></i> Save Changes
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Local products array passed from PHP controller
    const products = <?php echo json_encode($products); ?>;
    const existingItems = <?php echo json_encode($contract_items); ?>;

    // Group products by category
    const categories = {
        'content_marketing': 'Content Marketing',
        'banner_ads': 'Banner Ads',
        'social_media': 'Social Media'
    };

    const container = document.getElementById('items-container');
    const btnAdd = document.getElementById('btn-add-item');
    const valueInput = document.getElementById('value');

    // Is form disabled
    const isFormDisabled = <?= ($this->session->userdata('role') === 'client' && $contract->status === 'approved') ? 'true' : 'false' ?>;

    // Load existing items or add a blank one
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => {
            addRow(item.product_id, item.quantity);
        });
    } else {
        if (!isFormDisabled) {
            addRow();
        } else {
            container.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No products selected.</td></tr>';
        }
    }

    if (!isFormDisabled) {
        btnAdd.addEventListener('click', function () {
            addRow();
        });
    }

    function addRow(selectedProductId = null, qtyValue = 1) {
        const rowId = 'row-' + Date.now() + Math.random().toString(36).substr(2, 5);
        const tr = document.createElement('tr');
        tr.id = rowId;

        let optionsHtml = '';
        if (!selectedProductId) {
            optionsHtml = '<option value="" disabled selected>Select Product</option>';
        }

        // Group options by category
        const grouped = {};
        products.forEach(p => {
            if (!grouped[p.category]) grouped[p.category] = [];
            grouped[p.category].push(p);
        });

        for (const catKey in grouped) {
            let catLabel = categories[catKey] || catKey.replace('_', ' ').toUpperCase();
            optionsHtml += `<optgroup label="${catLabel}">`;
            grouped[catKey].forEach(p => {
                let modelLabel = p.price_model === 'cpm' ? '/ CPM' : '/ fixed';
                let priceFormatted = 'Rp ' + Number(p.price).toLocaleString('id-ID');
                let selectedAttr = (selectedProductId && selectedProductId == p.id) ? 'selected' : '';
                optionsHtml += `<option value="${p.id}" ${selectedAttr}>${p.name} (${priceFormatted} ${modelLabel})</option>`;
            });
            optionsHtml += `</optgroup>`;
        }

        const disabledAttr = isFormDisabled ? 'disabled' : '';

        tr.innerHTML = `
            <td>
                <select class="form-select select-product" name="product_id[]" required ${disabledAttr}>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <span class="span-price font-monospace text-secondary">-</span>
            </td>
            <td>
                <span class="span-model text-secondary">-</span>
            </td>
            <td>
                <input type="number" class="form-control input-qty text-end" name="quantity[]" min="1" value="${qtyValue}" required disabled ${disabledAttr}>
                <div class="form-text text-muted input-qty-hint" style="font-size: 0.75rem; margin-top: 0.25rem;"></div>
            </td>
            <td class="text-end fw-semibold">
                <span class="span-subtotal font-monospace text-dark">Rp 0</span>
            </td>
            <td class="text-center">
                ${!isFormDisabled ? '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>' : ''}
            </td>
        `;

        container.appendChild(tr);

        // Bind events
        const select = tr.querySelector('.select-product');
        const qtyInput = tr.querySelector('.input-qty');

        const setupRow = (pId) => {
            const product = products.find(p => p.id == pId);
            if (product) {
                if (!isFormDisabled) qtyInput.disabled = false;
                tr.querySelector('.span-price').textContent = 'Rp ' + Number(product.price).toLocaleString('id-ID');
                tr.querySelector('.span-model').textContent = product.price_model.toUpperCase();

                const hint = tr.querySelector('.input-qty-hint');
                if (product.price_model === 'cpm') {
                    hint.textContent = "Tayangan/Impresi (min. 1000)";
                    qtyInput.placeholder = "e.g. 50000";
                    qtyInput.min = "1000";
                    qtyInput.step = "1000";
                } else {
                    hint.textContent = "Jumlah unit/posting";
                    qtyInput.placeholder = "e.g. 1";
                    qtyInput.min = "1";
                    qtyInput.step = "1";
                }

                calculateRow(tr, product);
            }
        };

        select.addEventListener('change', function () {
            setupRow(this.value);
        });

        qtyInput.addEventListener('input', function () {
            const pId = select.value;
            const product = products.find(p => p.id == pId);
            if (product) {
                calculateRow(tr, product);
            }
        });

        if (!isFormDisabled) {
            const removeBtn = tr.querySelector('.btn-remove-row');
            removeBtn.addEventListener('click', function () {
                if (container.children.length > 1) {
                    tr.remove();
                    calculateTotal();
                } else {
                    alert("You must select at least one product.");
                }
            });
        }

        // Initialize if preselected
        if (selectedProductId) {
            setupRow(selectedProductId);
        }
    }

    function calculateRow(tr, product) {
        const qty = parseFloat(tr.querySelector('.input-qty').value) || 0;
        let subtotal = 0;

        if (product.price_model === 'cpm') {
            subtotal = (qty / 1000) * product.price;
        } else {
            subtotal = qty * product.price;
        }

        tr.querySelector('.span-subtotal').textContent = 'Rp ' + Number(subtotal).toLocaleString('id-ID');
        tr.setAttribute('data-subtotal', subtotal);

        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        const rows = container.querySelectorAll('tr[data-subtotal]');
        rows.forEach(tr => {
            let sub = parseFloat(tr.getAttribute('data-subtotal')) || 0;
            total += sub;
        });
        valueInput.value = total.toFixed(2);
    }
</script>

<script>
    const termToggle = document.getElementById('is_terminated');
    const termDetails = document.getElementById('termination_details');

    if (termToggle) {
        termToggle.addEventListener('change', function () {
            if (this.checked) {
                termDetails.classList.remove('d-none');
            } else {
                termDetails.classList.add('d-none');
            }
        });
    }
</script>

<?php $this->load->view('templates/footer'); ?>