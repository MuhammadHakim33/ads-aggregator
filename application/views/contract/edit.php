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

                                    <?php if ($this->session->userdata('role') != 'client'): ?>
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
                                                current one.
                                            </div>
                                            <?= form_error('document', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-4">

                                    <!-- products -->
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-semibold">Products</h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item"
                                            <?= ($this->session->userdata('role') === 'client' && $contract->status === 'approved') ? 'disabled' : '' ?>>
                                            <i class="bi bi-plus-lg me-1"></i>Add Item
                                        </button>
                                    </div>

                                    <div id="items-container" class="d-flex flex-column gap-2 mb-3">
                                        <!-- dynamic item cards will be added here -->
                                    </div>

                                    <!-- total value bar -->
                                    <div
                                        class="border rounded p-3 mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                        <div class="fw-semibold text-secondary small text-uppercase"
                                            style="letter-spacing: 0.5px;">Total Contract Value</div>
                                        <div class="input-group" style="max-width: 240px;">
                                            <span class="input-group-text bg-light fw-bold text-secondary">Rp</span>
                                            <input type="number" step="0.01"
                                                class="form-control fw-bold text-end font-monospace fs-6" id="value"
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
    const products = <?php echo json_encode($products); ?>;
    const existingItems = <?php echo json_encode($contract_items); ?>;

    const categoryLabels = {
        content_marketing: 'Content Marketing',
        banner_ads: 'Banner Ads',
        social_media: 'Social Media'
    };

    const container = document.getElementById('items-container');
    const btnAdd = document.getElementById('btn-add-item');
    const valueInput = document.getElementById('value');
    const isFormDisabled = <?= ($this->session->userdata('role') === 'client' && $contract->status === 'approved') ? 'true' : 'false' ?>;

    // build grouped <optgroup> HTML (shared)
    function buildOptionsHtml(selectedId = null) {
        let html = selectedId ? '' : '<option value="" disabled selected>Select a product...</option>';
        const grouped = {};
        products.forEach(p => {
            if (!grouped[p.category]) grouped[p.category] = [];
            grouped[p.category].push(p);
        });
        for (const catKey in grouped) {
            const catLabel = categoryLabels[catKey] || catKey.replace(/_/g, ' ');
            html += `<optgroup label="${catLabel}">`;
            grouped[catKey].forEach(p => {
                const modelLabel = p.price_model === 'cpm' ? 'CPM' : 'Fixed';
                const priceFormatted = 'Rp ' + Number(p.price).toLocaleString('id-ID');
                const sel = (selectedId && selectedId == p.id) ? 'selected' : '';
                html += `<option value="${p.id}" ${sel}>${p.name} &mdash; ${priceFormatted} / ${modelLabel}</option>`;
            });
            html += '</optgroup>';
        }
        return html;
    }

    // Load existing items or add a blank one
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(ei => addRow(ei.product_id, ei.quantity));
    } else {
        if (!isFormDisabled) {
            addRow();
        } else {
            container.innerHTML = '<p class="text-muted small mb-0">No products selected.</p>';
        }
    }

    if (!isFormDisabled) {
        btnAdd.addEventListener('click', () => addRow());
    }

    function addRow(selectedProductId = null, qtyValue = null) {
        const item = document.createElement('div');
        item.className = 'border rounded p-3 bg-white item-row';
        item.dataset.subtotal = '0';

        const disabledAttr = isFormDisabled ? 'disabled' : '';
        const removeBtn = isFormDisabled ? '' : `
            <div class="col-auto d-flex align-items-start pt-4">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;

        item.innerHTML = `
            <div class="row g-2 align-items-start">
                <div class="col-12 col-sm">
                    <label class="form-label form-label-sm text-muted mb-1">Product <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm select-product" name="product_id[]" required ${disabledAttr}>
                        ${buildOptionsHtml(selectedProductId)}
                    </select>
                </div>
                <div class="col-6 col-sm-auto" style="min-width: 140px;">
                    <label class="form-label form-label-sm text-muted mb-1">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm input-qty text-end"
                        name="quantity[]" min="1" value="${qtyValue ?? 1}" required disabled ${disabledAttr}>
                    <div class="form-text input-qty-hint mt-1" style="min-height: 1rem;"></div>
                </div>
                <div class="col-6 col-sm-auto text-end" style="min-width: 130px;">
                    <label class="form-label form-label-sm text-muted mb-1">Subtotal</label>
                    <div class="fw-semibold font-monospace pt-1 span-subtotal">Rp 0</div>
                    <div class="form-text span-price-hint mt-1" style="min-height: 1rem;"></div>
                </div>
                ${removeBtn}
            </div>
        `;

        container.appendChild(item);

        const select = item.querySelector('.select-product');
        const qtyInput = item.querySelector('.input-qty');

        const setupProduct = (pId) => {
            const product = products.find(p => p.id == pId);
            if (!product) return;

            if (!isFormDisabled) qtyInput.disabled = false;

            const hint = item.querySelector('.input-qty-hint');
            const priceHint = item.querySelector('.span-price-hint');
            priceHint.textContent = 'Rp ' + Number(product.price).toLocaleString('id-ID')
                + (product.price_model === 'cpm' ? ' / 1,000 impr' : ' / unit');

            if (product.price_model === 'cpm') {
                hint.textContent = 'Total impressions (min. 1,000)';
                qtyInput.placeholder = 'e.g. 50000';
                qtyInput.min = '1000';
                qtyInput.step = '1000';
                if (!qtyValue && (qtyInput.value == 1 || Number(qtyInput.value) < 1000)) qtyInput.value = 1000;
            } else {
                hint.textContent = 'Number of units/posts';
                qtyInput.placeholder = 'e.g. 1';
                qtyInput.min = '1';
                qtyInput.step = '1';
                if (!qtyValue && qtyInput.value == 1000) qtyInput.value = 1;
            }

            calculateRow(item, product);
        };

        select.addEventListener('change', function () {
            setupProduct(this.value);
        });

        qtyInput.addEventListener('input', function () {
            const product = products.find(p => p.id == select.value);
            if (product) calculateRow(item, product);
        });

        if (!isFormDisabled) {
            const removeBtnEl = item.querySelector('.btn-remove-row');
            if (removeBtnEl) {
                removeBtnEl.addEventListener('click', function () {
                    if (container.children.length > 1) {
                        item.remove();
                        calculateTotal();
                    } else {
                        alert('At least one product line item is required.');
                    }
                });
            }
        }

        if (selectedProductId) setupProduct(selectedProductId);
    }

    function calculateRow(item, product) {
        const qty = parseFloat(item.querySelector('.input-qty').value) || 0;
        const subtotal = product.price_model === 'cpm'
            ? (qty / 1000) * product.price
            : qty * product.price;

        item.querySelector('.span-subtotal').textContent = 'Rp ' + Number(subtotal).toLocaleString('id-ID');
        item.dataset.subtotal = subtotal;
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        container.querySelectorAll('.item-row').forEach(item => {
            total += parseFloat(item.dataset.subtotal) || 0;
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