<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- back button -->
                <div class="mb-3">
                    <a href="<?= base_url('product') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
                <!-- form card -->
                <div class="col-12 col-lg-7">
                    <!-- alert message -->
                    <?php if ($this->session->flashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div><?= $this->session->flashdata('errors') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Product Information</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('product/edit/' . $product->id) ?>" method="POST">

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        Product Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name', $product->name) ?>">
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label fw-medium">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="" disabled>Select Category</option>
                                        <option value="content_marketing" <?= set_select('category', 'content_marketing', $product->category === 'content_marketing') ?>>Content Marketing</option>
                                        <option value="banner_ads" <?= set_select('category', 'banner_ads', $product->category === 'banner_ads') ?>>Banner Ads</option>
                                        <option value="social_media" <?= set_select('category', 'social_media', $product->category === 'social_media') ?>>Social Media</option>
                                    </select>
                                    <?= form_error('category', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price_model" class="form-label fw-medium">
                                            Price Model <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="price_model" name="price_model">
                                            <option value="" disabled>Select Model</option>
                                            <option value="fixed" <?= set_select('price_model', 'fixed', $product->price_model === 'fixed') ?>>Fixed</option>
                                            <option value="cpm" <?= set_select('price_model', 'cpm', $product->price_model === 'cpm') ?>>CPM</option>
                                        </select>
                                        <?= form_error('price_model', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label fw-medium">
                                            Price <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-secondary">Rp</span>
                                            <input type="number" step="1" min="1" class="form-control" id="price"
                                                name="price" value="<?= set_value('price', $product->price) ?>">
                                        </div>
                                        <div class="form-text text-muted" id="price-hint"></div>
                                        <?= form_error('price', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="is_active" class="form-label fw-medium">Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?= set_select('is_active', '1', (bool) $product->is_active) ?>>Active</option>
                                        <option value="0" <?= set_select('is_active', '0', !(bool) $product->is_active) ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('product') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- info panel -->
                <!-- <div class="col-12 col-lg-5 mt-3 mt-lg-0">
                    <div class="card border-0 bg-light bg-opacity-75">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-secondary"><i class="bi bi-clock-history me-2"></i>Contract
                                Usage</h6>
                            <?php
                            $usage_count = $this->db
                                ->where('product_id', $product->id)
                                ->count_all_results('contract_items');
                            ?>
                            <p class="small text-muted mb-1">
                                Produk ini digunakan di
                                <strong class="text-dark"><?= $usage_count ?></strong>
                                item kontrak.
                            </p>
                            <?php if ($usage_count > 0): ?>
                                <div class="alert alert-warning py-2 px-3 mb-0 small">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Produk yang sudah digunakan di kontrak <strong>tidak dapat dihapus</strong>.
                                    Gunakan status <em>Inactive</em> untuk menyembunyikannya dari pilihan baru.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card border-0 bg-light bg-opacity-75 mt-3">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-secondary"><i class="bi bi-info-circle me-2"></i>Informasi
                                Produk Saat Ini</h6>
                            <table class="table table-borderless table-sm mb-0 small text-muted">
                                <tr>
                                    <td style="width: 120px;">ID</td>
                                    <td class="fw-medium text-dark font-monospace">#<?= $product->id ?></td>
                                </tr>
                                <tr>
                                    <td>Category</td>
                                    <td class="fw-medium text-dark">
                                        <?= ucwords(str_replace('_', ' ', $product->category)) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Price Model</td>
                                    <td class="fw-medium text-dark"><?= strtoupper($product->price_model) ?></td>
                                </tr>
                                <tr>
                                    <td>Current Price</td>
                                    <td class="fw-medium text-dark font-monospace">Rp
                                        <?= number_format($product->price, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </table>
                            <div class="form-text mt-2 text-warning-emphasis">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Perubahan harga tidak mempengaruhi kontrak yang sudah ada (harga sudah disimpan sebagai
                                snapshot).
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </main>
</div>
<!-- 
<script>
    const priceModelSelect = document.getElementById('price_model');
    const priceHint = document.getElementById('price-hint');

    function updatePriceHint() {
        const model = priceModelSelect.value;
        if (model === 'cpm') {
            priceHint.textContent = 'Isi harga per 1.000 impresi (tayangan).';
        } else if (model === 'fixed') {
            priceHint.textContent = 'Isi harga per unit/posting.';
        } else {
            priceHint.textContent = '';
        }
    }

    priceModelSelect.addEventListener('change', updatePriceHint);
    updatePriceHint();
</script> -->

<?php $this->load->view('templates/footer'); ?>