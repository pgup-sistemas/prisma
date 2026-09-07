<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="display-font mb-0"><i class="bi bi-bookmark-star" style="color:var(--color-accent);"></i> Favoritos</h2>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-light btn-sm" onclick="runHealthCheck()">
            <i class="bi bi-activity"></i> Checar links
        </button>
        <form method="POST" action="<?= url('/bookmarks/export') ?>" class="d-inline">
            <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
            <button type="submit" class="btn btn-outline-light btn-sm" <?= $total === 0 ? 'disabled' : '' ?>>
                <i class="bi bi-download"></i> Exportar
            </button>
        </form>
        <a href="<?= url('/bookmarks/import') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-upload"></i> Importar
        </a>
    </div>
</div>

<?php if ($total === 0): ?>
<div class="card p-5 text-center">
    <i class="bi bi-bookmark-star" style="font-size:3rem;color:var(--color-accent);opacity:.5;"></i>
    <h5 class="mt-3" style="color:var(--color-text-secondary);">Nenhum favorito importado ainda</h5>
    <p style="color:var(--color-text-muted);">Importe o arquivo .html exportado do seu navegador para começar.</p>
    <a href="<?= url('/bookmarks/import') ?>" class="btn btn-primary mt-2">
        <i class="bi bi-upload"></i> Importar Favoritos
    </a>
</div>
<?php else: ?>

<div class="row g-4">
    <!-- Pastas -->
    <div class="col-lg-3">
        <div class="card p-3">
            <h6 class="display-font mb-2" style="font-size:.9rem;">Pastas</h6>
            <ul class="nav nav-pills flex-column gap-1">
                <?php $folderUrl = fn (?int $id) => url('/bookmarks' . ($id !== null || $search !== '' ? '?' . http_build_query(array_filter(['folder' => $id, 'q' => $search !== '' ? $search : null])) : '')); ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentFolder === null ? 'active' : '' ?>" href="<?= $folderUrl(null) ?>" style="font-size:.85rem;<?= $currentFolder === null ? '' : 'color:var(--color-text-secondary);' ?>">
                        <i class="bi bi-collection me-1"></i> Todos (<?= $total ?>)
                    </a>
                </li>
                <?php
                $renderFolder = function (array $folders, int $depth = 0) use (&$renderFolder, $currentFolder, $folderUrl) {
                    foreach ($folders as $folder):
                        $active = $currentFolder === (int) $folder['id'];
                ?>
                    <li class="nav-item" style="margin-left:<?= $depth * 12 ?>px;">
                        <a class="nav-link <?= $active ? 'active' : '' ?>" href="<?= $folderUrl((int) $folder['id']) ?>" style="font-size:.85rem;<?= $active ? '' : 'color:var(--color-text-secondary);' ?>">
                            <i class="bi bi-folder me-1"></i> <?= e($folder['name']) ?>
                        </a>
                    </li>
                <?php
                        if (!empty($folder['children'])) {
                            $renderFolder($folder['children'], $depth + 1);
                        }
                    endforeach;
                };
                $renderFolder($tree);
                ?>
            </ul>
        </div>
    </div>

    <!-- Lista -->
    <div class="col-lg-9">
        <div class="card p-3 mb-3">
            <form method="GET" action="<?= url('/bookmarks') ?>" class="d-flex gap-2">
                <?php if ($currentFolder !== null): ?>
                <input type="hidden" name="folder" value="<?= (int) $currentFolder ?>">
                <?php endif; ?>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por título ou URL..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-outline-light btn-sm"><i class="bi bi-search"></i></button>
                <?php if ($search !== ''): ?>
                <a href="<?= url('/bookmarks' . ($currentFolder !== null ? '?folder=' . $currentFolder : '')) ?>" class="btn btn-outline-light btn-sm" title="Limpar busca">
                    <i class="bi bi-x-lg"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($bookmarks)): ?>
        <div class="card p-5 text-center">
            <i class="bi bi-search" style="font-size:2.5rem;color:var(--color-accent);opacity:.5;"></i>
            <p class="mt-3 mb-0" style="color:var(--color-text-muted);">Nenhum favorito encontrado<?= $search !== '' ? ' para "' . e($search) . '"' : '' ?>.</p>
        </div>
        <?php else: ?>

        <!-- Barra de ações em massa -->
        <div class="card p-2 mb-2 d-none align-items-center gap-2 flex-wrap" id="bulk-toolbar">
            <span style="color:var(--color-text-secondary);font-size:.85rem;" class="ms-2">
                <strong id="bulk-count">0</strong> selecionado(s)
            </span>
            <button class="btn btn-sm btn-primary" onclick="bulkAction('launcher_on')">
                <i class="bi bi-lightning-charge"></i> Marcar no Launcher
            </button>
            <button class="btn btn-sm btn-outline-light" onclick="bulkAction('launcher_off')">
                <i class="bi bi-lightning-charge-off"></i> Remover do Launcher
            </button>
            <button class="btn btn-sm btn-outline-danger ms-auto me-2" onclick="bulkAction('delete')">
                <i class="bi bi-trash"></i> Excluir selecionados
            </button>
        </div>

        <div class="card p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr style="color:var(--color-text-muted);font-size:.8rem;">
                            <th class="ps-3" style="width:36px;">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>Favorito</th>
                            <th>Status</th>
                            <th>Launcher</th>
                            <th class="text-end pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="bookmarks-tbody">
                        <?php foreach ($bookmarks as $b): ?>
                        <tr data-bookmark-id="<?= $b['id'] ?>">
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input bookmark-check" value="<?= (int) $b['id'] ?>">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($b['favicon'])): ?>
                                        <img src="<?= e($b['favicon']) ?>" style="width:16px;height:16px;" alt="" onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <i class="bi bi-globe" style="color:var(--color-text-muted);"></i>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= e($b['url']) ?>" target="_blank" rel="noopener" style="color:var(--color-text-primary);text-decoration:none;font-size:.9rem;">
                                            <?= e(truncate($b['title'], 60)) ?>
                                        </a>
                                        <div style="color:var(--color-text-muted);font-size:.75rem;"><?= e(truncate($b['url'], 70)) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $healthMap = [
                                    'ok'      => ['success', 'bi-check-circle', 'ok'],
                                    'broken'  => ['danger', 'bi-x-circle', 'quebrado'],
                                    'unknown' => ['secondary', 'bi-question-circle', 'não checado'],
                                ];
                                [$cls, $icon, $label] = $healthMap[$b['health']] ?? $healthMap['unknown'];
                                ?>
                                <span class="badge health-badge" style="background:var(--color-<?= $cls ?>, #4E6B87);font-size:.7rem;">
                                    <i class="bi <?= $icon ?>"></i> <?= $label ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm py-0 px-2 launcher-toggle <?= $b['in_launcher'] ? 'btn-primary' : 'btn-outline-light' ?>"
                                        onclick="toggleLauncher(<?= $b['id'] ?>, this)">
                                    <i class="bi bi-lightning-charge"></i>
                                </button>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="deleteBookmark(<?= $b['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php
                $pageUrl = function (int $p) use ($currentFolder, $search) {
                    $qs = ['page' => $p];
                    if ($currentFolder !== null) $qs['folder'] = $currentFolder;
                    if ($search !== '') $qs['q'] = $search;
                    return url('/bookmarks?' . http_build_query($qs));
                };
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl(max(1, $page - 1)) ?>">&laquo;</a>
                </li>
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl($p) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl(min($pages, $page + 1)) ?>">&raquo;</a>
                </li>
            </ul>
            <p class="text-center mt-2 mb-0" style="color:var(--color-text-muted);font-size:.8rem;">
                <?= (int) $totalFiltered ?> favorito<?= $totalFiltered === 1 ? '' : 's' ?> <?= $search !== '' ? 'encontrado' . ($totalFiltered === 1 ? '' : 's') : 'no total' ?>
            </p>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
