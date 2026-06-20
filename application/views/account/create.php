<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <!-- content area -->
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('account') ?>" class="btn btn-sm btn-outline-secondary">
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
                        <div class="card-header">
                            <h6 class="mb-0">Account Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('account/create') ?>" method="POST">
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name') ?>">
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-medium">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?= set_value('email') ?>">
                                    <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-medium">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-text">Minimum 6 characters.</div>
                                    </div>
                                    <?= form_error('password', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Role -->
                                <div class="mb-4">
                                    <label for="role_id" class="form-label fw-medium">
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="role_id" name="role_id">
                                        <option value="" disabled selected>Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role->id ?>" <?= set_select('role_id', $role->id) ?>>
                                                <?= ucwords($role->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= form_error('role_id', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('account') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Create Account
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
    // show/hide password
    document.getElementById('togglePassword').addEventListener('click', function () {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>

<?php $this->load->view('templates/footer'); ?>