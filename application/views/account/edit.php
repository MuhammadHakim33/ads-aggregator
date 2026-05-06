<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <!-- content area -->
        <div class="container-fluid py-4">
            <!-- form card -->
            <div class="row">
                <div class="col-12 col-lg-7">
                    <!-- alert message (non-validation errors) -->
                    <?php if ($this->session->flashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div><?= $this->session->flashdata('errors') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Account Information</h6>
                            <a href="<?= base_url('account') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('account/edit/' . $account->id) ?>" method="POST">
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        id="name"
                                        name="name"
                                        value="<?= set_value('name', $account->name) ?>"
                                        placeholder="e.g. John Doe"
                                        required>
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-medium">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?= set_value('email', $account->email) ?>"
                                        placeholder="e.g. john@example.com"
                                        required>
                                    <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-medium">
                                        Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control"
                                            id="password"
                                            name="password"
                                            placeholder="Kosongkan jika tidak ingin mengubah">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                                    <?= form_error('password', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Role -->
                                <div class="mb-3">
                                    <label for="role" class="form-label fw-medium">
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="" disabled>-- Select Role --</option>
                                        <option value="ae" <?= set_select('role', 'ae', $account->role === 'ae') ?>>AE (Account Executive)</option>
                                        <option value="superadmin" <?= set_select('role', 'superadmin', $account->role === 'superadmin') ?>>Superadmin</option>
                                    </select>
                                    <?= form_error('role', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>
                                <!-- Status -->
                                <div class="mb-4">
                                    <label for="is_active" class="form-label fw-medium">Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?= set_select('is_active', '1', (bool)$account->is_active) ?>>Aktif</option>
                                        <option value="0" <?= set_select('is_active', '0', !(bool)$account->is_active) ?>>Nonaktif</option>
                                    </select>
                                    <?= form_error('is_active', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('account') ?>" class="btn btn-outline-secondary">Cancel</a>
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
