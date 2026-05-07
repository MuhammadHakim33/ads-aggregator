<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- action bar -->
            <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
                <div></div>
                <a href="<?= base_url('client/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Client
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
                                <th scope="col">Company Name</th>
                                <th scope="col">PIC Name</th>
                                <th scope="col">AE</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                            <?php $status_class = $client->is_active ? 'success' : 'secondary'; ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($client->company_name) ?></td>
                                <td><?= htmlspecialchars($client->pic_name ?? '-') ?></td>
                                <td><?= htmlspecialchars($client->ae_name ?? '') ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $status_class ?>">
                                        <?= $client->is_active ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('client/edit/' . $client->id) ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger ms-1"
                                        title="Delete"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-id="<?= $client->id ?>"
                                        data-name="<?= htmlspecialchars($client->company_name) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No clients found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($clients) ?> client(s)</small>
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
                Are you sure you want to delete <strong id="deleteClientName"></strong>?
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
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
        const btn  = event.relatedTarget;
        document.getElementById('deleteClientName').textContent = btn.getAttribute('data-name');
        document.getElementById('deleteForm').action = '<?= base_url('client/delete/') ?>' + btn.getAttribute('data-id');
    });
</script>

<?php $this->load->view('templates/footer'); ?>
