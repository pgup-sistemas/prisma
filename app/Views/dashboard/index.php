<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="display-font mb-0">Olá, <?= e($auth_user['name']) ?>! 👋</h2>
        <p class="mb-0" style="color:var(--color-text-muted);font-size:.9rem;">Bem-vindo ao PRISMA — sua plataforma de links e QR Codes.</p>
    </div>
    <a href="<?= url('/generate') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Novo QR Code
    </a>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <div style="font-size:2rem;font-weight:700;color:var(--color-accent);"><?= number_format($qr_total) ?></div>
            <div style="font-size:.8rem;color:var(--color-text-muted);"><i class="bi bi-qr-code"></i> QR Codes</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <div style="font-size:2rem;font-weight:700;color:var(--color-success);"><?= number_format($scan_total) ?></div>
            <div style="font-size:.8rem;color:var(--color-text-muted);"><i class="bi bi-camera"></i> Scans</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <div style="font-size:2rem;font-weight:700;color:var(--color-accent-alt);"><?= number_format($link_total) ?></div>
            <div style="font-size:.8rem;color:var(--color-text-muted);"><i class="bi bi-link-45deg"></i> Links</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <div style="font-size:2rem;font-weight:700;color:var(--color-warning);"><?= number_format($credits) ?></div>
            <div style="font-size:.8rem;color:var(--color-text-muted);"><i class="bi bi-coin"></i> Créditos</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- QR Codes recentes -->
    <div class="col-lg-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="display-font mb-0">QR Codes recentes</h6>
                <a href="<?= url('/history') ?>" class="btn btn-sm btn-outline-light">Ver todos</a>
            </div>
            <?php if (empty($recent_qr)): ?>
                <p style="color:var(--color-text-muted);" class="text-center py-3">Nenhum QR Code ainda.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Label</th><th>Tipo</th><th>Scans</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_qr as $qr): ?>
                    <tr>
                        <td style="font-size:.85rem;"><?= e($qr['label'] ?: '—') ?></td>
                        <td><span class="badge" style="background:var(--color-elevated);color:var(--color-accent);font-size:.7rem;"><?= e($qr['type']) ?></span></td>
                        <td style="font-size:.85rem;"><?= number_format((int)$qr['scan_count']) ?></td>
                        <td><a href="<?= url('/qr/' . $qr['uuid']) ?>" class="btn btn-sm btn-outline-light py-0 px-2"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Links recentes -->
    <div class="col-lg-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="display-font mb-0">Links recentes</h6>
                <a href="<?= url('/links') ?>" class="btn btn-sm btn-outline-light">Ver todos</a>
            </div>
            <?php if (empty($recent_links)): ?>
                <p style="color:var(--color-text-muted);" class="text-center py-3">Nenhum link encurtado ainda.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Slug</th><th>Título</th><th>Cliques</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_links as $lnk): ?>
                    <tr>
                        <td style="font-size:.85rem;font-family:var(--font-mono);">/r/<?= e($lnk['slug']) ?></td>
                        <td style="font-size:.85rem;"><?= e(truncate($lnk['title'] ?: '—', 30)) ?></td>
                        <td style="font-size:.85rem;"><?= number_format((int)$lnk['click_count']) ?></td>
                        <td><a href="<?= url('/links/' . $lnk['uuid']) ?>" class="btn btn-sm btn-outline-light py-0 px-2"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Atalhos -->
<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card p-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= url('/generate') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-qr-code"></i> Gerar QR</a>
                <a href="<?= url('/links/create') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-link-45deg"></i> Novo Link</a>
                <a href="<?= url('/hub') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-grid-3x3-gap"></i> Hub Digital</a>
                <a href="<?= url('/analytics') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-graph-up"></i> Analytics</a>
                <a href="<?= url('/scanner') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-camera"></i> Scanner</a>
                <a href="<?= url('/tools') ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-tools"></i> Ferramentas</a>
            </div>
        </div>
    </div>
</div>
