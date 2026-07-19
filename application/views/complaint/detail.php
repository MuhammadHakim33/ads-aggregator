<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- back button -->
            <div class="mb-3 d-flex align-items-center gap-2">
                <a href="<?= base_url('complaint') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
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

            <div class="row g-4">
                <!-- main content -->
                <div class="col-12 col-lg-8">
                    <!-- complaint description card -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= htmlspecialchars($complaint->subject) ?></h6>
                            <?php
                            $badge_color = 'secondary';
                            if ($complaint->status === 'waiting')
                                $badge_color = 'warning';
                            elseif ($complaint->status === 'in_progress')
                                $badge_color = 'primary';
                            elseif ($complaint->status === 'resolved')
                                $badge_color = 'success';
                            elseif ($complaint->status === 'closed')
                                $badge_color = 'dark';
                            ?>
                            <span
                                class="badge text-bg-<?= $badge_color ?>"><?= ucwords(str_replace('_', ' ', $complaint->status)) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="mb-2 small text-uppercase">
                                Description
                            </div>
                            <p class="mb-0 text-dark">
                                <?= htmlspecialchars($complaint->description) ?>
                            </p>
                            <hr class="my-3">
                            <div class="text-muted small">
                                Submitted on <?= date('d M Y H:i', strtotime($complaint->created_at)) ?> by
                                <strong><?= htmlspecialchars($complaint->client_name) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- resolution section -->
                    <?php if ($complaint->resolution_note || $current_account['role'] === 'ae' || $current_account['role'] === 'manajemen'): ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Actions</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($current_account['role'] === 'ae' || $current_account['role'] === 'manajemen'): ?>
                                    <form action="<?= base_url('complaint/update_status/' . $complaint->id) ?>" method="POST">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-medium">Update Status</label>
                                            <select name="status" id="status" class="form-select" required>
                                                <option value="waiting" <?= $complaint->status === 'waiting' ? 'selected' : '' ?>>
                                                    Waiting</option>
                                                <option value="in_progress" <?= $complaint->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="resolved" <?= $complaint->status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                <option value="closed" <?= $complaint->status === 'closed' ? 'selected' : '' ?>>
                                                    Closed</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="resolution_note" class="form-label fw-medium">Resolution Note</label>
                                            <textarea name="resolution_note" id="resolution_note" rows="5"
                                                class="form-control"><?= htmlspecialchars($complaint->resolution_note ?? '') ?></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-lg me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <!-- display note for client -->
                                    <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                                        <div class="fw-semibold text-success mb-2">Resolution Note:</div>
                                        <p class="mb-0 text-muted">
                                            <?= htmlspecialchars($complaint->resolution_note) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ad info sidebar -->
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Ad Content Info</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase">Ad Title</div>
                                <div class="mt-1 fw-semibold"><?= htmlspecialchars($complaint->ad_title) ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase">Platform</div>
                                <div class="mt-1">
                                    <?= ucfirst($complaint->platform) ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase">Content ID</div>
                                <div class="mt-1">
                                    <code
                                        class="small text-break bg-light px-2 py-1 rounded"><?= htmlspecialchars($complaint->content_identifier) ?></code>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="mb-0">
                                <div class="text-muted small text-uppercase">Campaign</div>
                                <div class="mt-1">
                                    <a href="<?= base_url('campaign/detail/' . $complaint->campaign_id) ?>"
                                        class="text-decoration-none">
                                        <?= htmlspecialchars($complaint->campaign_name) ?>
                                        <i class="bi bi-arrow-right-short"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>