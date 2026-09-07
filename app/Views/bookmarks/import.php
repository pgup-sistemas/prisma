<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-file-earmark-arrow-up" style="color:var(--color-accent);"></i> Importar Favoritos</h2>
    <a href="<?= url('/bookmarks') ?>" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h6 class="display-font mb-3">Enviar arquivo</h6>
            <form method="POST" action="<?= url('/bookmarks/import') ?>" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label small">Arquivo .html exportado do navegador</label>
                    <input type="file" name="bookmarks_file" class="form-control form-control-sm" accept=".html,text/html" required>
                    <small style="color:var(--color-text-muted);">Formato NETSCAPE Bookmark File — máx. 10MB, 5.000 favoritos.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-upload"></i> Importar
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card p-4">
            <h6 class="display-font mb-3"><i class="bi bi-info-circle"></i> Como exportar</h6>
            <ul style="color:var(--color-text-secondary);font-size:.9rem;">
                <li class="mb-2"><strong>Chrome:</strong> Favoritos → Gerenciador de favoritos → ⋮ → Exportar favoritos</li>
                <li class="mb-2"><strong>Firefox:</strong> Favoritos → Gerenciar favoritos → Importar e Backup → Exportar favoritos para HTML</li>
                <li class="mb-2"><strong>Safari:</strong> Arquivo → Exportar favoritos</li>
                <li class="mb-2"><strong>Edge:</strong> Favoritos → ⋯ → Exportar favoritos</li>
            </ul>
            <p style="color:var(--color-text-muted);font-size:.85rem;" class="mb-0">
                A hierarquia de pastas é preservada e favoritos duplicados (mesma URL) são ignorados automaticamente.
            </p>
        </div>
    </div>
</div>
