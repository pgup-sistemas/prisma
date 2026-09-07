<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0">Histórico de QR Codes</h2>
    <a href="<?= url('/generate') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Novo QR
    </a>
</div>

<!-- Filtros -->
<form method="GET" action="<?= url('/history') ?>" class="card p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-sm-6">
            <label class="form-label small" style="color:var(--color-text-secondary);">Busca</label>
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Rótulo ou conteúdo..." value="<?= e($search) ?>">
        </div>
        <div class="col-sm-4">
            <label class="form-label small" style="color:var(--color-text-secondary);">Tipo</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Todos</option>
                <?php foreach (['url','text','email','phone','sms','whatsapp','wifi','vcard','geo','event','bitcoin','pix'] as $t): ?>
                    <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= strtoupper($t) ?></option>
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

<!-- Resultados -->
<?php if (empty($qrcodes)): ?>
    <div class="card p-5 text-center">
        <i class="bi bi-qr-code display-4 mb-3" style="color:var(--color-text-muted);"></i>
        <p style="color:var(--color-text-secondary);">Nenhum QR Code encontrado.</p>
        <a href="<?= url('/generate') ?>" class="btn btn-primary btn-sm mx-auto" style="width:fit-content;">Gerar meu primeiro QR</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="color:var(--color-text-secondary);border-bottom:1px solid var(--color-border);">
                        <th>QR Code</th>
                        <th>Rótulo</th>
                        <th>Tipo</th>
                        <th>Escaneamentos</th>
                        <th>Criado em</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($qrcodes as $qr): ?>
                        <tr>
                            <td style="width:60px;">
                                <?php if ($qr['file_png']): ?>
                                    <img src="<?= e(storageUrl($qr['file_png'])) ?>"
                                         alt="QR" width="48" height="48"
                                         style="background:#fff;border-radius:4px;padding:2px;">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center"
                                         style="width:48px;height:48px;background:var(--color-elevated);border-radius:4px;">
                                        <i class="bi bi-qr-code" style="color:var(--color-text-muted);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="color:var(--color-text-primary);">
                                    <?= e($qr['label'] ?: '—') ?>
                                </div>
                                <small style="color:var(--color-text-muted);"><?= e(truncate($qr['content'], 60)) ?></small>
                            </td>
                            <td><span class="badge badge-type"><?= e($qr['type']) ?></span></td>
                            <td>
                                <span class="fw-bold" style="color:var(--color-text-primary);"><?= (int) $qr['scan_count'] ?></span>
                                <small style="color:var(--color-text-muted);"> scans</small>
                            </td>
                            <td style="color:var(--color-text-secondary);">
                                <?= e(date('d/m/Y H:i', strtotime($qr['created_at']))) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/analytics/' . $qr['uuid']) ?>"
                                   class="btn btn-sm btn-outline-light me-1" title="Analytics">
                                    <i class="bi bi-bar-chart"></i>
                                </a>
                                <a href="<?= url('/qr/' . $qr['uuid']) ?>"
                                   class="btn btn-sm btn-outline-light" title="Ver QR">
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
            <small style="color:var(--color-text-muted);"><?= $total ?> QR Codes · página <?= $page ?> de <?= $pages ?></small>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="<?= url('/history?page=' . $p . ($search ? '&q=' . urlencode($search) : '') . ($type ? '&type=' . urlencode($type) : '')) ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
