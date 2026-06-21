<?php $this->load->view('templates/header'); ?>

<div class="d-flex flex-nowrap min-vh-100">
    <?php $this->load->view('templates/sidebar'); ?>
    <main class="flex-grow-1 bg-body-tertiary d-flex flex-column" id="main" style="min-width: 0;">
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-2 mb-4">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                    <form method="GET" action="<?= current_url() ?>"
                        class="d-flex flex-wrap gap-2 align-items-center mb-0">
                        <select name="platform" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">All Platforms</option>
                            <?php foreach ($platform_labels as $key => $label): ?>
                                <option value="<?= $key ?>" <?= (isset($filters['platform']) && $filters['platform'] == $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="input-group input-group-sm" style="width: 200px;">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0"
                                placeholder="Search ads..." value="<?= html_escape($filters['q'] ?? '') ?>">
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary d-none">Filter</button>

                        <?php if (!empty($filters['q']) || !empty($filters['platform'])): ?>
                            <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i
                                    class="bi bi-x-circle"></i></a>
                        <?php endif; ?>
                    </form>
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
            <div class="card shadow-sm border-0">
                <form action="<?= base_url('ads') ?>" method="post" id="mappingForm">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 50px;">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </th>
                                    <th scope="col">Published</th>
                                    <th scope="col">Identifier/Title</th>
                                    <th scope="col">Platform</th>
                                    <th scope="col" class="text-center">Link</th>
                                    <th scope="col" style="width: 250px;">Campaign</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($unconnected)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                            All ads have been connected to campaigns.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($unconnected as $ad): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input class="form-check-input row-check" type="checkbox" name="selected_ids[]"
                                                    value="<?= $ad->id ?>">
                                            </td>
                                            <td>
                                                <?php if (!empty($ad->published_at)): ?>
                                                    <small class="text-dark fw-medium">
                                                        <?= date('d M Y, H:i', strtotime($ad->published_at)) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-medium">
                                                    <?= $ad->title ?: '-' ?>
                                                </div>
                                                <small class="font-monospace text-muted"><?= $ad->content_identifier ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-light">
                                                    <?= $platform_labels[strtolower($ad->platform)] ?? ucfirst($ad->platform) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $post_url = generate_ad_post_url($ad->platform, $ad->content_identifier);
                                                ?>
                                                <?php if ($post_url !== '#'): ?>
                                                    <a href="<?= $post_url ?>" target="_blank"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select name="campaign_id[<?= $ad->id ?>]"
                                                    class="form-select form-select-sm campaign-select" disabled>
                                                    <option value="" disabled selected>Select Campaign</option>
                                                    <?php foreach ($campaigns as $campaign): ?>
                                                        <option value="<?= $campaign->id ?>">
                                                            <?= ucwords($campaign->name) ?>
                                                            (<?= $campaign->contract_number ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($unconnected)): ?>
                        <div
                            class="card-footer bg-white py-3 d-flex justify-content-between align-items-center border-top-0">
                            <span class="text-muted small"><span id="checkedCount">0</span> rows selected</span>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="ignore"
                                    class="btn btn-outline-danger btn-sm px-4 action-btn" disabled formnovalidate
                                    onclick="return confirm('Are you sure you want to ignore and permanently remove these ads?')">
                                    <i class="bi bi-trash"></i> Ignore
                                </button>
                                <button type="submit" name="action" value="connect"
                                    class="btn btn-primary btn-sm px-4 action-btn" disabled>
                                    <i class="bi bi-link-45deg"></i> Connect
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAll');
        const rowChecks = document.querySelectorAll('.row-check');
        const checkedCountEl = document.getElementById('checkedCount');
        const actionBtns = document.querySelectorAll('.action-btn');

        function updateState() {
            let count = 0;
            rowChecks.forEach(chk => {
                const tr = chk.closest('tr');
                const select = tr.querySelector('.campaign-select');

                if (chk.checked) {
                    count++;
                    tr.classList.add('table-primary');
                    select.disabled = false;
                    select.required = true;
                } else {
                    tr.classList.remove('table-primary');
                    select.disabled = true;
                    select.required = false;
                }
            });

            if (checkedCountEl) checkedCountEl.textContent = count;

            actionBtns.forEach(btn => {
                btn.disabled = count === 0;
            });

            if (checkAll) checkAll.checked = (count > 0 && count === rowChecks.length);
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                rowChecks.forEach(chk => chk.checked = checkAll.checked);
                updateState();
            });
        }

        rowChecks.forEach(chk => {
            chk.addEventListener('change', updateState);
        });
    });
</script>

<?php $this->load->view('templates/footer'); ?>