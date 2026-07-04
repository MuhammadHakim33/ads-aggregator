<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('client') ?>" class="btn btn-sm btn-outline-secondary">
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
                            <h6 class="mb-0">Client Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('client/edit/' . $client->id) ?>" method="POST">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label fw-medium">
                                        Company Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="company_name" name="company_name"
                                        value="<?= set_value('company_name', $client->company_name) ?>">
                                    <?= form_error('company_name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="ae_id" class="form-label fw-medium">
                                        Account Executive
                                    </label>
                                    <select class="form-select" id="ae_id" name="ae_id">
                                        <option value="" disabled>Select AE</option>
                                        <?php foreach ($ae_list as $ae): ?>
                                            <option value="<?= $ae->id ?>" <?= set_select('ae_id', $ae->id, (int) $client->ae_id === (int) $ae->id) ?>>
                                                <?= ucwords($ae->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= form_error('ae_id', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-4">
                                    <label for="is_active" class="form-label fw-medium">Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?= set_select('is_active', '1', (bool) $client->is_active) ?>>
                                            Active</option>
                                        <option value="0" <?= set_select('is_active', '0', !(bool) $client->is_active) ?>>
                                            Inactive</option>
                                    </select>
                                    <?= form_error('is_active', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="card bg-light border mb-4">
                                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0 text-muted small">
                                                Manage contact persons and login accounts for this client.
                                            </p>
                                        </div>
                                        <a href="<?= base_url('pic?client_id=' . $client->id) ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            Manage PICs
                                        </a>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('client') ?>" class="btn btn-outline-secondary">Cancel</a>
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
    // No script needed
</script>

<?php $this->load->view('templates/footer'); ?>