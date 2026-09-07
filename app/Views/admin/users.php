<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-people" style="color:var(--color-accent);"></i> Usuários</h2>
</div>

<div class="card p-3 mb-3">
    <form method="GET" action="<?= url('/admin/users') ?>" class="d-flex gap-2">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por nome ou e-mail..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-outline-light btn-sm"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="color:var(--color-text-muted);font-size:.8rem;">
                    <th class="ps-3">Nome</th>
                    <th>E-mail</th>
                    <th>Função</th>
                    <th>Créditos</th>
                    <th>Status</th>
                    <th>Cadastro</th>
                    <th class="text-end pe-3">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr data-user-id="<?= $u['id'] ?>">
                    <td class="ps-3"><?= e($u['name']) ?></td>
                    <td style="color:var(--color-text-secondary);"><?= e($u['email']) ?></td>
                    <td><span class="badge" style="background:var(--color-accent);"><?= e($u['role']) ?></span></td>
                    <td><i class="bi bi-coin" style="color:var(--color-warning);"></i> <?= (int) $u['credits'] ?></td>
                    <td>
                        <span class="badge status-badge" style="background:<?= $u['active'] ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                            <?= $u['active'] ? 'ativo' : 'inativo' ?>
                        </span>
                    </td>
                    <td style="color:var(--color-text-muted);font-size:.85rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-outline-light" onclick="toggleUser(<?= $u['id'] ?>, this)">
                            <?= $u['active'] ? 'Desativar' : 'Ativar' ?>
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
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= url('/admin/users?page=' . $p . '&q=' . urlencode($search)) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<script>
const CSRF = <?= json_encode($csrf_token) ?>;
const TOGGLE_BASE = <?= json_encode(url('/admin/users/')) ?>;

function toggleUser(id, btn) {
    fetch(TOGGLE_BASE + id + '/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(CSRF),
    }).then(r => r.json()).then(d => {
        if (!d.success) { alert(d.error || 'Erro ao alterar status.'); return; }
        var row = document.querySelector('tr[data-user-id="' + id + '"]');
        var badge = row.querySelector('.status-badge');
        badge.textContent = d.active ? 'ativo' : 'inativo';
        badge.style.background = d.active ? 'var(--color-success)' : 'var(--color-danger)';
        btn.textContent = d.active ? 'Desativar' : 'Ativar';
    });
}
</script>
