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
        <button id="sidebar-collapse-toggle" class="btn btn-sm btn-outline-light d-none d-lg-inline-flex me-2" type="button" title="Recolher menu" onclick="prismaToggleSidebar()">
            <i class="bi bi-layout-sidebar-inset"></i>
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
        <?php
        $currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
        $isActive = function (string $path) use ($currentPath): bool {
            $target = rtrim((string) parse_url(url($path), PHP_URL_PATH), '/');
            return $currentPath !== '' && ($currentPath === $target || str_starts_with($currentPath, $target . '/'));
        };
        $sidebarGroups = [
            'QR Code' => [
                ['/generate',  'bi-qr-code',        'Gerar QR'],
                ['/history',   'bi-clock-history',  'Histórico'],
                ['/analytics', 'bi-graph-up',        'Analytics'],
                ['/batch',     'bi-collection',      'Lote'],
            ],
            'Links' => [
                ['/links',  'bi-link-45deg',      'Encurtador'],
                ['/hub',    'bi-grid-3x3-gap',    'Hub Digital'],
                ['/agenda', 'bi-calendar-check',  'Agenda'],
            ],
            'Mais' => [
                ['/bookmarks', 'bi-bookmark-star',    'Favoritos'],
                ['/tools',     'bi-tools',            'Ferramentas'],
                ['/scanner',   'bi-camera',            'Scanner'],
                ['/download',  'bi-lightning-charge',  'Launcher'],
            ],
        ];
        if ($auth_user && in_array($auth_user['role'], ['admin', 'superadmin'], true)) {
            $sidebarGroups['Admin'] = [
                ['/admin/users',    'bi-people', 'Usuários'],
                ['/admin/settings', 'bi-gear',   'Configurações'],
            ];
        }
        $sidebarContent = function () use ($sidebarGroups, $isActive) { ?>
            <div class="sidebar p-3">
                <div class="sidebar-cards sidebar-cards-1col">
                    <a class="sidebar-card sidebar-card-wide<?= $isActive('/dashboard') ? ' active' : '' ?>" href="<?= url('/dashboard') ?>">
                        <i class="bi bi-speedometer2"></i>Dashboard
                    </a>
                </div>
                <?php foreach ($sidebarGroups as $title => $items): ?>
                    <div class="sidebar-group-title"><?= e($title) ?></div>
                    <div class="sidebar-cards">
                        <?php foreach ($items as [$path, $icon, $label]): ?>
                            <a class="sidebar-card<?= $isActive($path) ? ' active' : '' ?>" href="<?= url($path) ?>">
                                <i class="bi <?= $icon ?>"></i><?= e($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php }; ?>

        <div id="sidebar-desktop" class="d-none d-lg-block sidebar-wrap">
            <div style="width:240px;">
                <?php $sidebarContent(); ?>
            </div>
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

    function prismaToggleSidebar() {
        var el = document.getElementById('sidebar-desktop');
        var collapsed = el.classList.toggle('collapsed');
        localStorage.setItem('prisma-sidebar-collapsed', collapsed ? '1' : '0');
    }
    // Restaura o estado salvo (sem transição no load, pra não "piscar")
    (function(){
        if (localStorage.getItem('prisma-sidebar-collapsed') === '1') {
            var el = document.getElementById('sidebar-desktop');
            if (el) el.classList.add('collapsed', 'no-transition');
            requestAnimationFrame(function () {
                if (el) el.classList.remove('no-transition');
            });
        }
    })();
    </script>
</body>
</html>
