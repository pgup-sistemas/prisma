<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'PRISMA') ?> — PRISMA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body style="background:var(--color-base);min-height:100vh;display:flex;align-items:center;">
    <div class="container" style="max-width:420px;margin:auto;">
        <div class="text-center mb-4">
            <h4 class="mt-2" style="font-family:var(--font-display);color:var(--color-text-primary);">PRISMA</h4>
            <div class="spectrum-bar mt-1 rounded"></div>
        </div>
        <div class="card p-4">
            <?= renderFlash() ?>
            <?= $content ?>
        </div>
        <p class="text-center mt-3" style="color:var(--color-text-muted);font-size:.85rem;">
            © <?= date('Y') ?> PageUp Sistemas · Porto Velho, RO
        </p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
