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
                <form method="GET" action="<?= current_url() ?>" class="d-flex gap-2 align-items-center mb-0">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" <?= (isset($filters['status']) && $filters['status'] === '1') ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (isset($filters['status']) && $filters['status'] === '0') ? 'selected' : '' ?>>Inactive</option>
                    </select>

                    <select name="role_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r->id ?>" <?= (isset($filters['role_id']) && $filters['role_id'] == $r->id) ? 'selected' : '' ?>><?= htmlspecialchars(ucwords($r->name)) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search accounts..." value="<?= html_escape($filters['q'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    
                    <?php if(!empty($filters['q']) || (isset($filters['status']) && $filters['status'] !== '') || !empty($filters['role_id'])): ?>
                        <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </form>

                <a href="<?= base_url('account/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Account
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
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <?php
                                // status badge
                                $status_class = $account->is_active ? 'success' : 'secondary';
                                ?>
                                <tr>
                                    <td class="fw-medium"><?= ucwords($account->name) ?></td>
                                    <td><?= $account->email ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            <?= ucwords($account->role_name) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-<?= $status_class ?>">
                                            <?= $account->is_active ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('account/edit/' . $account->id) ?>"
                                            class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ((int) $account->id !== (int) $this->session->userdata('id')): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="<?= $account->id ?>" data-name="<?= $account->name ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" disabled
                                                title="Cannot delete own account">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- pagination -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($accounts) ?> account</small>
                    <!-- <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#"><i
                                        class="bi bi-chevron-left"></i></a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav> -->
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
                Are you sure you want to delete account <strong id="deleteAccountName"></strong>?
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
        document.getElementById('deleteAccountName').textContent = name;
        document.getElementById('deleteForm').action = '<?= base_url('account/delete/') ?>' + id;
    });
</script>

<?php $this->load->view('templates/footer'); ?>