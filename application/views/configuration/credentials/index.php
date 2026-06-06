<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <!-- template topbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <!-- flash message -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('errors') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- META -->
            <?php
            $meta = $credentials['meta'] ?? null;
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-meta fs-5 text-primary"></i>
                        <strong>Meta (Facebook + Instagram)</strong>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Enter the following JSON format:</p>
                    <pre class="bg-light rounded p-2 small mb-3">
{
    "system_user_token": "EAAB...",
    "fb_page_id": "1234567890",
    "ig_account_id": "9876543210"
}</pre>
                    <form method="POST" action="<?= base_url('config/credentials/save/meta') ?>">
                        <div class="mb-3">
                            <label for="meta_json" class="form-label fw-medium">Credential JSON</label>
                            <textarea id="meta_json" name="credential_json" class="form-control font-monospace" rows="6"
                                placeholder='{"system_user_token": "...", "fb_page_id": "...", "ig_account_id": "..."}'><?= $meta && isset($meta->credential_data) ? htmlspecialchars($meta->credential_data) : '' ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- GA4 -->
            <?php
            $ga4 = $credentials['ga4'] ?? null;
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-line fs-5 text-success"></i>
                        <strong>Google Analytics 4</strong>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Enter the following JSON format:</p>
                    <pre class="bg-light rounded p-2 small mb-3">
{
  "property_id": "123456789",
  "service_account": { ...isi dari file .json Google Cloud... }
}</pre>
                    <form method="POST" action="<?= base_url('config/credentials/save/ga4') ?>">
                        <div class="mb-3">
                            <label for="ga4_json" class="form-label fw-medium">Credential JSON</label>
                            <textarea id="ga4_json" name="credential_json" class="form-control font-monospace" rows="8"
                                placeholder='{"property_id": "...", "service_account": {...}}'><?= $ga4 && isset($ga4->credential_data) ? htmlspecialchars($ga4->credential_data) : '' ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- YOUTUBE -->
            <?php
            $yt = $credentials['youtube'] ?? null;
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-youtube fs-5 text-danger"></i>
                        <strong>YouTube</strong>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Enter the following JSON format:</p>
                    <pre class="bg-light rounded p-2 small mb-3">
{
  "api_key": "AIzaSy...",
  "channel_id": "UCxxx..."
}</pre>
                    <form method="POST" action="<?= base_url('config/credentials/save/youtube') ?>">
                        <div class="mb-3">
                            <label for="yt_json" class="form-label fw-medium">Credential JSON</label>
                            <textarea id="yt_json" name="credential_json" class="form-control font-monospace" rows="5"
                                placeholder='{"api_key": "...", "channel_id": "..."}'><?= $yt && isset($yt->credential_data) ? htmlspecialchars($yt->credential_data) : '' ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Toast notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="testToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" id="testToastHeader">
            <i class="bi bi-plug me-2" id="testToastIcon"></i>
            <strong class="me-auto">Test Koneksi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="testToastBody"></div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>