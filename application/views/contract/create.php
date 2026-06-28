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

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Contract Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('contract/create') ?>" method="POST"
                                enctype="multipart/form-data">

                                <div class="row">
                                    <?php if ($this->session->userdata('role') === 'client'): ?>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-medium">Client</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars(ucwords($client_company_name)) ?>" readonly>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-md-12 mb-3">
                                            <label for="client_id" class="form-label fw-medium">
                                                Client <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="client_id" name="client_id">
                                                <option value="" disabled selected>Select Client</option>
                                                <?php foreach ($clients as $client): ?>
                                                    <option value="<?= $client->id ?>" <?= set_select('client_id', $client->id) ?>>
                                                        <?= ucwords($client->company_name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?= form_error('client_id', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label fw-medium">
                                            Start Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="<?= set_value('start_date') ?>">
                                        <?= form_error('start_date', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label fw-medium">
                                            End Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="<?= set_value('end_date') ?>">
                                        <?= form_error('end_date', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="document" class="form-label fw-medium">
                                        Contract Document File
                                    </label>
                                    <input class="form-control" type="file" id="document" name="document"
                                        accept=".pdf,.doc,.docx">
                                    <div class="form-text text-muted">
                                        Only <strong>.pdf</strong>, <strong>.doc</strong>, and <strong>.docx</strong>
                                        files are allowed. Max size: 5MB.
                                    </div>
                                    <?= form_error('document', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <hr class="my-4">

                                <!-- Products Section -->
                                <div class="card mb-4 border-primary bg-light bg-opacity-10">
                                    <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0 fw-semibold text-primary"><i class="bi bi-cart-check me-2"></i>Select Iklan Products / Rate Card</h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="btn-add-item"><i class="bi bi-plus-lg me-1"></i>Add Product</button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-align-middle mb-0" id="table-contract-items">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Product <span class="text-danger">*</span></th>
                                                        <th style="width: 160px;">Price</th>
                                                        <th style="width: 120px;">Price Model</th>
                                                        <th style="width: 160px;">Quantity <span class="text-danger">*</span></th>
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

                                <div class="mb-4 bg-dark bg-opacity-10 p-3 rounded d-flex justify-content-between align-items-center">
                                    <span class="fs-6 fw-bold text-secondary">Total Contract Value:</span>
                                    <div class="input-group" style="width: 250px;">
                                        <span class="input-group-text bg-white text-secondary font-monospace fw-bold">Rp</span>
                                        <input type="number" step="0.01" class="form-control font-monospace fw-bold text-end" id="value" name="value"
                                            value="<?= set_value('value', '0') ?>" readonly>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('contract') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Create Contract
                                    </button>
                                </div>
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
    
    // Group products by category
    const categories = {
        'content_marketing': 'Content Marketing',
        'banner_ads': 'Banner Ads',
        'social_media': 'Social Media'
    };

    const container = document.getElementById('items-container');
    const btnAdd = document.getElementById('btn-add-item');
    const valueInput = document.getElementById('value');

    // Add first row on load
    addRow();

    btnAdd.addEventListener('click', function() {
        addRow();
    });

    function addRow() {
        const rowId = 'row-' + Date.now();
        const tr = document.createElement('tr');
        tr.id = rowId;
        
        let optionsHtml = '<option value="" disabled selected>Select Product</option>';
        
        // Group options by category
        const grouped = {};
        products.forEach(p => {
            if(!grouped[p.category]) grouped[p.category] = [];
            grouped[p.category].push(p);
        });

        for (const catKey in grouped) {
            let catLabel = categories[catKey] || catKey.replace('_', ' ').toUpperCase();
            optionsHtml += `<optgroup label="${catLabel}">`;
            grouped[catKey].forEach(p => {
                let modelLabel = p.price_model === 'cpm' ? '/ CPM' : '/ fixed';
                let priceFormatted = 'Rp ' + Number(p.price).toLocaleString('id-ID');
                optionsHtml += `<option value="${p.id}">${p.name} (${priceFormatted} ${modelLabel})</option>`;
            });
            optionsHtml += `</optgroup>`;
        }

        tr.innerHTML = `
            <td>
                <select class="form-select select-product" name="product_id[]" required>
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
                <input type="number" class="form-control input-qty text-end" name="quantity[]" min="1" value="1" required disabled>
                <div class="form-text text-muted input-qty-hint" style="font-size: 0.75rem; margin-top: 0.25rem;"></div>
            </td>
            <td class="text-end fw-semibold">
                <span class="span-subtotal font-monospace text-dark">Rp 0</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
            </td>
        `;

        container.appendChild(tr);

        // Bind events
        const select = tr.querySelector('.select-product');
        const qtyInput = tr.querySelector('.input-qty');
        const removeBtn = tr.querySelector('.btn-remove-row');

        select.addEventListener('change', function() {
            const pId = this.value;
            const product = products.find(p => p.id == pId);
            
            if(product) {
                qtyInput.disabled = false;
                tr.querySelector('.span-price').textContent = 'Rp ' + Number(product.price).toLocaleString('id-ID');
                tr.querySelector('.span-model').textContent = product.price_model.toUpperCase();
                
                // Show hint based on price model
                const hint = tr.querySelector('.input-qty-hint');
                if(product.price_model === 'cpm') {
                    hint.textContent = "Tayangan/Impresi (min. 1000)";
                    qtyInput.placeholder = "e.g. 50000";
                    qtyInput.min = "1000";
                    qtyInput.step = "1000";
                    if(qtyInput.value == 1 || qtyInput.value < 1000) qtyInput.value = 1000; // default for CPM
                } else {
                    hint.textContent = "Jumlah unit/posting";
                    qtyInput.placeholder = "e.g. 1";
                    qtyInput.min = "1";
                    qtyInput.step = "1";
                    if(qtyInput.value == 1000) qtyInput.value = 1;
                }
                
                calculateRow(tr, product);
            }
        });

        qtyInput.addEventListener('input', function() {
            const pId = select.value;
            const product = products.find(p => p.id == pId);
            if(product) {
                calculateRow(tr, product);
            }
        });

        removeBtn.addEventListener('click', function() {
            // Do not remove if it is the only row left
            if(container.children.length > 1) {
                tr.remove();
                calculateTotal();
            } else {
                alert("You must select at least one product.");
            }
        });
    }

    function calculateRow(tr, product) {
        const qty = parseFloat(tr.querySelector('.input-qty').value) || 0;
        let subtotal = 0;
        
        if(product.price_model === 'cpm') {
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
        const rows = container.querySelectorAll('tr');
        rows.forEach(tr => {
            let sub = parseFloat(tr.getAttribute('data-subtotal')) || 0;
            total += sub;
        });
        valueInput.value = total.toFixed(2);
    }
</script>

<?php $this->load->view('templates/footer'); ?>