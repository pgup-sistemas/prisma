<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-person-circle" style="color:var(--color-accent);"></i> Meu Perfil</h2>
</div>

<div class="row g-4">
    <!-- Dados pessoais -->
    <div class="col-lg-6">
        <div class="card p-4">
            <h6 class="display-font mb-3">Dados Pessoais</h6>
            <form method="POST" action="<?= url('/profile') ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label small">Nome *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($profile['name'] ?? '') ?>" required minlength="2" maxlength="120">
                </div>
                <div class="mb-3">
                    <label class="form-label small">E-mail *</label>
                    <input type="email" name="email" class="form-control" value="<?= e($profile['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Nova senha <span style="color:var(--color-text-muted);">(deixe em branco para manter)</span></label>
                    <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-lg"></i> Salvar alterações
                </button>
            </form>
        </div>
    </div>

    <!-- Informações da conta -->
    <div class="col-lg-6">
        <div class="card p-4 mb-4">
            <h6 class="display-font mb-3">Informações da Conta</h6>
            <table class="table table-sm mb-0">
                <tr>
                    <td style="color:var(--color-text-muted);width:40%;">Função</td>
                    <td><span class="badge" style="background:var(--color-accent);"><?= e($profile['role'] ?? '') ?></span></td>
                </tr>
                <tr>
                    <td style="color:var(--color-text-muted);">Créditos</td>
                    <td><i class="bi bi-coin" style="color:var(--color-warning);"></i> <?= (int)($profile['credits'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td style="color:var(--color-text-muted);">E-mail verificado</td>
                    <td>
                        <?php if ($profile['email_verified'] ?? false): ?>
                            <i class="bi bi-check-circle-fill" style="color:var(--color-success);"></i> Sim
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill" style="color:var(--color-danger);"></i> Não
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--color-text-muted);">Membro desde</td>
                    <td><?= date('d/m/Y', strtotime($profile['created_at'] ?? 'now')) ?></td>
                </tr>
                <tr>
                    <td style="color:var(--color-text-muted);">Último login</td>
                    <td><?= $profile['last_login_at'] ? date('d/m/Y H:i', strtotime($profile['last_login_at'])) : '—' ?></td>
                </tr>
            </table>
        </div>

        <!-- API Key -->
        <div class="card p-4">
            <h6 class="display-font mb-3">Chave de API</h6>
            <?php if (!empty($profile['api_key'])): ?>
            <div class="input-group mb-3">
                <input type="text" class="form-control form-control-sm" value="<?= e($profile['api_key']) ?>" id="api-key-input" readonly style="font-family:var(--font-mono);font-size:.8rem;">
                <button class="btn btn-outline-light btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('api-key-input').value);this.innerHTML='<i class=\'bi bi-check\'></i>';" title="Copiar">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
            <?php else: ?>
            <p style="color:var(--color-text-muted);" class="mb-3">Nenhuma chave gerada ainda.</p>
            <?php endif; ?>
            <form method="POST" action="<?= url('/profile/api-key') ?>" onsubmit="return confirm('Isso invalidará a chave atual. Continuar?')">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-arrow-repeat"></i> <?= empty($profile['api_key']) ? 'Gerar chave' : 'Regenerar chave' ?>
                </button>
            </form>
            <small class="d-block mt-2" style="color:var(--color-text-muted);">
                Use o header <code>X-API-Key: &lt;chave&gt;</code> nas chamadas à API REST.
            </small>
        </div>
    </div>
</div>

<!-- Launcher Widget -->
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card p-4">
            <h6 class="display-font mb-2"><i class="bi bi-lightning-charge"></i> Widget Launcher</h6>
            <?php if (!empty($profile['api_key'])): ?>
            <p style="color:var(--color-text-secondary);font-size:.85rem;">
                Cole este trecho antes do <code>&lt;/body&gt;</code> do seu site para embutir o buscador rápido
                (favoritos, links e QR codes) com atalho <kbd>Ctrl</kbd>+<kbd>K</kbd>.
            </p>
            <div class="input-group mb-2">
                <textarea class="form-control form-control-sm" id="launcher-embed-snippet" rows="3" readonly
                          style="font-family:var(--font-mono);font-size:.78rem;background:var(--color-elevated);"><?= e('<script src="' . url('/launcher/embed') . '" data-user-id="' . ($profile['uuid'] ?? '') . '" data-api-key="' . $profile['api_key'] . '" async></script>') ?></textarea>
            </div>
            <button class="btn btn-outline-light btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('launcher-embed-snippet').value);this.innerHTML='<i class=\'bi bi-check\'></i> Copiado';">
                <i class="bi bi-clipboard"></i> Copiar snippet
            </button>
            <?php else: ?>
            <p style="color:var(--color-text-muted);" class="mb-0">Gere uma chave de API acima para obter o snippet de embed do Launcher.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($profile['api_key'])):
    $deepLink = 'prisma-launcher://conectar?' . http_build_query([
        'server' => rtrim(url(''), '/'),
        'uid'    => $profile['uuid'] ?? '',
        'key'    => $profile['api_key'],
    ]);
?>
<!-- Pareamento com o Launcher desktop -->
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="p-3 rounded d-flex flex-wrap align-items-center gap-3"
             style="background:rgba(46,134,171,.08);border:1px solid rgba(46,134,171,.25);backdrop-filter:blur(6px);">
            <i class="bi bi-lightning-charge-fill flex-shrink-0" style="font-size:1.5rem;color:var(--color-accent);"></i>
            <div class="flex-grow-1" style="min-width:220px;">
                <strong style="color:var(--color-text-primary);">PRISMA Launcher no seu computador</strong>
                <p class="mb-0 small" style="color:var(--color-text-secondary);">
                    Já instalou? Conecte sua conta com um clique. Ainda não? Baixe grátis.
                </p>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
                <a href="<?= e($deepLink) ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plug"></i> Conectar Launcher
                </a>
                <a href="<?= url('/download') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-download"></i> Baixar
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
