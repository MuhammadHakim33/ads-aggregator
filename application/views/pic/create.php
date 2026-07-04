<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('pic?client_id=' . $client->id) ?>" class="btn btn-sm btn-outline-secondary">
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
                            <h6 class="mb-0">PIC Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pic/create?client_id=' . $client->id) ?>" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        PIC Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name') ?>">
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-4">
                                    <label for="position" class="form-label fw-medium">
                                        Position
                                    </label>
                                    <input type="text" class="form-control" id="position" name="position"
                                        value="<?= set_value('position') ?>">
                                    <?= form_error('position', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="create_account"
                                        id="create_account" value="1" <?= set_checkbox('create_account', '1') ?>>
                                    <p class=" fs-6" for="create_account">
                                        Create User Account
                                    </p>
                                </div>

                                <div id="account_fields" class="d-none border p-3 rounded mb-4 bg-light">
                                    <h6 class="mb-3 border-bottom pb-2 text-secondary">Account Details</h6>

                                    <div class="mb-3">
                                        <label for="username" class="form-label fw-medium">Username
                                            <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="<?= set_value('username') ?>">
                                        <?= form_error('username', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-medium">Email
                                            <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= set_value('email') ?>">
                                        <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-medium">Password
                                            <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Minimum 6 characters.</div>
                                        <?= form_error('password', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('pic?client_id=' . $client->id) ?>"
                                        class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Add PIC
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
    const createAccountCheck = document.getElementById('create_account');
    const accountFields = document.getElementById('account_fields');

    function toggleAccountFields() {
        if (createAccountCheck.checked) {
            accountFields.classList.remove('d-none');
        } else {
            accountFields.classList.add('d-none');
        }
    }

    createAccountCheck.addEventListener('change', toggleAccountFields);
    toggleAccountFields(); // Initial call on load

    // show/hide password
    const togglePasswordBtn = document.getElementById('togglePassword');
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function () {
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
    }
</script>

<?php $this->load->view('templates/footer'); ?>