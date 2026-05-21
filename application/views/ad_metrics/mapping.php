<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <!-- page heading -->
            <div class="d-flex justify-content-between align-items-center pb-2 mb-4">
                <div>
                    <a href="<?= base_url('ad-metrics') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div>
                    <?php $unmapped_count = count($unmapped ?? []); ?>
                    <span class="badge text-bg-<?= $unmapped_count > 0 ? 'warning' : 'success' ?> fs-6 px-3 py-2">
                        <i class="bi bi-<?= $unmapped_count > 0 ? 'exclamation-circle' : 'check-circle' ?> me-1"></i>
                        <?= $unmapped_count ?> not mapped
                    </span>
                </div>
            </div>

            <!-- flash alerts -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?= $this->session->flashdata('errors') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- empty state -->
            <?php if (empty($unmapped)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
                        <p class="text-muted mb-3">There is no ads that needs to be connected to a client.</p>
                    </div>
                </div>
            <?php else: ?>

            <!-- toolbar -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="text-muted small ms-2">
                    <span id="countRow">0</span> rows selected
                </span>
                <button type="button" id="btnSelectAll" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-check-square me-1"></i> Select All
                </button>
                <button type="button" id="btnDeselectAll" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-square me-1"></i> Cancel All
                </button>
                <select id="bulkClientSelect" class="form-select form-select-sm d-inline-block w-auto">
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c->id ?>"><?= htmlspecialchars($c->company_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btnBulkApply" class="btn btn-sm btn-outline-primary ms-1">
                    <i class="bi bi-arrow-down-square me-1"></i> Assign to Selected
                </button>
                <div class="ms-auto">
                </div>
            </div>

            <!-- mapping form -->
            <form action="<?= base_url('ad-metrics/mapping') ?>" method="POST" id="mappingForm">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:40px" class="text-center">
                                        <input type="checkbox" id="checkboxAll" class="form-check-input">
                                    </th>
                                    <th scope="col">Ads</th>
                                    <th scope="col">Platform</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Created Date</th>
                                    <th scope="col" style="width:260px">Assign to Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unmapped as $ad): ?>
                                <tr class="mapping-row">
                                    <td class="text-center">
                                        <input type="checkbox" name="selected_ids[]" value="<?= $ad->id ?>" class="form-check-input checkboxRow">
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($ad->title ?? '-') ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($ad->content_identifier) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light text-dark">
                                            <?= htmlspecialchars(ucfirst($ad->platform)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light text-dark">
                                            <?= htmlspecialchars(ucfirst($ad->ad_type)) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d M Y', strtotime($ad->created_at)) ?>
                                    </td>
                                    <td>
                                        <select name="client_id[<?= $ad->id ?>]" class="form-select form-select-sm client-select">
                                            <option value="">Select Client</option>
                                            <?php foreach ($clients as $c): ?>
                                                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->company_name) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted"><?= count($unmapped) ?> ad contents</small>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSave" disabled>
                            <i class="bi bi-floppy me-1"></i> Save Mapping
                        </button>
                    </div>
                </div>
            </form>

            <?php endif; ?>

        </div>
    </main>
</div>

<script>
    const checkboxAll = document.getElementById('checkboxAll');
    const checkboxRow = document.querySelectorAll('.checkboxRow');
    const countRow = document.getElementById('countRow');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselect = document.getElementById('btnDeselectAll');
    const bulkSelect = document.getElementById('bulkClientSelect');
    const btnBulkApply = document.getElementById('btnBulkApply');
    const btnSave = document.getElementById('btnSave');

    // count checked rows
    function countCheckedRows() {
        const n = document.querySelectorAll('.checkboxRow:checked').length;
        countRow.textContent = n;
        // if has 0 checked row, enable save btn, else disable save btn
        if (n === 0) {
            btnSave.disabled = true;
        } else {
            btnSave.disabled = false;
        }
    }

    // check all checkbox
    if (checkboxAll) {
        checkboxAll.addEventListener('change', function () {
            checkboxRow.forEach(c => { c.checked = this.checked; });
            countCheckedRows();
        });
    }

    checkboxRow.forEach(c => c.addEventListener('change', function () {
        countCheckedRows();
        // uncheck checkboxall if there are unchecked rows
        if (!this.checked) checkboxAll.checked = false;
    }));

    // btn select all
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function () {
            checkboxRow.forEach(c => c.checked = true);
            if (checkboxAll) checkboxAll.checked = true;
            countCheckedRows();
        });
    }

    // btn deselect all
    if (btnDeselect) {
        btnDeselect.addEventListener('click', function () {
            checkboxRow.forEach(c => c.checked = false);
            if (checkboxAll) checkboxAll.checked = false;
            countCheckedRows();
        });
    }

    // btn bulk apply to assign client for selected rows
    if (btnBulkApply) {
        btnBulkApply.addEventListener('click', function () {
            const clientId = bulkSelect.value;
            // show alert if client id is not selected
            if (!clientId) {
                alert("Please select client from dropdown.");
                return;
            }

            const checkedRows = document.querySelectorAll('.checkboxRow:checked');
            // show alert if there are no checked rows
            if (checkedRows.length === 0) {
                alert("Please select at least one row.");
                return;
            }

            // assign client to selected rows
            checkedRows.forEach(function (check) {
                const row = check.closest('tr');
                const select = row.querySelector('.client-select');
                if (select) select.value = clientId;
            });
        });
    }

    countCheckedRows();
</script>

<?php $this->load->view('templates/footer'); ?>
