<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <!-- action bar -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-2 mb-3">
                <form method="GET" action="<?= current_url() ?>" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="waiting" <?= (isset($filters['status']) && $filters['status'] === 'waiting') ? 'selected' : '' ?>>Waiting</option>
                        <option value="in_progress" <?= (isset($filters['status']) && $filters['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="resolved" <?= (isset($filters['status']) && $filters['status'] === 'resolved') ? 'selected' : '' ?>>Resolved</option>
                        <option value="closed" <?= (isset($filters['status']) && $filters['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
                    </select>

                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" 
                            placeholder="Search complaints..." 
                            value="<?= html_escape($filters['q'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>

                    <?php if (!empty($filters['q']) || !empty($filters['status'])): ?>
                        <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </form>

                <?php if ($current_account['role'] === 'client'): ?>
                    <a href="<?= base_url('complaint/create') ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Submit Complaint
                    </a>
                <?php endif; ?>
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
                                <th scope="col">Subject</th>
                                <th scope="col">Ad Content</th>
                                <th scope="col">Campaign</th>
                                <?php if ($current_account['role'] !== 'client'): ?>
                                    <th scope="col">Client</th>
                                <?php endif; ?>
                                <th scope="col" class="text-center" style="width: 120px;">Status</th>
                                <th scope="col" style="width: 150px;">Date Submitted</th>
                                <th scope="col" class="text-end" style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <?php
                                $badge_color = 'secondary';
                                if ($c->status === 'waiting') $badge_color = 'warning';
                                elseif ($c->status === 'in_progress') $badge_color = 'primary';
                                elseif ($c->status === 'resolved') $badge_color = 'success';
                                elseif ($c->status === 'closed') $badge_color = 'dark';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($c->subject) ?></div>
                                        <div class="text-muted mt-1 fw-normal" style="font-size: 0.8rem; max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= htmlspecialchars($c->description) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php
                                            $icon = 'bi-globe';
                                            if ($c->platform === 'facebook') $icon = 'bi-facebook text-primary';
                                            elseif ($c->platform === 'instagram') $icon = 'bi-instagram text-danger';
                                            elseif ($c->platform === 'youtube') $icon = 'bi-youtube text-danger';
                                            elseif ($c->platform === 'ga4') $icon = 'bi-bar-chart-line text-success';
                                            ?>
                                            <i class="bi <?= $icon ?>"></i>
                                            <span><?= htmlspecialchars($c->ad_title) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border"><?= htmlspecialchars($c->campaign_name) ?></span>
                                    </td>
                                    <?php if ($current_account['role'] !== 'client'): ?>
                                        <td>
                                            <span><?= htmlspecialchars($c->client_name) ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <span class="badge text-bg-<?= $badge_color ?>"><?= ucwords(str_replace('_', ' ', $c->status)) ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size: 0.85rem;"><?= date('Y-m-d H:i', strtotime($c->created_at)) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('complaint/detail/' . $c->id) ?>" class="btn btn-sm btn-outline-primary ms-1" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($complaints)): ?>
                                <tr>
                                    <td colspan="<?= ($current_account['role'] !== 'client') ? 7 : 6 ?>" class="text-center text-muted py-4">
                                        No complaints found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php $this->load->view('templates/footer'); ?>
