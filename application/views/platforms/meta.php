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
    "system_user_token": "EAAB...",
    "fb_page_id": "1234567890",
    "ig_account_id": "9876543210"
}
</pre>
                            <div class="mb-3">
                                <a class="small text-decoration-none text-muted d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="collapse" href="#metaCredentialGuide" role="button"
                                    aria-expanded="false" aria-controls="metaCredentialGuide">
                                    <i class="bi bi-question-circle"></i> How to get these credentials?
                                </a>
                                <div class="collapse mt-2" id="metaCredentialGuide">
                                    <div class="border rounded p-2 bg-light">
                                        <p class="small fw-medium mb-1">Meta Business Suite</p>
                                        <table class="table table-sm table-borderless mb-0 small">
                                            <tbody>
                                                <tr class="align-top border-bottom">
                                                    <td class="text-nowrap pe-3 py-2"><code>system_user_token</code>
                                                    </td>
                                                    <td class="text-muted py-2">
                                                        <strong>1. Create System User:</strong> Go to <a
                                                            href="https://business.facebook.com/settings"
                                                            target="_blank">Meta Business Settings</a> →
                                                        <strong>Users</strong> → <strong>System Users</strong>. Click
                                                        <strong>Add</strong>, give it a name, and set the role to
                                                        <strong>Admin</strong>.<br>
                                                        <strong>2. Assign Assets:</strong> Select the user, click
                                                        <strong>Add Assets</strong>. First, select <strong>Apps</strong>
                                                        and enable Manage App (Full Control). Then, click Add Assets
                                                        again, select <strong>Pages</strong>, and choose the Facebook
                                                        Page linked to your Instagram.<br>
                                                        <strong>3. Generate Token:</strong> Click <strong>Generate New
                                                            Token</strong>. Select your App and enable these
                                                        permissions: <code>instagram_basic</code>,
                                                        <code>pages_show_list</code>, and
                                                        <code>pages_read_engagement</code>. Click Generate Token and
                                                        copy the result immediately.
                                                    </td>
                                                </tr>
                                                <tr class="align-top border-bottom">
                                                    <td class="text-nowrap pe-3 py-2"><code>fb_page_id</code></td>
                                                    <td class="text-muted py-2">
                                                        Open <strong>Meta Business Suite</strong> on your desktop web
                                                        browser. Select your business portfolio from the top-left menu.
                                                        Click on the <strong>Settings</strong> gear icon in the
                                                        bottom-left menu. Go to <strong>Business
                                                            assets</strong></strong>. Click on your specific Facebook
                                                        Page and a summary tab will appear on the right showing your
                                                        numeric Facebook Page ID.
                                                    </td>
                                                </tr>
                                                <tr class="align-top">
                                                    <td class="text-nowrap pe-3 py-2"><code>ig_account_id</code></td>
                                                    <td class="text-muted py-2">
                                                        Open <strong>Meta Business Suite</strong> on your desktop web
                                                        browser. Select your business portfolio from the top-left menu.
                                                        Click on the <strong>Settings</strong> gear icon in the
                                                        bottom-left menu. Go to <strong>Business assets</strong>. Click
                                                        on your
                                                        specific Instagram account and a summary tab will appear on the
                                                        right showing your numeric Instagram Account ID.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="<?= base_url('config/platforms/save-credential/meta') ?>">
                                <div class="mb-3">
                                    <label for="meta_credential_json" class="form-label fw-medium">Credential
                                        JSON</label>
                                    <textarea id="meta_credential_json" name="credential_json"
                                        class="form-control font-monospace"
                                        rows="6"><?= $credential ? htmlspecialchars($credential) : '' ?></textarea>
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
                                        <th>Keyword</th>
                                        <th>Status</th>
                                        <th class="text-end" style="width:90px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($keywords)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
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
                                                <td><?= htmlspecialchars($item->keyword) ?></td>
                                                <td><span class="badge text-bg-<?= $status_class ?>"><?= $status_label ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-kw"
                                                        data-id="<?= $item->id ?>"
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
                            <small class="text-muted">Showing <?= count($keywords) ?> keyword</small>
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
            <form method="POST" action="<?= base_url('config/platforms/meta/keyword/create') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_keyword" class="form-label fw-medium">
                            Keyword <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="create_keyword" name="keyword" minlength="2"
                            maxlength="255">
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
                    <div class="mb-3">
                        <label for="edit_keyword" class="form-label fw-medium">
                            Keyword <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_keyword" name="keyword" minlength="2"
                            maxlength="255">
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
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-lg me-1"></i> Edit
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
    const editBase = '<?= base_url('config/platforms/meta/keyword/edit/') ?>';
    const deleteBase = '<?= base_url('config/platforms/meta/keyword/delete/') ?>';

    document.querySelectorAll('.btn-edit-kw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editKeywordForm').action = editBase + this.dataset.id;
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