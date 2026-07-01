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
                        <i class="bi bi-arrow-left me-1"></i> Back to PIC List
                    </a>
                </div>
                <!-- form card -->
                <div class="col-12 col-lg-7">
                    <!-- alert messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div><?= $this->session->flashdata('errors') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Edit PIC Information &mdash;
                                <?= htmlspecialchars(ucwords($client->company_name)) ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pic/edit/' . $pic->id) ?>" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        PIC Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name', $pic->name) ?>" placeholder="e.g. John Doe">
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="position" class="form-label fw-medium">
                                        Position / Job Title
                                    </label>
                                    <input type="text" class="form-control" id="position" name="position"
                                        value="<?= set_value('position', $pic->position) ?>"
                                        placeholder="e.g. Marketing Manager">
                                    <?= form_error('position', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-4">
                                    <label for="is_active" class="form-label fw-medium">PIC Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?= set_select('is_active', '1', (int) $pic->is_active === 1) ?>>
                                            Active</option>
                                        <option value="0" <?= set_select('is_active', '0', (int) $pic->is_active === 0) ?>>
                                            Inactive</option>
                                    </select>
                                    <?= form_error('is_active', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <?php if ($account): ?>
                                    <!-- Existing Login Account details -->
                                    <input type="hidden" name="has_account" value="1">
                                    <div class="border p-3 rounded mb-4 bg-light">
                                        <div
                                            class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                            <h6 class="mb-0 text-secondary">Account Details</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0"
                                                data-bs-toggle="modal" data-bs-target="#unlinkModal">
                                                <i class="bi bi-link-45deg me-1"></i> Delete Account
                                            </button>
                                        </div>

                                        <div class="mb-3">
                                            <label for="username" class="form-label fw-medium">Username <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="<?= set_value('username', $account->name) ?>">
                                            <?= form_error('username', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-medium">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?= set_value('email', $account->email) ?>">
                                            <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label fw-medium">Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password" name="password"
                                                    placeholder="Leave blank to keep current password">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Minimum 6 characters. Only fill if you want to change the
                                                password.</div>
                                            <?= form_error('password', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>

                                        <div class="mb-1">
                                            <label for="account_active" class="form-label fw-medium">Account Status</label>
                                            <select class="form-select" id="account_active" name="account_active">
                                                <option value="1" <?= set_select('account_active', '1', (int) $account->is_active === 1) ?>>Active</option>
                                                <option value="0" <?= set_select('account_active', '0', (int) $account->is_active === 0) ?>>Inactive</option>
                                            </select>
                                            <?= form_error('account_active', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- No account yet: Option to create one -->
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="create_account"
                                            id="create_account" value="1" <?= set_checkbox('create_account', '1') ?>>
                                        <label class="form-check-label fw-medium text-primary" for="create_account">
                                            Create User Account / Login Access for this PIC
                                        </label>
                                    </div>

                                    <div id="account_fields" class="d-none border p-3 rounded mb-4 bg-light">
                                        <h6 class="mb-3 border-bottom pb-2 text-secondary">Account Details</h6>

                                        <div class="mb-3">
                                            <label for="username" class="form-label fw-medium">Username <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="<?= set_value('username') ?>" placeholder="Username or full name">
                                            <?= form_error('username', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-medium">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?= set_value('email') ?>" placeholder="email@example.com">
                                            <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label fw-medium">Password <span
                                                    class="text-danger">*</span></label>
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
                                <?php endif; ?>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('pic?client_id=' . $client->id) ?>"
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

<?php if ($account): ?>
    <!-- delete account confirmation modal -->
    <div class="modal fade" id="unlinkModal" tabindex="-1" aria-labelledby="unlinkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="unlinkModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        Confirm Delete Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the login account for <strong
                        class="text-dark"><?= htmlspecialchars(ucwords($pic->name)) ?></strong>?
                    They will no longer be able to log in to the system. This action cannot be undone.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="<?= base_url('pic/edit/' . $pic->id) ?>" method="POST">
                        <input type="hidden" name="action" value="unlink">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    const createAccountCheck = document.getElementById('create_account');
    const accountFields = document.getElementById('account_fields');

    function toggleAccountFields() {
        if (createAccountCheck && accountFields) {
            if (createAccountCheck.checked) {
                accountFields.classList.remove('d-none');
            } else {
                accountFields.classList.add('d-none');
            }
        }
    }

    if (createAccountCheck) {
        createAccountCheck.addEventListener('change', toggleAccountFields);
        toggleAccountFields(); // Initial call on load
    }

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