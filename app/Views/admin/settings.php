<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-gear" style="color:var(--color-accent);"></i> Configurações</h2>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h6 class="display-font mb-3">Geral</h6>
            <form method="POST" action="<?= url('/admin/settings') ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label small">Nome do site</label>
                    <input type="text" name="site_name" class="form-control form-control-sm"
                           value="<?= e($settings['site_name']) ?>" maxlength="60">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Créditos iniciais para novos usuários</label>
                    <input type="number" name="default_credits" class="form-control form-control-sm"
                           value="<?= (int) $settings['default_credits'] ?>" min="0">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="allow_registration" class="form-check-input" id="allow-registration"
                           <?= $settings['allow_registration'] ? 'checked' : '' ?>>
                    <label for="allow-registration" class="form-check-label small">Permitir novos cadastros</label>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="maintenance_mode" class="form-check-input" id="maintenance-mode"
                           <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                    <label for="maintenance-mode" class="form-check-label small">Modo manutenção</label>
                    <small class="d-block" style="color:var(--color-text-muted);">Bloqueia novos logins de usuários não-admin.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-lg"></i> Salvar configurações
                </button>
            </form>
        </div>
    </div>
</div>