const CSRF = <?= json_encode($csrf_token) ?>;
const BASE = <?= json_encode(url('/bookmarks/')) ?>;

function deleteBookmark(id) {
    if (!confirm('Excluir este favorito?')) return;
    fetch(BASE + id, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(CSRF),
    }).then(r => r.json()).then(d => {
        if (d.success) document.querySelector('tr[data-bookmark-id="' + id + '"]')?.remove();
    });
}

function toggleLauncher(id, btn) {
    fetch(BASE + id + '/launcher', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(CSRF),
    }).then(r => r.json()).then(d => {
        if (!d.success) return;
        btn.classList.toggle('btn-primary', d.in_launcher);
        btn.classList.toggle('btn-outline-light', !d.in_launcher);
    });
}

function runHealthCheck() {
    fetch(<?= json_encode(url('/bookmarks/health-check')) ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(CSRF),
    }).then(r => r.json()).then(d => {
        if (d.success) alert(d.queued + ' favoritos enfileirados para checagem. Atualize a página em alguns segundos.');
    });
}

// ── Seleção em massa ──────────────────────────────────────────────────
const selectAllBox = document.getElementById('select-all');
const bulkToolbar   = document.getElementById('bulk-toolbar');
const bulkCountEl   = document.getElementById('bulk-count');

function getChecks() {
    return Array.from(document.querySelectorAll('.bookmark-check'));
}

function updateBulkToolbar() {
    const checked = getChecks().filter((c) => c.checked);
    bulkCountEl.textContent = checked.length;
    bulkToolbar.classList.toggle('d-none', checked.length === 0);
    bulkToolbar.classList.toggle('d-flex', checked.length > 0);
}

document.getElementById('bookmarks-tbody')?.addEventListener('change', function (e) {
    if (e.target.classList.contains('bookmark-check')) updateBulkToolbar();
});

selectAllBox?.addEventListener('change', function () {
    getChecks().forEach((c) => { c.checked = selectAllBox.checked; });
    updateBulkToolbar();
});

function bulkAction(action) {
    const ids = getChecks().filter((c) => c.checked).map((c) => c.value);
    if (!ids.length) return;

    const labels = {
        launcher_on: 'marcar ' + ids.length + ' favorito(s) no Launcher',
        launcher_off: 'remover ' + ids.length + ' favorito(s) do Launcher',
        delete: 'EXCLUIR ' + ids.length + ' favorito(s)',
    };
    if (!confirm('Confirma: ' + labels[action] + '?')) return;

    const body = new URLSearchParams();
    body.set('_csrf', CSRF);
    body.set('bulk_action', action);
    ids.forEach((id) => body.append('ids[]', id));

    fetch(<?= json_encode(url('/bookmarks/bulk')) ?>, { method: 'POST', body: body })
        .then((r) => r.json())
        .then((d) => {
            if (!d.success) { alert(d.error || 'Erro ao processar ação em massa.'); return; }
            if (action === 'delete') {
                ids.forEach((id) => document.querySelector('tr[data-bookmark-id="' + id + '"]')?.remove());
            } else {
                ids.forEach((id) => {
                    const row = document.querySelector('tr[data-bookmark-id="' + id + '"]');
                    const btn = row?.querySelector('.launcher-toggle');
                    if (btn) {
                        btn.classList.toggle('btn-primary', action === 'launcher_on');
                        btn.classList.toggle('btn-outline-light', action !== 'launcher_on');
                    }
                });
            }
            selectAllBox.checked = false;
            updateBulkToolbar();
        });
}
</script>
