<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
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
                            <form action="<?= base_url('contract/create') ?>" method="POST" enctype="multipart/form-data">
                                
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="client_id" class="form-label fw-medium">
                                            Client <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="client_id" name="client_id" required>
                                            <option value="" disabled selected>Select Client</option>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?= $client->id ?>" <?= set_select('client_id', $client->id) ?>>
                                                    <?= htmlspecialchars(ucwords($client->company_name)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= form_error('client_id', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="value" class="form-label fw-medium">
                                        Contract Value (IDR) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-secondary">Rp</span>
                                        <input type="number" step="0.01" class="form-control" id="value" name="value"
                                            placeholder="e.g. 50000000" value="<?= set_value('value') ?>" required>
                                    </div>
                                    <?= form_error('value', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label fw-medium">
                                            Start Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="<?= set_value('start_date') ?>" required>
                                        <?= form_error('start_date', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label fw-medium">
                                            End Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="<?= set_value('end_date') ?>" required>
                                        <?= form_error('end_date', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="document" class="form-label fw-medium">
                                        Contract Document File
                                    </label>
                                    <input class="form-control" type="file" id="document" name="document" accept=".pdf,.doc,.docx">
                                    <div class="form-text text-muted">
                                        Only <strong>.pdf</strong>, <strong>.doc</strong>, and <strong>.docx</strong> files are allowed. Max size: 5MB.
                                    </div>
                                    <?= form_error('document', '<div class="form-text text-danger">', '</div>'); ?>
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

<?php $this->load->view('templates/footer'); ?>
