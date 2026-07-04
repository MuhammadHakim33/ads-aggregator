<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="mb-3 d-flex align-items-center gap-2">
                <a href="<?= base_url('campaign/detail/' . $campaign->id) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Detail
                </a>
            </div>

            <div class="row">
                <div class="col-12 col-xl-10">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">Configure Report Metrics</h5>
                                    <p class="mb-0 text-muted small">Select the metrics you want to display on the client dashboard and report exports for <strong><?= html_escape($campaign->name) ?></strong>.</p>
                                </div>
                                <span class="badge bg-secondary px-3 py-2">Campaign ID: #<?= $campaign->id ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('campaign/configure_metrics/' . $campaign->id) ?>" method="POST">
                                <!-- CSRF Token -->
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                <?php if (empty($platforms)): ?>
                                    <div class="alert alert-warning mb-0">
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
                                            ?>
                                            <div class="col-12 col-md-6">
                                                <div class="card h-100 border border-light-subtle shadow-xs">
                                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                                                        <span class="fw-semibold text-dark">
                                                            <i class="bi bi-tag-fill text-primary me-2"></i><?= html_escape($label) ?>
                                                        </span>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" class="btn btn-xs btn-outline-primary select-all" data-platform="<?= $name ?>">All</button>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary deselect-all" data-platform="<?= $name ?>">None</button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body py-3 px-3" style="max-height: 280px; overflow-y: auto;">
                                                        <?php if (empty($metrics)): ?>
                                                            <span class="text-muted small italic">No metrics defined for this platform.</span>
                                                        <?php else: ?>
                                                            <div class="row row-cols-1 g-2">
                                                                <?php foreach ($metrics as $metric): ?>
                                                                    <?php
                                                                    $is_checked = isset($current_metrics[$name][$metric]);
                                                                    $metric_label = ucwords(str_replace('_', ' ', $metric));
                                                                    ?>
                                                                    <div class="col">
                                                                        <div class="form-check form-check-inline m-0">
                                                                            <input class="form-check-input metric-checkbox" type="checkbox" 
                                                                                   name="metrics[<?= html_escape($name) ?>][]" 
                                                                                   value="<?= html_escape($metric) ?>" 
                                                                                   id="check_<?= html_escape($name) ?>_<?= html_escape($metric) ?>"
                                                                                   data-platform="<?= html_escape($name) ?>"
                                                                                   <?= $is_checked ? 'checked' : '' ?>>
                                                                            <label class="form-check-label text-secondary small" for="check_<?= html_escape($name) ?>_<?= html_escape($metric) ?>">
                                                                                <?= html_escape($metric_label) ?>
                                                                                <span class="text-muted font-monospace d-block" style="font-size: 0.65rem;">(<?= html_escape($metric) ?>)</span>
                                                                            </label>
                                                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    document.querySelectorAll('.select-all').forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.getAttribute('data-platform');
            document.querySelectorAll(`.metric-checkbox[data-platform="${platform}"]`).forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    });

    // Deselect All functionality
    document.querySelectorAll('.deselect-all').forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.getAttribute('data-platform');
            document.querySelectorAll(`.metric-checkbox[data-platform="${platform}"]`).forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
});
</script>

<?php $this->load->view('templates/footer'); ?>
