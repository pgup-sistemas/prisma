<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-grid-1x2" style="color:var(--color-accent);"></i> Hub Digital</h2>
    <a href="<?= url('/hub/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Hub
    </a>
</div>

<?php if (empty($pages)): ?>
<div class="card p-5 text-center">
    <i class="bi bi-grid-1x2" style="font-size:3rem;color:var(--color-accent);opacity:.5;"></i>
    <h5 class="mt-3" style="color:var(--color-text-secondary);">Nenhum Hub criado ainda</h5>
    <p style="color:var(--color-text-muted);">Crie sua página de links para compartilhar tudo em um só lugar.</p>
    <a href="<?= url('/hub/create') ?>" class="btn btn-primary mt-2">
        <i class="bi bi-plus-lg"></i> Criar Primeiro Hub
    </a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($pages as $page): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if (!empty($page['avatar'])): ?>
                    <img src="<?= e(storageUrl($page['avatar'])) ?>" class="rounded-circle"
                         style="width:48px;height:48px;object-fit:cover;" alt="">
                <?php else: ?>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:<?= e($page['theme_color']) ?>;font-size:1.4rem;color:#fff;">
                        <?= mb_substr($page['title'], 0, 1) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h6 class="mb-0"><?= e($page['title']) ?></h6>
                    <small style="color:var(--color-text-muted);">/hub/<?= e($page['slug']) ?></small>
                </div>
            </div>
            <div class="d-flex gap-1 flex-wrap mt-auto">
                <a href="<?= url('/hub/' . $page['uuid'] . '/edit') ?>" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="<?= url('/hub/' . $page['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-up-right"></i> Ver
                </a>
                <span class="ms-auto" style="color:var(--color-text-muted);font-size:.8rem;align-self:center;">
                    <i class="bi bi-eye"></i> <?= number_format((int)$page['view_count']) ?> views
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
