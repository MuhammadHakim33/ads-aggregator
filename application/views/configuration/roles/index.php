<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <!-- content area -->
        <div class="container-fluid py-4">
            <!-- action bar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 pb-2 mb-3">
                <div class="mb-0"></div>
                <a href="<?= base_url('config/role/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Role
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
                                <th scope="col" style="width: 10%;">ID</th>
                                <th scope="col">Role Name</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($roles)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No roles found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?= $role->id ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($role->name) ?></td>
                                        <td class="text-end">
                                            <a href="<?= base_url('config/role/edit/' . $role->id) ?>"
                                                class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $role->id ?>"
                                                data-name="<?= htmlspecialchars($role->name) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- footer status -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($roles) ?> role(s)</small>
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
                Are you sure you want to delete role <strong id="deleteRoleName"></strong>?
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
        const name = btn.getAttribute('data-name');
        document.getElementById('deleteRoleName').textContent = name;
        document.getElementById('deleteForm').action = '<?= base_url('config/role/delete/') ?>' + id;
    });
</script>

<?php $this->load->view('templates/footer'); ?>