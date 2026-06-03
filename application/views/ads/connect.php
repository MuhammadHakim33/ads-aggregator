<?php $this->load->view('templates/header'); ?>

<div class="d-flex">
    <!-- template sidebar -->
    <?php $this->load->view('templates/sidebar'); ?>
    <!-- main content -->
    <main class="col-sm-10 bg-body-tertiary" id="main">
        <!-- template top navbar -->
        <?php $this->load->view('templates/topbar'); ?>
        <div class="container-fluid py-4">
            <!-- page heading -->
            <div class="d-flex justify-content-between align-items-center pb-2 mb-4">
                <div>
                    <h5 class="mb-0 fw-semibold">Connect Ads to Client</h5>
                </div>
                <div>
                    <a href="<?= base_url('ads') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Ads
                    </a>
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
                <form action="<?= base_url('ads/connect') ?>" method="post" id="mappingForm">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 50px;">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </th>
                                    <th scope="col">Fetched</th>
                                    <th scope="col">Identifier/Title</th>
                                    <th scope="col">Platform</th>
                                    <th scope="col" class="text-center">Link</th>
                                    <th scope="col" style="width: 250px;">Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($unconnected)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                            All ads have been connected to clients.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($unconnected as $ad): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input class="form-check-input row-check" type="checkbox" name="selected_ids[]" value="<?= $ad->id ?>">
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= date('d M Y, H:i', strtotime($ad->created_at)) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-medium" >
                                                <?= htmlspecialchars($ad->title ?: '-') ?>
                                            </div>
                                            <small class="font-monospace text-muted"><?= htmlspecialchars($ad->content_identifier) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-light"><?= htmlspecialchars($platform_labels[strtolower($ad->platform)] ?? ucfirst($ad->platform)) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                                $post_url = '#';
                                                $platform = strtolower($ad->platform);
                                                if ($platform === 'facebook') {
                                                    $post_url = 'https://www.facebook.com/' . $ad->content_identifier;
                                                } elseif ($platform === 'instagram') {
                                                    $post_url = 'https://www.instagram.com/p/' . $ad->content_identifier . '/';
                                                } elseif ($platform === 'youtube') {
                                                    $post_url = 'https://www.youtube.com/watch?v=' . $ad->content_identifier;
                                                } elseif ($platform === 'ga4') {
                                                    $post_url = 'https://' . $ad->content_identifier;
                                                }
                                            ?>
                                            <?php if ($post_url !== '#'): ?>
                                                <a href="<?= $post_url ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka postingan asli">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <select name="client_id[<?= $ad->id ?>]" class="form-select form-select-sm client-select" disabled>
                                                <option value="" disabled selected>Select Client</option>
                                                <?php foreach ($clients as $client): ?>
                                                    <option value="<?= $client->id ?>"><?= htmlspecialchars($client->company_name) ?></option>
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
                    <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center border-top-0">
                        <span class="text-muted small"><span id="checkedCount">0</span> rows selected</span>
                        <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmit" disabled>
                            <i class="bi bi-link-45deg"></i> Connect
                        </button>
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
    const btnSubmit = document.getElementById('btnSubmit');

    function updateState() {
        let count = 0;
        rowChecks.forEach(chk => {
            const tr = chk.closest('tr');
            const select = tr.querySelector('.client-select');
            
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
        if (btnSubmit) btnSubmit.disabled = count === 0;
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
