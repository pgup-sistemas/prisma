<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'PRISMA') ?></title>
    <meta name="description" content="<?= e($meta_description ?? '') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php if (!empty($theme_color)): ?>
    <style>:root { --hub-theme: <?= e($theme_color) ?>; }</style>
    <?php endif; ?>
</head>
<body style="background:var(--color-base);min-height:100vh;">
    <?php if ($show_public_nav ?? true): ?>
    <nav class="navbar navbar-expand-lg px-3" style="background:var(--color-surface);border-bottom:1px solid var(--color-border);">
        <div class="container-fluid px-0">
            <a class="navbar-brand display-font d-flex align-items-center gap-2" href="<?= url('/') ?>" style="color:var(--color-text-primary);">
                <img src="<?= asset('img/logo.svg') ?>" height="28" alt="" onerror="this.style.display='none'">
                PRISMA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav"
                    aria-controls="publicNav" aria-expanded="false" aria-label="Alternar navegação"
                    style="border-color:var(--color-border);">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/tools') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-tools me-1"></i>Ferramentas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/download') ?>" style="color:var(--color-text-secondary);">
                            <i class="bi bi-lightning-charge me-1"></i>Launcher Desktop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-lg-2" href="<?= url('/login') ?>">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="spectrum-bar"></div>
    <?php endif; ?>
    <?= $content ?>
    <footer class="text-center py-4 mt-auto" style="color:var(--color-text-muted);font-size:.8rem;">
        Criado com <strong style="color:var(--color-accent);">PRISMA</strong> · PageUp Sistemas
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
