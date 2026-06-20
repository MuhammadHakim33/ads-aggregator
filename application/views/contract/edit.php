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
                            <form action="<?= base_url('contract/edit/' . $contract->id) ?>" method="POST"
                                enctype="multipart/form-data">

                                <div class="row">
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

                                    <div class="col-md-6 mb-3">
                                        <label for="contract_number" class="form-label fw-medium">
                                            Contract Number
                                        </label>
                                        <input type="text" class="form-control-plaintext fw-semibold text-primary"
                                            id="contract_number" name="contract_number"
                                            value="<?= $contract->contract_number ?>" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="value" class="form-label fw-medium">
                                        Contract Value (IDR) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-secondary">Rp</span>
                                        <input type="number" step="0.01" class="form-control" id="value" name="value"
                                            value="<?= set_value('value', $contract->value) ?>">
                                    </div>
                                    <?= form_error('value', '<div class="form-text text-danger">', '</div>'); ?>
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

                                <div class="mb-4">
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
                                        <strong>.docx</strong>. Max size: 5MB. Select a new file to replace the current
                                        one.
                                    </div>
                                    <?= form_error('document', '<div class="form-text text-danger">', '</div>'); ?>
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
                                                <label for="termination_reason" class="form-label fw-medium">Reason for
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
                                    <a href="<?= base_url('contract') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> Save Changes
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
    const termToggle = document.getElementById('is_terminated');
    const termDetails = document.getElementById('termination_details');

    termToggle.addEventListener('change', function () {
        if (this.checked) {
            termDetails.classList.remove('d-none');
        } else {
            termDetails.classList.add('d-none');
        }
    });
</script>

<?php $this->load->view('templates/footer'); ?>