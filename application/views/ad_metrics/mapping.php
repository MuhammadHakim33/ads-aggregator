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

            <?php if (empty($unmapped)): ?>
                <!-- empty state -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
                        <h6 class="fw-semibold">All Ad Content has been mapped!</h6>
                        <p class="text-muted mb-3">There is no ad content that needs to be connected to a client.</p>
                        <a href="<?= base_url('ad-metrics') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-bar-chart-line me-1"></i> View Ad Metrics
                        </a>
                    </div>
                </div>
            <?php else: ?>

            <!-- toolbar -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <button type="button" id="btnSelectAll" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-check-square me-1"></i> Select All
                </button>
                <button type="button" id="btnDeselectAll" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-square me-1"></i> Cancel All
                </button>
                <span class="text-muted small ms-2">
                    <span id="selectedCount">0</span> rows selected
                </span>
                <div class="ms-auto">
                    <select id="bulkClientSelect" class="form-select form-select-sm d-inline-block w-auto">
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c->id ?>"><?= htmlspecialchars($c->company_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btnBulkApply" class="btn btn-sm btn-outline-primary ms-1">
                        <i class="bi bi-arrow-down-square me-1"></i> Assign to Selected
                    </button>
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
                                        <input type="checkbox" id="checkAll" class="form-check-input">
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
                                        <input type="checkbox" name="selected_ids[]" value="<?= $ad->id ?>" class="form-check-input row-check">
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
                                        <select name="client_id[<?= $ad->id ?>]"
                                                class="form-select form-select-sm client-select">
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
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSave">
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
(function () {
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const selectedCount = document.getElementById('selectedCount');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselect = document.getElementById('btnDeselectAll');
    const bulkSelect = document.getElementById('bulkClientSelect');
    const btnBulkApply = document.getElementById('btnBulkApply');

    function updateCount() {
        const n = document.querySelectorAll('.row-check:checked').length;
        selectedCount.textContent = n;
    }

    // header checkbox check/uncheck all
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks.forEach(c => { c.checked = this.checked; });
            updateCount();
        });
    }

    rowChecks.forEach(c => c.addEventListener('change', function () {
        updateCount();
        if (!this.checked && checkAll) checkAll.checked = false;
        if (document.querySelectorAll('.row-check:checked').length === rowChecks.length && checkAll) {
            checkAll.checked = true;
        }
    }));

    // select all/deselect all buttons
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function () {
            rowChecks.forEach(c => c.checked = true);
            if (checkAll) checkAll.checked = true;
            updateCount();
        });
    }
    if (btnDeselect) {
        btnDeselect.addEventListener('click', function () {
            rowChecks.forEach(c => c.checked = false);
            if (checkAll) checkAll.checked = false;
            updateCount();
        });
    }

    // bulk apply set selected client to all checked rows
    if (btnBulkApply) {
        btnBulkApply.addEventListener('click', function () {
            const clientId = bulkSelect.value;
            if (!clientId) {
                alert('Pilih klien terlebih dahulu pada dropdown "Assign semua ke".');
                return;
            }
            const checkedRows = document.querySelectorAll('.row-check:checked');
            if (checkedRows.length === 0) {
                alert('Pilih minimal satu baris terlebih dahulu.');
                return;
            }
            checkedRows.forEach(function (check) {
                const row = check.closest('tr');
                const sel = row.querySelector('.client-select');
                if (sel) sel.value = clientId;
            });
        });
    }

    updateCount();
})();
</script>

<?php $this->load->view('templates/footer'); ?>
