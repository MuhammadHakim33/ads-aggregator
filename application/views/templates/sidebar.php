<aside class="offcanvas-md offcanvas-start border-end bg-white flex-shrink-0" tabindex="-1" id="sidebarCollapse"
    aria-labelledby="sidebarLabel" style="width: 250px; z-index: 1045;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center" id="sidebarLabel">
            <i class="bi bi-bar-chart-fill me-2 fs-4 text-primary"></i>
            <span class="fs-6 fw-semibold">Kontan Ad Reporter</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarCollapse"
            aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <?php
        $role = $current_account['role'] ?? '';
        $brand_url = base_url('dashboard');
        ?>
        <a href="<?= $brand_url ?>"
            class="d-none d-md-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <i class="bi bi-bar-chart-fill me-2 fs-4 text-primary"></i>
            <span class="fs-6 fw-semibold">Kontan Ad Reporter</span>
        </a>
        <hr class="d-none d-md-block">
        <ul class="nav nav-pills flex-column mb-auto w-100">
            <li class="nav-item">
                <a href="<?= base_url('dashboard') ?>"
                    class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'dashboard' ? 'active text-white' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <?php if ($role === 'superadmin' || $role === 'manajemen'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('account') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'account' ? 'active text-white' : '' ?>">
                        <i class="bi bi-people me-2"></i> Account
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'manajemen'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('client') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'client' ? 'active text-white' : '' ?>">
                        <i class="bi bi-person-check me-2"></i> Client
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'manajemen' || $role === 'client'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('contract') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'contract' ? 'active text-white' : '' ?>">
                        <i class="bi bi-file-earmark-text me-2"></i> Contract
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'manajemen' || $role === 'client' || $role === 'ae'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('campaign') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'campaign' ? 'active text-white' : '' ?>">
                        <i class="bi bi-megaphone me-2"></i> Campaign
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'ae'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('ads') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'ads' ? 'active text-white' : '' ?>">
                        <i class="bi bi-collection-play me-2"></i> Ads
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'superadmin'): ?>
                <!-- platforms section -->
                <li class="nav-item mt-3 mb-1 px-3">
                    <span class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Platforms
                    </span>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('config/platforms/meta') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'platform_meta' ? 'active text-white' : '' ?>">
                        <i class="bi bi-meta me-2"></i> Meta
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('config/platforms/ga4') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'platform_ga4' ? 'active text-white' : '' ?>">
                        <i class="bi bi-bar-chart-line me-2"></i> GA4
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('config/platforms/youtube') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'platform_youtube' ? 'active text-white' : '' ?>">
                        <i class="bi bi-youtube me-2"></i> YouTube
                    </a>
                </li>

                <!-- master data section -->
                <li class="nav-item mt-3 mb-1 px-3">
                    <span class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Master data
                    </span>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('config/role') ?>"
                        class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'role' ? 'active text-white' : '' ?>">
                        <i class="bi bi-shield-lock me-2"></i> Roles
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</aside>