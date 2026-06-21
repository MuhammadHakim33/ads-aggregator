<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <!-- flash alerts -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('errors') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="row g-4">
                <!-- profile card -->
                <div class="col-12 col-md-4 col-xl-3">
                    <div class="card text-center h-100">
                        <div class="card-body py-4">
                            <div class="mx-auto mb-3 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <span class="fw-bold text-primary" style="font-size: 2rem;">
                                    <?= strtoupper(mb_substr($account->name, 0, 1)) ?>
                                </span>
                            </div>

                            <h6 class="fw-semibold mb-1"><?= $account->name ?></h6>
                            <div class="text-muted small mb-2"><?= $account->email ?></div>

                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1">
                                <?= ucwords($account->role_name ?? $current_account['role']) ?>
                            </span>

                            <?php if (!empty($account->is_active)): ?>
                                <div class="mt-3">
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="mt-3">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Inactive
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted small">
                            <i class="bi bi-clock me-1"></i>
                            Member since
                            <?= !empty($account->created_at) ? date('M Y', strtotime($account->created_at)) : '—' ?>
                        </div>
                    </div>
                </div>
                <!-- form -->
                <div class="col-12 col-md-8 col-xl-9">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Account Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('account/profile') ?>" method="POST" id="profileForm">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label for="name" class="form-label fw-medium">
                                            Full Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="<?= set_value('name', $account->name) ?>"
                                            placeholder="Your full name">
                                        <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="email" class="form-label fw-medium">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= set_value('email', $account->email) ?>"
                                            placeholder="email@example.com">
                                        <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary" form="profileForm" name="section"
                                        value="info">
                                        <i class="bi bi-check-lg me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Change Password</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('account/profile') ?>" method="POST" id="passwordForm">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label for="password" class="form-label fw-medium">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Min. 6 characters" autocomplete="new-password">
                                            <button class="btn btn-outline-secondary toggle-pwd" type="button"
                                                data-target="password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <?= form_error('password', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="password_confirm" class="form-label fw-medium">Confirm New
                                            Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password_confirm"
                                                name="password_confirm" placeholder="Repeat new password"
                                                autocomplete="new-password">
                                            <button class="btn btn-outline-secondary toggle-pwd" type="button"
                                                data-target="password_confirm">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <?= form_error('password_confirm', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-warning" name="section" value="password">
                                        <i class="bi bi-shield-check me-1"></i> Update Password
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
    document.querySelectorAll('.toggle-pwd').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });
</script>

<?php $this->load->view('templates/footer'); ?>