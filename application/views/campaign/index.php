<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- action bar -->
            <div class="d-flex justify-content-between align-items-center pb-2 mb-3">
                <div></div>
                <a href="<?= base_url('campaign/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Campaign
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
                                <th scope="col">Campaign Name</th>
                                <th scope="col">Contract Number</th>
                                <th scope="col">Client</th>
                                <th scope="col">Start Date</th>
                                <th scope="col">End Date</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td class="fw-medium">
                                        <?= ucwords($campaign->name) ?>
                                        <?php if ($campaign->description): ?>
                                            <div class="text-muted mt-1 fw-normal" style="font-size: 0.8rem;">
                                                <?= $campaign->description ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $campaign->contract_number ?></td>
                                    <td><?= ucwords($campaign->client_name) ?></td>
                                    <td><?= date('Y-m-d', strtotime($campaign->start_date)) ?></td>
                                    <td><?= date('Y-m-d', strtotime($campaign->end_date)) ?></td>
                                    <td class="text-end">
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                id="exportDropdown-<?= $campaign->id ?>" data-bs-toggle="dropdown"
                                                aria-expanded="false" title="Export">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="exportDropdown-<?= $campaign->id ?>">
                                                <li>
                                                    <a class="dropdown-item text-start"
                                                        href="<?= base_url('campaign/export/pdf/' . $campaign->id) ?>"
                                                        target="_blank">
                                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                        Export PDF
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-start"
                                                        href="<?= base_url('campaign/export/excel/' . $campaign->id) ?>">
                                                        <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>
                                                        Export Excel
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a href="<?= base_url('campaign/edit/' . $campaign->id) ?>"
                                            class="btn btn-sm btn-outline-secondary ms-1" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="<?= $campaign->id ?>" data-name="<?= $campaign->name ?>"
                                            data-client="<?= $campaign->client_name ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($campaigns)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No campaigns found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($campaigns) ?> campaign(s)</small>
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
                Are you sure you want to delete campaign <strong id="deleteCampaignName" class="text-danger"></strong>
                for <strong id="deleteClientName"></strong>?
                This action will perform a soft delete.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('deleteCampaignName').textContent = btn.getAttribute('data-name');
        document.getElementById('deleteClientName').textContent = btn.getAttribute('data-client');
        document.getElementById('deleteForm').action = '<?= base_url('campaign/delete/') ?>' + btn.getAttribute('data-id');
    });
</script>

<?php $this->load->view('templates/footer'); ?>