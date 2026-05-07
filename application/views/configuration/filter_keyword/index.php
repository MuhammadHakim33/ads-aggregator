<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <!-- content area -->
        <div class="container-fluid py-4">
            <!-- action bar -->
            <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
                <h4 class="mb-0"></h4>
                <a href="<?= base_url('config/filter-keyword/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Keyword
                </a>
            </div>

            <!-- flash alerts -->
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

            <!-- data table card -->
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Platform</th>
                                <th scope="col">Type</th>
                                <th scope="col">Keyword</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($keywords)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No keywords found.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($keywords as $item): ?>
                            <?php
                                $status_class = $item->is_active ? 'success' : 'secondary';
                                $status_text = $item->is_active ? 'Aktif' : 'Nonaktif';
                            ?>
                            <tr>
                                <td class="fw-medium"><?= ucfirst(htmlspecialchars($item->platform)) ?></td>
                                <td><?= ucfirst(htmlspecialchars($item->type)) ?></td>
                                <td><?= htmlspecialchars($item->keyword) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('config/filter-keyword/edit/' . $item->id) ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger ms-1"
                                        title="Delete"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-id="<?= $item->id ?>"
                                        data-keyword="<?= htmlspecialchars($item->keyword) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- pagination -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($keywords) ?> keyword(s)</small>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete keyword <strong id="deleteKeywordName"></strong>?
                This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const id = btn.getAttribute('data-id');
        const keyword = btn.getAttribute('data-keyword');
        document.getElementById('deleteKeywordName').textContent = keyword;
        document.getElementById('deleteForm').action = '<?= base_url('config/filter-keyword/delete/') ?>' + id;
    });
</script>

<?php $this->load->view('templates/footer'); ?>
