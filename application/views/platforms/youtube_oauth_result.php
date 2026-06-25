<div class="container-fluid">
    <div class="row mt-4">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow">
                <div
                    class="card-header <?= $status === 'success' ? 'bg-success' : 'bg-danger' ?> text-white text-center">
                    <h4 class="mb-0">
                        <?php if ($status === 'success'): ?>
                            <i class="fas fa-check-circle"></i> OAuth Berhasil
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle"></i> OAuth Gagal
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body text-center p-5">
                    <p class="lead mb-4"><?= $message; ?></p>
                    <?php if ($status === 'success'): ?>
                        <p class="text-muted">Token Anda telah berhasil diamankan di dalam database. Sistem sekarang dapat
                            melakukan sinkronisasi dengan YouTube.</p>
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-primary mt-3">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('youtube_oauth/login') ?>" class="btn btn-warning mt-3">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </a>
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary mt-3 ml-2">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>