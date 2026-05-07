<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
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
                            <h6 class="mb-0">Edit Keyword Information</h6>
                            <a href="<?= base_url('config/filter-keyword') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('config/filter-keyword/edit/' . $keyword->id) ?>" method="POST">
                                <div class="mb-3">
                                    <label for="platform" class="form-label fw-medium">
                                        Platform <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="platform" name="platform" required>
                                        <option value="facebook" <?= set_select('platform', 'facebook', $keyword->platform === 'facebook') ?>>Facebook</option>
                                        <option value="instagram" <?= set_select('platform', 'instagram', $keyword->platform === 'instagram') ?>>Instagram</option>
                                        <option value="gam" <?= set_select('platform', 'gam', $keyword->platform === 'gam') ?>>GAM</option>
                                        <option value="ga4" <?= set_select('platform', 'ga4', $keyword->platform === 'ga4') ?>>GA4</option>
                                        <option value="youtube" <?= set_select('platform', 'youtube', $keyword->platform === 'youtube') ?>>YouTube</option>
                                    </select>
                                    <?= form_error('platform', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="type" class="form-label fw-medium">
                                        Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="html" <?= set_select('type', 'html', $keyword->type === 'html') ?>>HTML</option>
                                        <option value="keyword" <?= set_select('type', 'keyword', $keyword->type === 'keyword') ?>>Keyword</option>
                                        <option value="hostname" <?= set_select('type', 'hostname', $keyword->type === 'hostname') ?>>Hostname</option>
                                    </select>
                                    <?= form_error('type', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <div class="mb-3">
                                    <label for="keyword" class="form-label fw-medium">
                                        Keyword <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        id="keyword"
                                        name="keyword"
                                        value="<?= set_value('keyword', $keyword->keyword) ?>"
                                        placeholder="e.g. Content partnership"
                                        required minlength="2" maxlength="255">
                                    <?= form_error('keyword', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <div class="mb-4">
                                    <label for="is_active" class="form-label fw-medium">Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?= set_select('is_active', '1', $keyword->is_active == 1) ?>>Aktif</option>
                                        <option value="0" <?= set_select('is_active', '0', $keyword->is_active == 0) ?>>Nonaktif</option>
                                    </select>
                                    <?= form_error('is_active', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('config/filter-keyword') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Changes
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
