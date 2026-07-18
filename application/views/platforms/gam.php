<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column min-w-0" id="main">
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
                                                        <strong>1. Create Service Account:</strong> Go to <a
                                                            href="https://console.cloud.google.com"
                                                            target="_blank">Google Cloud Console</a> → <strong>IAM &amp;
                                                            Admin</strong> → <strong>Service Accounts</strong>. Click
                                                        <strong>+ Create Service Account</strong>, fill in the details,
                                                        and click Done. Click your new service account, go to the
                                                        <strong>Keys</strong> tab, click <strong>Add Key</strong> →
                                                        <strong>Create new key</strong> (JSON).<br>
                                                        <strong>2. Grant Access:</strong> Go back to Google Ad Manager →
                                                        <strong>Admin</strong> → <strong>Global settings</strong> →
                                                        <strong>API access</strong>. Click <strong>Add a service account
                                                            user</strong> and provide the email address of your new
                                                        service account with an appropriate role.<br>
                                                        <strong>3. Paste JSON:</strong> Open the downloaded JSON file
                                                        and paste the entire content into this form.
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

                <!-- filter keywords -->
                <div class="col-12 col-xl-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <strong>Filter Keywords</strong>
                            </div>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#createKeywordModal">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Keyword</th>
                                        <th>Status</th>
                                        <th class="text-end" style="width:90px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($keywords)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                                Empty
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($keywords as $item): ?>
                                            <?php
                                            $status_class = $item->is_active ? 'success' : 'secondary';
                                            $status_label = $item->is_active ? 'Active' : 'Inactive';
                                            ?>
                                            <tr>
                                                <td><?= ucfirst(htmlspecialchars($item->type)) ?></td>
                                                <td><?= htmlspecialchars($item->keyword) ?></td>
                                                <td><span class="badge text-bg-<?= $status_class ?>"><?= $status_label ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-kw"
                                                        data-id="<?= $item->id ?>"
                                                        data-type="<?= htmlspecialchars($item->type) ?>"
                                                        data-keyword="<?= htmlspecialchars($item->keyword) ?>"
                                                        data-is-active="<?= $item->is_active ?>" data-bs-toggle="modal"
                                                        data-bs-target="#editKeywordModal" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger ms-1 btn-delete-kw"
                                                        data-id="<?= $item->id ?>"
                                                        data-keyword="<?= htmlspecialchars($item->keyword) ?>"
                                                        data-bs-toggle="modal" data-bs-target="#deleteKeywordModal"
                                                        title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">Showing <?= count($keywords) ?> keywords</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- modal create -->
<div class="modal fade" id="createKeywordModal" tabindex="-1" aria-labelledby="createKeywordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="createKeywordModalLabel">Add Filter Keyword</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('config/platforms/gam/keyword/create') ?>">
                <div class="modal-body">
                    <input type="hidden" name="type" value="keyword">
                    <div class="mb-3">
                        <label for="create_keyword" class="form-label fw-medium">
                            Keyword <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="create_keyword" name="keyword" minlength="2"
                            maxlength="255" required placeholder="e.g. promo_banner">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- modal edit -->
<div class="modal fade" id="editKeywordModal" tabindex="-1" aria-labelledby="editKeywordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="editKeywordModalLabel">
                    <i class="bi bi-pencil-square me-2 text-danger"></i>Edit Filter Keyword
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editKeywordForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_type" name="type" value="keyword">
                    <div class="mb-3">
                        <label for="edit_keyword" class="form-label fw-medium">
                            Keyword <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_keyword" name="keyword" minlength="2"
                            maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_is_active" class="form-label fw-medium">Status</label>
                        <select class="form-select" id="edit_is_active" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- modal delete -->
<div class="modal fade" id="deleteKeywordModal" tabindex="-1" aria-labelledby="deleteKeywordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteKeywordModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the keyword <strong id="deleteKeywordName"></strong>?
                This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteKeywordForm" method="POST">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const editBase = '<?= base_url('config/platforms/gam/keyword/edit/') ?>';
    const deleteBase = '<?= base_url('config/platforms/gam/keyword/delete/') ?>';

    document.querySelectorAll('.btn-edit-kw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editKeywordForm').action = editBase + this.dataset.id;
            document.getElementById('edit_type').value = this.dataset.type;
            document.getElementById('edit_keyword').value = this.dataset.keyword;
            document.getElementById('edit_is_active').value = this.dataset.isActive;
        });
    });

    document.querySelectorAll('.btn-delete-kw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('deleteKeywordName').textContent = this.dataset.keyword;
            document.getElementById('deleteKeywordForm').action = deleteBase + this.dataset.id;
        });
    });
</script>

<?php $this->load->view('templates/footer'); ?>