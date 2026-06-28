<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('complaint') ?>" class="btn btn-sm btn-outline-secondary">
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
                            <h6 class="mb-0">Complaint Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('complaint/create') ?>" method="POST">
                                <div class="mb-3">
                                    <label for="ad_content_id" class="form-label fw-medium">
                                        Ad Content <span class="text-danger">*</span>
                                    </label>
                                    <select name="ad_content_id" id="ad_content_id" class="form-select">
                                        <option value="" disabled selected>Select Ad Content</option>
                                        <?php foreach ($ads as $ad): ?>
                                            <option value="<?= $ad->id ?>" <?= set_select('ad_content_id', $ad->id) ?>>
                                                <?= htmlspecialchars($ad->title ?: $ad->content_identifier) ?> [<?= ucfirst($ad->platform) ?>] - Campaign: <?= htmlspecialchars($ad->campaign_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= form_error('ad_content_id', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label fw-medium">
                                        Subject <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="subject" id="subject" class="form-control" 
                                        placeholder="e.g. Ad metrics not updating, wrong graphic used..." 
                                        value="<?= set_value('subject') ?>">
                                    <?= form_error('subject', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-medium">
                                        Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="description" id="description" rows="6" class="form-control" 
                                        placeholder="Provide detailed information about the issue you are facing with this ad..."><?= set_value('description') ?></textarea>
                                    <?= form_error('description', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="d-flex gap-2 justify-content-end mt-4">
                                    <a href="<?= base_url('complaint') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">Submit Complaint</button>
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
