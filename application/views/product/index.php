<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- action bar -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-2 mb-3">
                <form method="GET" action="<?= current_url() ?>" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                    <select name="category" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="content_marketing" <?= (isset($filters['category']) && $filters['category'] === 'content_marketing') ? 'selected' : '' ?>>Content Marketing</option>
                        <option value="banner_ads" <?= (isset($filters['category']) && $filters['category'] === 'banner_ads') ? 'selected' : '' ?>>Banner Ads</option>
                        <option value="social_media" <?= (isset($filters['category']) && $filters['category'] === 'social_media') ? 'selected' : '' ?>>Social Media</option>
                    </select>

                    <select name="is_active" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" <?= (isset($filters['is_active']) && $filters['is_active'] === '1') ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (isset($filters['is_active']) && $filters['is_active'] === '0') ? 'selected' : '' ?>>Inactive</option>
                    </select>

                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0"
                            placeholder="Search products..." value="<?= html_escape($filters['q'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>

                    <?php if (!empty($filters['q']) || (isset($filters['category']) && $filters['category'] !== '') || (isset($filters['is_active']) && $filters['is_active'] !== '')): ?>
                        <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </form>

                <a href="<?= base_url('product/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Product
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
                                <th scope="col">Product Name</th>
                                <th scope="col">Category</th>
                                <th scope="col">Price Model</th>
                                <th scope="col" class="text-end">Price</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $category_labels = [
                                'content_marketing' => ['label' => 'Content Marketing', 'badge' => 'bg-info bg-opacity-10 text-info'],
                                'banner_ads' => ['label' => 'Banner Ads', 'badge' => 'bg-warning bg-opacity-10 text-warning-emphasis'],
                                'social_media' => ['label' => 'Social Media', 'badge' => 'bg-purple bg-opacity-10 text-primary'],
                            ];
                            ?>
                            <?php foreach ($products as $product): ?>
                                <?php $cat = $category_labels[$product->category] ?? ['label' => ucwords(str_replace('_', ' ', $product->category)), 'badge' => 'bg-secondary bg-opacity-10 text-secondary']; ?>
                                <tr>
                                    <td class="fw-medium">
                                        <?= htmlspecialchars($product->name) ?>
                                        <?php if (!empty($product->description)): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($product->description) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $cat['badge'] ?> fw-normal">
                                            <?= $cat['label'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product->price_model === 'cpm'): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-normal">
                                                <i class="bi bi-eye me-1"></i> CPM
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">
                                                <i class="bi bi-tag me-1"></i> Fixed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end font-monospace fw-medium">
                                        Rp <?= number_format($product->price, 0, ',', '.') ?>
                                        <?php if ($product->price_model === 'cpm'): ?>
                                            <span class="text-muted fw-normal small">/ 1.000 impression</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($product->is_active)): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('product/edit/' . $product->id) ?>"
                                            class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="<?= $product->id ?>"
                                            data-name="<?= htmlspecialchars($product->name) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?= count($products) ?>
                        products</small>
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
                Are you sure you want to delete product <strong id="deleteProductName"></strong>?
                This action cannot be undone. If this product is used in existing contracts, deletion will be blocked.
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
        const btn = event.relatedTarget;
        document.getElementById('deleteProductName').textContent = btn.getAttribute('data-name');
        document.getElementById('deleteForm').action = '<?= base_url('product/delete/') ?>' + btn.getAttribute('data-id');
    });
</script>

<?php $this->load->view('templates/footer'); ?>