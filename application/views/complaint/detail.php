<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('complaint') ?>">Complaints</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Complaint Details</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($complaint->subject) ?></h4>
                        <span class="text-muted small">Submitted on <?= date('d M Y H:i', strtotime($complaint->created_at)) ?> by <strong><?= htmlspecialchars($complaint->client_name) ?></strong></span>
                    </div>
                    <div>
                        <?php
                        $badge_color = 'secondary';
                        if ($complaint->status === 'waiting') $badge_color = 'warning';
                        elseif ($complaint->status === 'in_progress') $badge_color = 'primary';
                        elseif ($complaint->status === 'resolved') $badge_color = 'success';
                        elseif ($complaint->status === 'closed') $badge_color = 'dark';
                        ?>
                        <span class="badge fs-6 px-3 py-2 text-bg-<?= $badge_color ?>"><?= ucwords(str_replace('_', ' ', $complaint->status)) ?></span>
                    </div>
                </div>
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
                <div class="col-12 col-lg-8">
                    <!-- Complaint Description -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            <i class="bi bi-chat-left-text me-2 text-primary"></i> Complaint Description
                        </div>
                        <div class="card-body p-4">
                            <p class="mb-0 text-dark" style="white-space: pre-line; line-height: 1.6;">
                                <?= htmlspecialchars($complaint->description) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Resolution Section -->
                    <?php if ($complaint->resolution_note || in_array($current_account['role'], ['ae', 'manajemen'])): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white fw-bold py-3">
                                <i class="bi bi-check-circle me-2 text-success"></i> Resolution Note & Actions
                            </div>
                            <div class="card-body p-4">
                                <?php if (in_array($current_account['role'], ['ae', 'manajemen'])): ?>
                                    <form action="<?= base_url('complaint/update_status/' . $complaint->id) ?>" method="POST">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-semibold">Update Status</label>
                                            <select name="status" id="status" class="form-select" required>
                                                <option value="waiting" <?= $complaint->status === 'waiting' ? 'selected' : '' ?>>Waiting</option>
                                                <option value="in_progress" <?= $complaint->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="resolved" <?= $complaint->status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                <option value="closed" <?= $complaint->status === 'closed' ? 'selected' : '' ?>>Closed</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="resolution_note" class="form-label fw-semibold">Resolution Note</label>
                                            <textarea name="resolution_note" id="resolution_note" rows="5" class="form-control" 
                                                placeholder="Provide resolution summary, actions taken, or instructions for the client..."><?= htmlspecialchars($complaint->resolution_note ?? '') ?></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success px-4">
                                                <i class="bi bi-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <!-- Display Note for Client -->
                                    <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                                        <div class="fw-semibold text-success mb-2">Resolution Note:</div>
                                        <p class="mb-0 text-muted" style="white-space: pre-line;">
                                            <?= htmlspecialchars($complaint->resolution_note) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ad Info Sidebar -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            <i class="bi bi-info-circle me-2 text-info"></i> Ad Content Info
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="small text-muted d-block">Ad Title</label>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($complaint->ad_title) ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted d-block">Platform</label>
                                <span>
                                    <?php
                                    $icon = 'bi-globe';
                                    if ($complaint->platform === 'facebook') $icon = 'bi-facebook text-primary';
                                    elseif ($complaint->platform === 'instagram') $icon = 'bi-instagram text-danger';
                                    elseif ($complaint->platform === 'youtube') $icon = 'bi-youtube text-danger';
                                    elseif ($complaint->platform === 'ga4') $icon = 'bi-bar-chart-line text-success';
                                    ?>
                                    <i class="bi <?= $icon ?> me-1"></i> <?= ucfirst($complaint->platform) ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted d-block">Platform Content ID / URL</label>
                                <code class="small text-break bg-light px-2 py-1 rounded"><?= htmlspecialchars($complaint->content_identifier) ?></code>
                            </div>
                            <div class="mb-0">
                                <label class="small text-muted d-block">Campaign</label>
                                <a href="<?= base_url('campaign/detail/' . $complaint->campaign_id) ?>" class="text-decoration-none fw-medium">
                                    <?= htmlspecialchars($complaint->campaign_name) ?> <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>
