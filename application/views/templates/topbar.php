<nav class="navbar sticky-top navbar-expand-lg border-bottom bg-body-tertiary">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary btn-sm me-2" type="button"
            data-bs-toggle="collapse" data-bs-target="#sidebarCollapse"
            aria-expanded="true" aria-controls="sidebarCollapse">
            <i class="bi bi-layout-sidebar"></i>
        </button>
        <span class="navbar-brand mb-0 h6"><?= isset($title) ? $title : 'Page' ?></span>
        <div class="ms-auto dropdown">
            <a href="#" class="ms-auto d-flex align-items-center gap-2 link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="small text-muted"><?= $current_account['name'] ?></span>
                <span class="badge bg-secondary"><?= $current_account['role'] ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end text-small shadow">
                <li><a href="#" class="dropdown-item">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?= base_url('auth/logout') ?>" method="POST">
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-left me-2"></i> Sign out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
