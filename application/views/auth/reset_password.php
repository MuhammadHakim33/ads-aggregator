<?php $this->load->view('templates/header'); ?>

<div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
    <!-- header -->
    <div class="d-flex flex-column align-items-center mb-4">
        <div class="bg-primary rounded-3 p-2 mb-3">
            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="10" width="3" height="10" rx="1" fill="white" />
                <rect x="10" y="4" width="3" height="16" rx="1" fill="white" />
                <rect x="16" y="8" width="3" height="12" rx="1" fill="white" />
            </svg>
        </div>
        <h1 class="fs-4 fw-bold mb-1">Set New Password</h1>
        <p class="text-muted text-center">Please enter your new password below.</p>
    </div>
    <!-- form -->
    <div class="card shadow-sm w-100" style="max-width: 400px;">
        <div class="card-body p-4">
            <form action="<?= base_url('auth/update_password') ?>" method="POST">
                <input type="hidden" name="token" value="<?= $token ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                    <?= form_error('password', '<div class="form-text text-danger">', '</div>'); ?>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirm" class="form-control" id="password_confirm" required>
                    <?= form_error('password_confirm', '<div class="form-text text-danger">', '</div>'); ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>