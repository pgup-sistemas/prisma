<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'PRISMA') ?> — PRISMA</title>
    <script>
        (function(){var t=localStorage.getItem('prisma-theme')||'dark';document.documentElement.setAttribute('data-theme',t);document.documentElement.setAttribute('data-bs-theme',t);})();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg px-3">
        <button class="btn btn-sm btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand display-font" href="<?= url('/dashboard') ?>" style="color:var(--color-text-primary);">PRISMA</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <button id="theme-toggle" class="btn btn-sm btn-outline-light" title="Alternar tema" onclick="prismaToggleTheme()">
                <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
            </button>
            <?php if ($auth_user): ?>
                <span class="badge rounded-pill" style="background:var(--color-elevated);color:var(--color-text-secondary);">
                    <i class="bi bi-coin"></i> <?= (int) $auth_user['credits'] ?> créditos
                </span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <?= e($auth_user['name']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('/profile') ?>">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('/logout') ?>">Sair</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <div class="spectrum-bar"></div>

    <div class="d-flex">
        <?php $sidebarContent = function () use ($auth_user) { ?>
            <div class="sidebar p-3">
                <ul class="nav nav-pills flex-column gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/dashboard') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>

                    <li class="nav-item mt-3"><small class="text-uppercase" style="color:var(--color-text-muted);">QR Code</small></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/generate') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-qr-code me-2"></i>Gerar QR
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/history') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-clock-history me-2"></i>Histórico
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/analytics') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-graph-up me-2"></i>Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/batch') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-collection me-2"></i>Lote
                        </a>
                    </li>

                    <li class="nav-item mt-3"><small class="text-uppercase" style="color:var(--color-text-muted);">Links</small></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/links') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-link-45deg me-2"></i>Encurtador
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/hub') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-grid-3x3-gap me-2"></i>Hub Digital
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/agenda') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-calendar-check me-2"></i>Agenda
                        </a>
                    </li>

                    <li class="nav-item mt-3"><small class="text-uppercase" style="color:var(--color-text-muted);">Mais</small></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/bookmarks') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-bookmark-star me-2"></i>Favoritos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/tools') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-tools me-2"></i>Ferramentas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/scanner') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-camera me-2"></i>Scanner
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/download') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-lightning-charge me-2"></i>Launcher Desktop
                        </a>
                    </li>

                    <?php if ($auth_user && in_array($auth_user['role'], ['admin', 'superadmin'], true)): ?>
                        <li class="nav-item mt-3"><small class="text-uppercase" style="color:var(--color-text-muted);">Admin</small></li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/users') ?>" style="color:var(--color-text-secondary);">
                                <i class="bi bi-people me-2"></i>Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/settings') ?>" style="color:var(--color-text-secondary);">
                                <i class="bi bi-gear me-2"></i>Configurações
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php }; ?>

        <div class="d-none d-lg-block" style="width:240px;flex-shrink:0;">
            <?php $sidebarContent(); ?>
        </div>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" style="background:var(--color-surface);width:240px;">
            <div class="offcanvas-header">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0">
                <?php $sidebarContent(); ?>
            </div>
        </div>

        <div class="flex-grow-1 p-4">
            <?= renderFlash() ?>
            <?= $content ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
    function prismaToggleTheme() {
        var html = document.documentElement;
        var cur  = html.getAttribute('data-theme') || 'dark';
        var next = cur === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('prisma-theme', next);
        document.getElementById('theme-icon').className =
            next === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    }
    // Sync icon on load
    (function(){
        var t = localStorage.getItem('prisma-theme') || 'dark';
        var ic = document.getElementById('theme-icon');
        if (ic) ic.className = t === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    })();
    </script>
</body>
</html>
