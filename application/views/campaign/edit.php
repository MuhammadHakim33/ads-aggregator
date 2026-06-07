<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('campaign') ?>" class="btn btn-sm btn-outline-secondary">
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
                            <h6 class="mb-0">Campaign Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('campaign/edit/' . $campaign->id) ?>" method="POST">
                                
                                <div class="mb-3">
                                    <label for="contract_id" class="form-label fw-medium">
                                        Parent Contract <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="contract_id" name="contract_id" required>
                                        <option value="" disabled>Select Contract</option>
                                        <?php foreach ($contracts as $contract): ?>
                                            <option value="<?= $contract->id ?>" <?= set_select('contract_id', $contract->id, $campaign->contract_id == $contract->id) ?>>
                                                <?= htmlspecialchars(ucwords($contract->client_name)) ?> (<?= htmlspecialchars($contract->contract_number) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= form_error('contract_id', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        Campaign Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name', $campaign->name) ?>" required>
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-medium">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                        placeholder="Brief description of campaign goals..."><?= set_value('description', $campaign->description) ?></textarea>
                                    <?= form_error('description', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="start_date" class="form-label fw-medium">
                                            Start Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="<?= set_value('start_date', $campaign->start_date) ?>" required>
                                        <?= form_error('start_date', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label for="end_date" class="form-label fw-medium">
                                            End Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="<?= set_value('end_date', $campaign->end_date) ?>" required>
                                        <?= form_error('end_date', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('campaign') ?>" class="btn btn-outline-secondary">Cancel</a>
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

<?php $this->load->view('templates/footer'); ?>
