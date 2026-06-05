<aside class="collapse show collapse-horizontal col-sm-2 p-3 border-end bg-body-tertiary vh-100 sticky-top" id="sidebarCollapse">
    <a href="<?= base_url('welcome') ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <i class="bi bi-bar-chart-fill me-2 fs-4 text-primary"></i>
        <span class="fs-6 fw-semibold">Kontan Ad Reporter</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'dashboard' ? 'active text-white' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('account') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'account' ? 'active text-white' : '' ?>">
                <i class="bi bi-people me-2"></i> Account
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('client') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'client' ? 'active text-white' : '' ?>">
                <i class="bi bi-person-check me-2"></i> Client
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('ads') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'ads' ? 'active text-white' : '' ?>">
                <i class="bi bi-collection-play me-2"></i> Ads
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('config/credentials') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'api' ? 'active text-white' : '' ?>">
                <i class="bi bi-key me-2"></i> API Credentials
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('config/filter-keyword') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'configuration' ? 'active text-white' : '' ?>">
                <i class="bi bi-funnel me-2"></i> Filter Keyword
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('config/role') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'role' ? 'active text-white' : '' ?>">
                <i class="bi bi-shield-lock me-2"></i> Roles
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('config/platform') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'platform' ? 'active text-white' : '' ?>">
                <i class="bi bi-hdd-network me-2"></i> Platforms
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('config/keyword-type') ?>" class="nav-link link-body-emphasis <?= ($active_menu ?? '') === 'keyword_type' ? 'active text-white' : '' ?>">
                <i class="bi bi-tags me-2"></i> Keyword Types
            </a>
        </li>
    </ul>
</aside>
