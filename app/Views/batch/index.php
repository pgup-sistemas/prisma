<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-collection" style="color:var(--color-accent);"></i> Lote de QR Codes</h2>
</div>

<div class="row g-4">
    <!-- Formulário -->
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="display-font mb-3">Gerar novo lote</h6>
            <form method="POST" action="<?= url('/batch') ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label small">Nome do lote</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Ex: Etiquetas loja centro" maxlength="120">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Tipo</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="url">URL</option>
                        <option value="text">Texto</option>
                        <option value="phone">Telefone</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Itens <small style="color:var(--color-text-muted);">(um por linha, máx. <?= (int) $maxItems ?>)</small></label>
                    <textarea name="items_text" class="form-control form-control-sm" rows="8" required
                              placeholder="https://exemplo.com/produto-1&#10;https://exemplo.com/produto-2&#10;https://exemplo.com/produto-3" style="font-family:var(--font-mono);font-size:.8rem;"></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label small">Cor</label>
                        <input type="color" name="fg_color" class="form-control form-control-color w-100" value="#000000">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Fundo</label>
                        <input type="color" name="bg_color" class="form-control form-control-color w-100" value="#FFFFFF">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">ECC</label>
                        <select name="ecc_level" class="form-select form-select-sm">
                            <option value="L">L</option>
                            <option value="M" selected>M</option>
                            <option value="Q">Q</option>
                            <option value="H">H</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Tamanho (px)</label>
                    <input type="range" name="size" class="form-range" min="128" max="1024" step="32" value="512">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-gear-wide-connected"></i> Processar lote
                </button>
            </form>
        </div>
    </div>

    <!-- Lotes anteriores -->
    <div class="col-lg-7">
        <div class="card p-0">
            <div class="p-3" style="border-bottom:1px solid var(--color-border);">
                <h6 class="display-font mb-0">Lotes gerados</h6>
            </div>
            <?php if (empty($batches)): ?>
            <div class="p-5 text-center">
                <i class="bi bi-collection" style="font-size:2.5rem;color:var(--color-accent);opacity:.5;"></i>
                <p class="mt-3 mb-0" style="color:var(--color-text-muted);">Nenhum lote gerado ainda.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr style="color:var(--color-text-muted);font-size:.8rem;">
                            <th class="ps-3">Nome</th>
                            <th>Itens</th>
                            <th>Criado em</th>
                            <th class="text-end pe-3">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batches as $b): ?>
                        <tr>
                            <td class="ps-3"><?= e($b['name']) ?></td>
                            <td><?= (int) $b['done'] ?> / <?= (int) $b['total'] ?></td>
                            <td style="color:var(--color-text-muted);font-size:.85rem;"><?= date('d/m/Y H:i', strtotime($b['created_at'])) ?></td>
                            <td class="text-end pe-3">
                                <?php if (!empty($b['zip_file'])): ?>
                                <a href="<?= url('/batch/' . $b['id'] . '/download') ?>" class="btn btn-sm btn-outline-light">
                                    <i class="bi bi-download"></i> ZIP
                                </a>
                                <?php else: ?>
                                <span class="badge" style="background:var(--color-warning);">processando</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
