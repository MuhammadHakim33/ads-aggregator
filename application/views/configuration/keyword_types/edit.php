<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('config/keyword-type') ?>" class="btn btn-sm btn-outline-secondary">
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
                            <h6 class="mb-0">Edit Keyword Type Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('config/keyword-type/edit/' . $keyword_type->id) ?>" method="POST">
                                <!-- Keyword Type Name -->
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-medium">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name', $keyword_type->name) ?>"
                                        placeholder="e.g. html, keyword, hostname" required autofocus>
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('config/keyword-type') ?>"
                                        class="btn btn-outline-secondary">Cancel</a>
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
