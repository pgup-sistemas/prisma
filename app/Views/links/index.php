<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0">Links</h2>
    <a href="<?= url('/links/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Link
    </a>
</div>

<!-- Filtros -->
<form method="GET" action="<?= url('/links') ?>" class="card p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-sm-6">
            <label class="form-label small" style="color:var(--color-text-secondary);">Busca</label>
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Título, destino ou slug…" value="<?= e($search) ?>">
        </div>
        <div class="col-sm-4">
            <label class="form-label small" style="color:var(--color-text-secondary);">Tipo</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Todos</option>
                <?php foreach (['none','intersticial','utm','conditional','ab','cloaking'] as $t): ?>
                    <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-2">
            <button type="submit" class="btn btn-outline-light btn-sm w-100">
                <i class="bi bi-search"></i> Filtrar
            </button>
        </div>
    </div>
</form>

<?php if (empty($links)): ?>
    <div class="card p-5 text-center">
        <i class="bi bi-link-45deg display-4 mb-3" style="color:var(--color-text-muted);"></i>
        <p style="color:var(--color-text-secondary);">Nenhum link encontrado.</p>
        <a href="<?= url('/links/create') ?>" class="btn btn-primary btn-sm mx-auto" style="width:fit-content;">
            Criar meu primeiro link
        </a>
    </div>
<?php else: ?>
    <?php
    $typeColors = [
        'none' => 'var(--color-text-muted)', 'intersticial' => '#F59E0B',
        'utm'  => '#2E86AB',  'conditional' => '#A23B72',
        'ab'   => '#22C55E',  'cloaking'    => '#EF4444',
    ];
    $typeIcons = [
        'none' => 'bi-arrow-right-circle', 'intersticial' => 'bi-hourglass-split',
        'utm'  => 'bi-tags',               'conditional'  => 'bi-diagram-3',
        'ab'   => 'bi-bar-chart',          'cloaking'     => 'bi-incognito',
    ];
    ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="color:var(--color-text-secondary);border-bottom:1px solid var(--color-border);">
                        <th>Slug / Destino</th>
                        <th>Tipo</th>
                        <th>Cliques</th>
                        <th>Status</th>
                        <th>Criado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $lk): ?>
                        <?php $color = $typeColors[$lk['wrapper_type']] ?? 'var(--color-text-muted)'; ?>
                        <tr>
                            <td>
                                <div style="color:var(--color-accent);font-family:var(--font-mono);font-size:.9rem;">
                                    /r/<?= e($lk['slug']) ?>
                                </div>
                                <small style="color:var(--color-text-muted);">→ <?= e(truncate($lk['destination'], 55)) ?></small>
                            </td>
                            <td>
                                <span style="color:<?= $color ?>;">
                                    <i class="bi <?= $typeIcons[$lk['wrapper_type']] ?? 'bi-question' ?>"></i>
                                    <?= e($lk['wrapper_type']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color:var(--color-text-primary);"><?= number_format((int) $lk['click_count']) ?></strong>
                            </td>
                            <td>
                                <?php if ((bool) $lk['active']): ?>
                                    <span class="badge badge-success">ativo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">inativo</span>
                                <?php endif; ?>
                                <?php if (!empty($lk['expires_at']) && strtotime($lk['expires_at']) < time()): ?>
                                    <span class="badge badge-danger ms-1">expirado</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--color-text-secondary);">
                                <?= e(date('d/m/Y', strtotime($lk['created_at']))) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/links/' . $lk['uuid']) ?>"
                                   class="btn btn-sm btn-outline-light" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginação -->
    <?php if ($pages > 1): ?>
        <nav class="mt-4 d-flex justify-content-between align-items-center">
            <small style="color:var(--color-text-muted);"><?= $total ?> links · página <?= $page ?> de <?= $pages ?></small>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="<?= url('/links?page=' . $p . ($search ? '&q=' . urlencode($search) : '') . ($type ? '&type=' . urlencode($type) : '')) ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
