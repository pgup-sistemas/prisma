<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><?= e($qr['label'] ?: 'QR Code') ?></h2>
    <a href="<?= url('/generate') ?>" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-5 text-center">
        <div class="card p-4">
            <?php
            $displayImg = (!empty($qr['file_logo_png']) && $qr['render_status'] === 'done')
                ? storageUrl($qr['file_logo_png'])
                : storageUrl($qr['file_png']);
            ?>
            <img src="<?= e($displayImg) ?>" alt="QR Code" id="qr-main-img"
                 class="img-fluid mb-3" style="background:#fff;border-radius:var(--radius);padding:.5rem;">

            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?= url('/download/' . $qr['uuid'] . '?format=png') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-download"></i> PNG
                </a>
                <a href="<?= url('/download/' . $qr['uuid'] . '?format=svg') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-download"></i> SVG
                </a>
                <?php if (!empty($qr['file_logo_png']) && $qr['render_status'] === 'done'): ?>
                <a href="<?= e(storageUrl($qr['file_logo_png'])) ?>" download class="btn btn-outline-light btn-sm">
                    <i class="bi bi-download"></i> QR-Logo
                </a>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-qr">
                    <i class="bi bi-trash"></i> Excluir
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <!-- Detalhes -->
        <div class="card p-4 mb-3">
            <h6 style="color:var(--color-text-secondary);">Detalhes</h6>
            <table class="table table-sm">
                <tr><th>Tipo</th><td><span class="badge badge-type"><?= e($qr['type']) ?></span></td></tr>
                <tr><th>Conteúdo</th><td style="word-break:break-all;"><?= e(truncate($qr['content'], 200)) ?></td></tr>
                <tr><th>ECC</th><td><?= e($qr['ecc_level']) ?></td></tr>
                <tr><th>Tamanho</th><td><?= (int) $qr['size'] ?>px</td></tr>
                <tr>
                    <th>Escaneamentos</th>
                    <td>
                        <?= (int) $qr['scan_count'] ?>
                        <?php if ($qr['scan_count'] > 0): ?>
                            <a href="<?= url('/analytics/' . $qr['uuid']) ?>"
                               class="ms-2 btn btn-sm btn-outline-light py-0 px-2" style="font-size:.75rem;">
                                <i class="bi bi-bar-chart"></i> Analytics
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><th>Criado em</th><td><?= e(date('d/m/Y H:i', strtotime($qr['created_at']))) ?></td></tr>
            </table>
        </div>

        <!-- URL Curta -->
        <?php if (!empty($qr['short_url'])): ?>
        <div class="card p-3 mb-3">
            <h6 style="color:var(--color-text-secondary);" class="mb-2">URL Curta</h6>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="shortUrlInput"
                       value="<?= e($qr['short_url']) ?>" readonly>
                <button class="btn btn-outline-light" type="button" onclick="copyShortUrl()">
                    <i class="bi bi-clipboard" id="copyIcon"></i>
                </button>
            </div>
            <small style="color:var(--color-text-muted);" class="mt-1 d-block">
                Redireciona para o conteúdo do QR e registra o scan automaticamente.
            </small>
        </div>
        <?php endif; ?>

        <!-- QR-Logo -->
        <div class="card p-4" id="qrlogo-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="display-font mb-0">
                    <i class="bi bi-image" style="color:var(--color-accent);"></i> QR-Logo
                </h6>
                <?php if (!empty($qr['legibility_index'])): ?>
                    <span class="badge"
                          style="background:var(--color-success);font-size:.8rem;">
                        Índice PRISMA: <?= (int)$qr['legibility_index'] ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php
            $renderStatus = $qr['render_status'] ?? 'done';
            $hasLogo      = !empty($qr['file_logo_png']) && $renderStatus === 'done';
            ?>

            <!-- Status: processando ou na fila -->
            <?php if ($renderStatus === 'processing' || $renderStatus === 'pending'): ?>
            <div id="qrlogo-processing" class="text-center py-3">
                <div class="spinner-border" style="color:var(--color-accent);" role="status"></div>
                <p class="mt-2" style="color:var(--color-text-secondary);">
                    Renderizando QR-Logo… isso pode levar alguns segundos.
                </p>
                <small style="color:var(--color-text-muted);">
                    O worker processa o job em background. Inicie o worker se ainda não estiver rodando.
                </small>
            </div>

            <!-- Status: falhou -->
            <?php elseif ($renderStatus === 'failed'): ?>
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-x-circle"></i>
                <strong>Falha na renderização.</strong> O índice de legibilidade ficou abaixo do mínimo (70).
                Crédito reembolsado automaticamente. Tente uma logo com menos detalhes.
            </div>

            <?php endif; ?>

            <!-- Upload form (sempre visível exceto durante processamento) -->
            <?php if ($renderStatus !== 'processing' && $renderStatus !== 'pending'): ?>
            <form id="logo-form" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

                <?php if ($hasLogo): ?>
                <p class="mb-2" style="color:var(--color-text-muted);font-size:.85rem;">
                    <i class="bi bi-check-circle" style="color:var(--color-success);"></i>
                    Logo aplicada. Custo: 1 crédito por nova renderização.
                </p>
                <?php else: ?>
                <p class="mb-2" style="color:var(--color-text-muted);font-size:.85rem;">
                    Adicione a logomarca do seu cliente ao QR Code.
                    O QR é gerado com ECC H (30% de tolerância a erros).<br>
                    <strong style="color:var(--color-accent);">Custo: 1 crédito por renderização.</strong>
                </p>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label small">Logo (PNG, JPEG, WebP — máx. 5MB)</label>
                    <input type="file" name="logo" id="logo-input" class="form-control form-control-sm"
                           accept="image/png,image/jpeg,image/gif,image/webp" required>
                </div>

                <div id="logo-preview-wrap" style="display:none;" class="mb-3 text-center">
                    <img id="logo-preview" class="rounded"
                         style="max-height:80px;max-width:80px;background:#fff;padding:4px;">
                </div>

                <button type="submit" class="btn btn-primary btn-sm" id="btn-add-logo">
                    <i class="bi bi-stars"></i>
                    <?= $hasLogo ? 'Substituir logo' : 'Gerar QR-Logo' ?>
                </button>

                <div id="logo-error" class="alert alert-danger py-2 mt-2 d-none"></div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" action="<?= url('/qr/' . $qr['uuid']) ?>" class="d-none">
    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
