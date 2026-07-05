<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">

            <!-- back button -->
            <div class="mb-3">
                <a href="<?= base_url('campaign/detail/' . $campaign->id) ?>" class="btn btn-sm btn-outline-secondary">
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

            <div class="row">
                <div class="col-12 col-xl-10">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Configure Report Metrics</h6>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                Campaign  <?= $campaign->name ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <form action="<?= base_url('campaign/configure_metrics/' . $campaign->id) ?>" method="POST">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                <?php if (empty($platforms)): ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No active platforms are configured in the system.
                                    </div>
                                <?php else: ?>
                                    <div class="row g-4">
                                        <?php foreach ($platforms as $name => $conf): ?>
                                            <?php
                                            $label = $conf['label'] ?? ucfirst($name);
                                            $metrics = $conf['metrics'] ?? [];
                                            if ($name === 'instagram' && isset($conf['reels_metrics'])) {
                                                $metrics = array_merge($metrics, $conf['reels_metrics']);
                                            }
                                            $checked_count = 0;
                                            foreach ($metrics as $m) {
                                                if (isset($current_metrics[$name][$m])) $checked_count++;
                                            }
                                            ?>
                                            <div class="col-12 col-md-6">
                                                <div class="card h-100">
                                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                                        <span class="fw-medium d-flex align-items-center gap-2">
                                                            <i class="bi bi-tag-fill text-primary"></i>
                                                            <?= html_escape($label) ?>
                                                            <?php if (!empty($metrics)): ?>
                                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-normal ms-1">
                                                                    <?= $checked_count ?> / <?= count($metrics) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <?php if (!empty($metrics)): ?>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-primary select-all" data-platform="<?= $name ?>">All</button>
                                                                <button type="button" class="btn btn-outline-secondary deselect-all" data-platform="<?= $name ?>">None</button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-body overflow-y-auto" style="max-height: 280px;">
                                                        <?php if (empty($metrics)): ?>
                                                            <p class="text-muted small fst-italic mb-0">No metrics defined for this platform.</p>
                                                        <?php else: ?>
                                                            <div class="row row-cols-1 g-1">
                                                                <?php foreach ($metrics as $metric): ?>
                                                                    <?php
                                                                    $is_checked = isset($current_metrics[$name][$metric]);
                                                                    $metric_label = ucwords(str_replace('_', ' ', $metric));
                                                                    $input_id = 'check_' . html_escape($name) . '_' . html_escape($metric);
                                                                    ?>
                                                                    <div class="col">
                                                                        <label class="d-flex align-items-start gap-2 rounded px-2 py-1 metric-row <?= $is_checked ? 'bg-primary bg-opacity-10' : '' ?>"
                                                                            for="<?= $input_id ?>">
                                                                            <input class="form-check-input mt-1 flex-shrink-0 metric-checkbox"
                                                                                type="checkbox"
                                                                                name="metrics[<?= html_escape($name) ?>][]"
                                                                                value="<?= html_escape($metric) ?>"
                                                                                id="<?= $input_id ?>"
                                                                                data-platform="<?= html_escape($name) ?>"
                                                                                <?= $is_checked ? 'checked' : '' ?>>
                                                                            <span class="d-flex flex-column">
                                                                                <span class="text-dark small"><?= html_escape($metric_label) ?></span>
                                                                                <span class="text-muted font-monospace" style="font-size: .7rem;"><?= html_escape($metric) ?></span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                                    <a href="<?= base_url('campaign/detail/' . $campaign->id) ?>" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-check-circle me-1"></i> Save Configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<style>
    .metric-row {
        cursor: pointer;
        transition: background-color .15s;
    }
    .metric-row:hover {
        background-color: rgba(var(--bs-primary-rgb), .06);
    }
    .metric-row.bg-primary.bg-opacity-10:hover {
        background-color: rgba(var(--bs-primary-rgb), .15) !important;
    }
    .overflow-y-auto {
        overflow-y: auto;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        function updateRowHighlight(checkbox) {
            const row = checkbox.closest('.metric-row');
            if (!row) return;
            if (checkbox.checked) {
                row.classList.add('bg-primary', 'bg-opacity-10');
            } else {
                row.classList.remove('bg-primary', 'bg-opacity-10');
            }
        }

        function updateCounter(platform) {
            const checkboxes = document.querySelectorAll(`.metric-checkbox[data-platform="${platform}"]`);
            const total = checkboxes.length;
            const checked = Array.from(checkboxes).filter(c => c.checked).length;
            const badge = document.querySelector(`.platform-counter[data-platform="${platform}"]`);
            if (badge) badge.textContent = checked + ' / ' + total;
        }

        // Select All
        document.querySelectorAll('.select-all').forEach(btn => {
            btn.addEventListener('click', function () {
                const platform = this.getAttribute('data-platform');
                document.querySelectorAll(`.metric-checkbox[data-platform="${platform}"]`).forEach(chk => {
                    chk.checked = true;
                    updateRowHighlight(chk);
                });
                updateCounter(platform);
            });
        });

        // Deselect All
        document.querySelectorAll('.deselect-all').forEach(btn => {
            btn.addEventListener('click', function () {
                const platform = this.getAttribute('data-platform');
                document.querySelectorAll(`.metric-checkbox[data-platform="${platform}"]`).forEach(chk => {
                    chk.checked = false;
                    updateRowHighlight(chk);
                });
                updateCounter(platform);
            });
        });

        // Per-checkbox change
        document.querySelectorAll('.metric-checkbox').forEach(chk => {
            chk.addEventListener('change', function () {
                updateRowHighlight(this);
                updateCounter(this.getAttribute('data-platform'));
            });
        });
    });
</script>

<?php $this->load->view('templates/footer'); ?>
