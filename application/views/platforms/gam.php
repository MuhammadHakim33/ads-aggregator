<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- flash messages -->
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

            <div class="row g-4">
                <!-- api credential -->
                <div class="col-12 col-xl-5">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center gap-2">
                            <strong>API Credential</strong>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">Required JSON format:</p>
                            <pre class="bg-light rounded p-2 small mb-3">
{
    "network_code": "123456789",
    "service_account": {
        "type": "service_account",
        "project_id": "...",
        "private_key_id": "...",
        "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
        "client_email": "..."
    }
}
</pre>
                            <div class="mb-3">
                                <a class="small text-decoration-none text-muted d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="collapse" href="#gamCredentialGuide" role="button"
                                    aria-expanded="false" aria-controls="gamCredentialGuide">
                                    <i class="bi bi-question-circle"></i> How to get these credentials?
                                </a>
                                <div class="collapse mt-2" id="gamCredentialGuide">
                                    <div class="border rounded p-2 bg-light">
                                        <p class="small fw-medium mb-1">Google Ad Manager & Google Cloud Console</p>
                                        <table class="table table-sm table-borderless mb-0 small">
                                            <tbody>
                                                <tr class="align-top border-bottom">
                                                    <td class="text-nowrap pe-3 py-2"><code>network_code</code></td>
                                                    <td class="text-muted py-2">Log in to <strong>Google Ad
                                                            Manager</strong>. In the left menu, go to
                                                        <strong>Admin</strong> → <strong>Global settings</strong> →
                                                        <strong>Network settings</strong>. You will find your numeric
                                                        Network code on this page. You can also find it in the URL when
                                                        logged in (e.g.,
                                                        <code>admanager.google.com/[network_code]/...</code>).
                                                    </td>
                                                </tr>
                                                <tr class="align-top">
                                                    <td class="text-nowrap pe-3 py-2"><code>service_account</code></td>
                                                    <td class="text-muted py-2">
                                                        <strong>1. Create Service Account:</strong> Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a> → <strong>IAM &amp; Admin</strong> → <strong>Service Accounts</strong>. Click <strong>+ Create Service Account</strong>, fill in the details, and click Done. Click your new service account, go to the <strong>Keys</strong> tab, click <strong>Add Key</strong> → <strong>Create new key</strong> (JSON).<br>
                                                        <strong>2. Grant Access:</strong> Go back to Google Ad Manager → <strong>Admin</strong> → <strong>Global settings</strong> → <strong>API access</strong>. Click <strong>Add a service account user</strong> and provide the email address of your new service account with an appropriate role.<br>
                                                        <strong>3. Paste JSON:</strong> Open the downloaded JSON file and paste the entire content into this form.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="<?= base_url('config/platforms/save-credential/gam') ?>">
                                <div class="mb-3">
                                    <label for="gam_credential_json" class="form-label fw-medium">JSON
                                        Credential</label>
                                    <textarea id="gam_credential_json" name="credential_json"
                                        class="form-control font-monospace"
                                        rows="9"><?= $credential ? htmlspecialchars($credential) : '' ?></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-check-lg me-1"></i> Save
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

<?php $this->load->view('templates/footer'); ?>