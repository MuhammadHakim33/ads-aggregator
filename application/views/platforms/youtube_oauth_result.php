<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm">

                <?php if ($status === 'success'): ?>
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10"
                                style="width:72px;height:72px;">
                                <i class="bi bi-check-lg text-success" style="font-size:2rem;"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-2">Authentication Successful</h5>
                        <p class="text-muted small mb-4"><?= htmlspecialchars($message) ?></p>
                        <p class="text-muted small mb-4">
                            Your access token and refresh token have been saved to the database.
                            The system is now ready to sync with YouTube.
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= site_url('config/platforms/youtube') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-gear me-1"></i> Back to Platform Config
                            </a>
                            <a href="<?= site_url('dashboard') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-house me-1"></i> Dashboard
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                                style="width:72px;height:72px;">
                                <i class="bi bi-exclamation-triangle text-danger" style="font-size:2rem;"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-2">Authentication Failed</h5>
                        <p class="text-muted small mb-4"><?= htmlspecialchars($message) ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= site_url('config/platforms/youtube') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-gear me-1"></i> Platform Config
                            </a>
                            <a href="<?= site_url('youtube_oauth/login') ?>" class="btn btn-danger btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>