</form>

<script>
(function () {
    const QR_UUID   = <?= json_encode($qr['uuid']) ?>;
    const CSRF      = <?= json_encode($csrf_token) ?>;
    const STATUS_URL = <?= json_encode(url('/qr/' . $qr['uuid'] . '/logo/status')) ?>;
    const LOGO_URL  = <?= json_encode(url('/qr/' . $qr['uuid'] . '/logo')) ?>;
    let pollTimer   = null;

    // ── Excluir QR ────────────────────────────────────────────────────────
    document.getElementById('btn-delete-qr').addEventListener('click', function () {
        if (!confirm('Tem certeza que deseja excluir este QR Code?')) return;
        fetch(<?= json_encode(url('/qr/' . $qr['uuid'])) ?>, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(CSRF),
        }).then(() => { window.location.href = <?= json_encode(url('/generate')) ?>; });
    });

    // ── Copiar URL curta ──────────────────────────────────────────────────
    function copyShortUrl() {
        const input = document.getElementById('shortUrlInput');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(() => {
            const icon = document.getElementById('copyIcon');
            icon.className = 'bi bi-check-lg';
            setTimeout(() => { icon.className = 'bi bi-clipboard'; }, 1500);
        });
    }
    window.copyShortUrl = copyShortUrl;

    // ── Preview da logo selecionada ───────────────────────────────────────
    const logoInput = document.getElementById('logo-input');
    if (logoInput) {
        logoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const wrap = document.getElementById('logo-preview-wrap');
            const img  = document.getElementById('logo-preview');
            const url  = URL.createObjectURL(file);
            img.src = url;
            wrap.style.display = 'block';
        });
    }

    // ── Upload de logo ────────────────────────────────────────────────────
    const logoForm = document.getElementById('logo-form');
    if (logoForm) {
        logoForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn     = document.getElementById('btn-add-logo');
            const errDiv  = document.getElementById('logo-error');
            const logoFile = document.getElementById('logo-input').files[0];

            if (!logoFile) {
                showError('Selecione um arquivo de logo.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…';
            errDiv.classList.add('d-none');

            const fd = new FormData();
            fd.append('_csrf', CSRF);
            fd.append('logo', logoFile);

            fetch(LOGO_URL, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Mostra seção de processamento
                        const card = document.getElementById('qrlogo-card');
                        card.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="display-font mb-0">
                                    <i class="bi bi-image" style="color:var(--color-accent);"></i> QR-Logo
                                </h6>
                            </div>
                            <div id="qrlogo-processing" class="text-center py-3">
                                <div class="spinner-border" style="color:var(--color-accent);" role="status"></div>
                                <p class="mt-2" style="color:var(--color-text-secondary);">
                                    Renderizando QR-Logo…
                                </p>
                                <small style="color:var(--color-text-muted);">Créditos restantes: ${data.credits ?? '–'}</small>
                            </div>`;
                        startPolling();
                    } else {
                        showError(data.error || 'Erro ao enviar logo.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-stars"></i> Gerar QR-Logo';
                    }
                })
                .catch(() => {
                    showError('Erro de conexão. Tente novamente.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-stars"></i> Gerar QR-Logo';
                });
        });
    }

    function showError(msg) {
        const div = document.getElementById('logo-error');
        if (!div) return;
        div.textContent = msg;
        div.classList.remove('d-none');
    }

    // ── Polling de status ─────────────────────────────────────────────────
    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(checkStatus, 2500);
    }

    function checkStatus() {
        fetch(STATUS_URL)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'done') {
                    clearInterval(pollTimer);
                    onLogoReady(data);
                } else if (data.status === 'failed') {
                    clearInterval(pollTimer);
                    onLogoFailed(data.message);
                }
                // 'processing' ou 'pending': continua aguardando
            })
            .catch(() => {}); // silencia erros de rede durante polling
    }

    function onLogoReady(data) {
        // Troca imagem principal para o QR-Logo
        const img = document.getElementById('qr-main-img');
        if (img && data.logo_url) {
            img.src = data.logo_url + '?t=' + Date.now();
        }

        const card = document.getElementById('qrlogo-card');
        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="display-font mb-0">
                    <i class="bi bi-image" style="color:var(--color-accent);"></i> QR-Logo
                </h6>
                <span class="badge" style="background:var(--color-success);">
                    Índice PRISMA: ${data.legibility_index ?? '–'}
                </span>
            </div>
            <p style="color:var(--color-success);"><i class="bi bi-check-circle"></i> QR-Logo gerado com sucesso!</p>
            ${data.logo_url ? `<a href="${data.logo_url}" download class="btn btn-primary btn-sm"><i class="bi bi-download"></i> Download QR-Logo</a>` : ''}`;
    }

    function onLogoFailed(message) {
        const card = document.getElementById('qrlogo-card');
        card.innerHTML = `
            <h6 class="display-font mb-3">
                <i class="bi bi-image" style="color:var(--color-accent);"></i> QR-Logo
            </h6>
            <div class="alert alert-danger py-2">
                <i class="bi bi-x-circle"></i>
                <strong>Falha na renderização.</strong> ${message || 'Índice de legibilidade insuficiente. Crédito reembolsado.'}
            </div>
            <p><a href="" onclick="location.reload()" class="btn btn-outline-light btn-sm">Tentar novamente</a></p>`;
    }

    // Auto-inicia polling se estava em processamento ao carregar a página
    <?php if (in_array($qr['render_status'] ?? '', ['processing', 'pending'])): ?>
    startPolling();
    <?php endif; ?>
})();
</script>
