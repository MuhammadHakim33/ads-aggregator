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
        <h1 class="fs-4 fw-bold mb-1">Forgot Password</h1>
        <p class="text-muted text-center">Enter your email address and we'll send you a link to reset your password.</p>
    </div>
    <!-- form -->
    <div class="card shadow-sm w-100" style="max-width: 400px;">
        <div class="card-body p-4">
            <!-- flashdata alert -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('auth/send_reset_link') ?>" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                    <?= form_error('email', '<div class="form-text text-danger">', '</div>'); ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="<?= base_url('auth') ?>" class="text-decoration-none small">Back to Login</a>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>