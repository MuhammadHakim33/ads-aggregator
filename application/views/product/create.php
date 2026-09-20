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
                            <form action="<?= base_url('product/create') ?>" method="POST">

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        Product Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= set_value('name') ?>">
                                    <?= form_error('name', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label fw-medium">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="content_marketing" <?= set_select('category', 'content_marketing') ?>>Content Marketing</option>
                                        <option value="banner_ads" <?= set_select('category', 'banner_ads') ?>>Banner Ads
                                        </option>
                                        <option value="social_media" <?= set_select('category', 'social_media') ?>>Social
                                            Media</option>
                                    </select>
                                    <?= form_error('category', '<div class="form-text text-danger">', '</div>'); ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price_model" class="form-label fw-medium">
                                            Price Model <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="price_model" name="price_model">
                                            <option value="" disabled selected>Select Model</option>
                                            <option value="fixed" <?= set_select('price_model', 'fixed') ?>>Fixed
                                                (unit/post)</option>
                                            <option value="cpm" <?= set_select('price_model', 'cpm') ?>>CPM</option>
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
                                                name="price" placeholder="e.g. 50000" value="<?= set_value('price') ?>">
                                        </div>
                                        <div class="form-text text-muted" id="price-hint"></div>
                                        <?= form_error('price', '<div class="form-text text-danger">', '</div>'); ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('product') ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Create Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- helper card -->
                <!-- <div class="col-12 col-lg-5 mt-3 mt-lg-0">
                    <div class="card border-0 bg-light bg-opacity-75">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-secondary"><i class="bi bi-info-circle me-2"></i>Panduan
                                Pengisian</h6>
                            <ul class="small text-muted mb-0 ps-3">
                                <li class="mb-2"><strong>Category</strong>: Pilih kategori iklan yang sesuai dengan
                                    produk ini.</li>
                                <li class="mb-2"><strong>Price Model - Fixed</strong>: Harga tetap per unit/posting.
                                    Contoh: Instagram Feed Post = Rp 5.000.000 per posting.</li>
                                <li class="mb-2"><strong>Price Model - CPM</strong>: Harga per 1.000 impresi (tayang).
                                    Contoh: Billboard Desktop = Rp 40.000 per 1.000 impresi. Jika klien membeli 50.000
                                    tayangan, total = 50 × Rp 40.000 = Rp 2.000.000.</li>
                                <li><strong>Description</strong>: Catatan opsional tentang spesifikasi produk.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card border-0 bg-primary bg-opacity-10 mt-3">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-primary"><i class="bi bi-tags me-2"></i>Rate Card Referensi
                            </h6>
                            <div class="small text-muted">
                                <div class="fw-medium text-dark mb-1">Content Marketing</div>
                                <div class="mb-2 ps-2">Content Partnership - Khas: <span
                                        class="font-monospace fw-medium text-dark">Rp 15.000.000</span></div>

                                <div class="fw-medium text-dark mb-1">Banner Ads (CPM)</div>
                                <div class="ps-2">
                                    <div>Masthead Desktop: <span class="font-monospace fw-medium text-dark">Rp
                                            50.000</span></div>
                                    <div>Billboard Desktop: <span class="font-monospace fw-medium text-dark">Rp
                                            40.000</span></div>
                                    <div class="mb-2">Masthead Mobile: <span
                                            class="font-monospace fw-medium text-dark">Rp 45.000</span></div>
                                </div>

                                <div class="fw-medium text-dark mb-1">Social Media</div>
                                <div class="ps-2">
                                    <div>Instagram Feed Post: <span class="font-monospace fw-medium text-dark">Rp
                                            5.000.000</span></div>
                                    <div>YouTube Video Integration: <span class="font-monospace fw-medium text-dark">Rp
                                            12.500.000</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </main>
</div>

<!-- <script>
    const priceModelSelect = document.getElementById('price_model');
    const priceHint = document.getElementById('price-hint');

    function updatePriceHint() {
        const model = priceModelSelect.value;
        if (model === 'cpm') {
            priceHint.textContent = 'Isi harga per 1.000 impresi (tayangan). Contoh: 50000 untuk Rp 50.000 / CPM.';
        } else if (model === 'fixed') {
            priceHint.textContent = 'Isi harga per unit/posting. Contoh: 5000000 untuk Rp 5.000.000 per posting.';
        } else {
            priceHint.textContent = '';
        }
    }

    priceModelSelect.addEventListener('change', updatePriceHint);
    updatePriceHint();
</script> -->

<?php $this->load->view('templates/footer'); ?>