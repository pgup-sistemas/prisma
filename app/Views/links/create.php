<?php $editing = $link !== null; $lk = $link ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><?= $editing ? 'Editar Link' : 'Novo Link' ?></h2>
    <a href="<?= url('/links') ?>" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card p-4" style="max-width:720px;">
    <form id="link-form" method="POST" action="<?= url('/links') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="uuid" value="<?= e($link['uuid']) ?>">
        <?php endif; ?>

        <!-- Destino + Slug -->
        <div class="mb-3">
            <label class="form-label">URL de Destino <span class="text-danger">*</span></label>
            <input type="url" name="destination" class="form-control"
                   placeholder="https://exemplo.com/pagina"
                   value="<?= e($link['destination'] ?? '') ?>" required>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label">Slug personalizado</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--color-elevated);color:var(--color-text-muted);border-color:var(--color-border);">/r/</span>
                    <input type="text" name="slug" class="form-control"
                           placeholder="meu-link (deixe vazio para gerar)"
                           value="<?= e($link['slug'] ?? '') ?>"
                           pattern="[a-zA-Z0-9_\-]+" title="Apenas letras, números, traços e underscores"
                           <?= $editing ? 'readonly' : '' ?>>
                </div>
                <?php if ($editing): ?>
                    <small style="color:var(--color-text-muted);">Slug não pode ser alterado após criação.</small>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label class="form-label">Título (opcional)</label>
                <input type="text" name="title" class="form-control"
                       placeholder="Nome interno do link"
                       value="<?= e($link['title'] ?? '') ?>">
            </div>
        </div>

        <!-- Tipo de encapsulador -->
        <div class="mb-3">
            <label class="form-label">Tipo de Encapsulador</label>
            <select name="wrapper_type" id="wrapper_type" class="form-select">
                <?php
                $types = [
                    'none'        => 'Nenhum — Redirect direto (302)',
                    'utm'         => 'UTM — Injeta parâmetros UTM na URL',
                    'intersticial'=> 'Intersticial — Página com countdown de 5s',
                    'conditional' => 'Condicional — Redireciona por device/país/hora',
                    'ab'          => 'A/B Split — Divide tráfego entre variantes',
                    'cloaking'    => 'Cloaking — Abre destino em iframe',
                ];
                $current = $lk['wrapper_type'] ?? 'none';
                foreach ($types as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- UTM fields -->
        <div id="section-utm" class="wrapper-section card p-3 mb-3" style="display:none;">
            <h6 class="display-font mb-3">Parâmetros UTM</h6>
            <?php
            $utm = [];
            if (!empty($lk['utm_json'])) $utm = json_decode($lk['utm_json'], true) ?? [];
            $utmFields = ['source'=>'Source *','medium'=>'Medium','campaign'=>'Campaign','term'=>'Term','content'=>'Content'];
            ?>
            <div class="row g-2">
                <?php foreach ($utmFields as $key => $label): ?>
                    <div class="col-sm-6">
                        <label class="form-label small"><?= $label ?></label>
                        <input type="text" name="utm_<?= $key ?>" class="form-control form-control-sm"
                               placeholder="<?= $key ?>" value="<?= e($utm[$key] ?? '') ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Conditional fields -->
        <div id="section-conditional" class="wrapper-section card p-3 mb-3" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="display-font mb-0">Condições de Redirecionamento</h6>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="addCondition()">
                    <i class="bi bi-plus"></i> Adicionar
                </button>
            </div>
            <div id="conditions-list"></div>
            <input type="hidden" name="conditions_json" id="conditions_json"
                   value="<?= e($link['conditions_json'] ?? '[]') ?>">
            <small style="color:var(--color-text-muted);">
                Primeira condição que corresponder é usada. Se nenhuma corresponder, usa URL de destino principal.
            </small>
        </div>

        <!-- A/B fields -->
        <div id="section-ab" class="wrapper-section card p-3 mb-3" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="display-font mb-0">Variantes A/B <small style="color:var(--color-text-muted);">(pesos devem somar 100)</small></h6>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="addVariant()">
                    <i class="bi bi-plus"></i> Variante
                </button>
            </div>
            <div id="variants-list"></div>
            <input type="hidden" name="ab_variants" id="ab_variants"
                   value="<?= e($link['ab_variants'] ?? '[]') ?>">
        </div>

        <!-- Expiração -->
        <div class="mb-4">
            <label class="form-label">Expiração (opcional)</label>
            <input type="datetime-local" name="expires_at" class="form-control"
                   value="<?= e(!empty($link['expires_at']) ? date('Y-m-d\TH:i', strtotime($link['expires_at'])) : '') ?>">
            <small style="color:var(--color-text-muted);">Após essa data o link retorna 410 Gone.</small>
        </div>

        <?php if ($editing): ?>
            <!-- Status ativo/inativo -->
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                           <?= ($link['active'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active">Link ativo</label>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <?php if ($editing): ?>
                <button type="button" class="btn btn-primary" onclick="submitUpdate()">
                    <i class="bi bi-check-lg"></i> Salvar alterações
                </button>
                <button type="button" class="btn btn-outline-danger ms-auto" onclick="confirmDelete()">
                    <i class="bi bi-trash"></i> Excluir
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-link-45deg"></i> Criar Link
                </button>
            <?php endif; ?>
            <a href="<?= url('/links') ?>" class="btn btn-outline-light">Cancelar</a>
        </div>
    </form>
</div>

<script>
(function () {
    // ── Mostrar/ocultar seções por tipo ──────────────────────────────
    const wrapperSelect = document.getElementById('wrapper_type');

    function toggleSections() {
        document.querySelectorAll('.wrapper-section').forEach(el => el.style.display = 'none');
        const selected = wrapperSelect.value;
        const section = document.getElementById('section-' + selected);
        if (section) section.style.display = 'block';
    }

    wrapperSelect.addEventListener('change', toggleSections);
    toggleSections();

    // ── Condicional: gerenciar condições ─────────────────────────────
    let conditions = JSON.parse(document.getElementById('conditions_json').value || '[]');

    function renderConditions() {
        const list = document.getElementById('conditions-list');
        list.innerHTML = '';
        conditions.forEach((c, i) => {
            list.insertAdjacentHTML('beforeend', `
                <div class="d-flex gap-2 mb-2 align-items-center">
                    <select class="form-select form-select-sm" onchange="conditions[${i}].field=this.value;syncConditions()">
                        <option value="device"  ${c.field==='device'  ?'selected':''}>Dispositivo</option>
                        <option value="country" ${c.field==='country' ?'selected':''}>País (ISO-2)</option>
                        <option value="hour"    ${c.field==='hour'    ?'selected':''}>Hora</option>
                    </select>
                    <select class="form-select form-select-sm" style="max-width:110px;" onchange="conditions[${i}].operator=this.value;syncConditions()">
                        <option value="eq"      ${c.operator==='eq'     ?'selected':''}>= igual</option>
                        <option value="neq"     ${c.operator==='neq'    ?'selected':''}>≠ diferente</option>
                        <option value="in"      ${c.operator==='in'     ?'selected':''}>está em</option>
                        <option value="between" ${c.operator==='between'?'selected':''}>entre</option>
                    </select>
                    <input type="text" class="form-control form-control-sm"
                           placeholder="valor (ex: mobile, BR, 8)" value="${Array.isArray(c.value)?c.value.join(','):c.value||''}"
                           onchange="conditions[${i}].value=this.value.includes(',')?this.value.split(','):this.value;syncConditions()">
                    <input type="text" class="form-control form-control-sm"
                           placeholder="URL de destino" value="${c.destination||''}"
                           onchange="conditions[${i}].destination=this.value;syncConditions()">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="conditions.splice(${i},1);renderConditions();syncConditions()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>`);
        });
    }

    function syncConditions() {
        document.getElementById('conditions_json').value = JSON.stringify(conditions);
    }

    window.addCondition = function () {
        conditions.push({field:'device',operator:'eq',value:'',destination:''});
        renderConditions();
    };

    renderConditions();

    // ── A/B: gerenciar variantes ──────────────────────────────────────
    let variants = JSON.parse(document.getElementById('ab_variants').value || '[]');
    if (variants.length === 0) variants = [{url:'',weight:50},{url:'',weight:50}];

    function renderVariants() {
        const list = document.getElementById('variants-list');
        list.innerHTML = '';
        variants.forEach((v, i) => {
            list.insertAdjacentHTML('beforeend', `
                <div class="row g-2 mb-2">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm"
                               placeholder="URL variante ${String.fromCharCode(65+i)}"
                               value="${v.url||''}"
                               onchange="variants[${i}].url=this.value;syncVariants()">
                    </div>
                    <div class="col-3">
                        <div class="input-group input-group-sm">
                            <input type="number" min="1" max="100" class="form-control"
                                   placeholder="%" value="${v.weight||0}"
                                   onchange="variants[${i}].weight=parseInt(this.value)||0;syncVariants()">
                            <span class="input-group-text" style="background:var(--color-elevated);border-color:var(--color-border);">%</span>
                        </div>
                    </div>
                    ${variants.length > 2 ? `<div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger" onclick="variants.splice(${i},1);renderVariants();syncVariants()"><i class="bi bi-x"></i></button></div>` : ''}
                </div>`);
        });
    }

    function syncVariants() {
        document.getElementById('ab_variants').value = JSON.stringify(variants);
    }

    window.addVariant = function () {
        variants.push({url:'',weight:0});
        renderVariants();
    };

    renderVariants();

    // ── Update/Delete (para modo edição) ─────────────────────────────
    <?php if ($editing): ?>
    const uuid = <?= json_encode($link['uuid']) ?>;
    const csrf = <?= json_encode($csrf_token) ?>;

    window.submitUpdate = function () {
        const form = document.getElementById('link-form');
        const data = new URLSearchParams();
        new FormData(form).forEach((v, k) => data.append(k, v));
        data.set('active', document.getElementById('active')?.checked ? '1' : '0');

        fetch(<?= json_encode(url('/links/' . $link['uuid'])) ?>, {
            method: 'PUT',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: data.toString()
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) window.location.href = res.redirect;
            else alert(res.error || 'Erro ao salvar.');
        });
    };

    window.confirmDelete = function () {
        if (!confirm('Excluir este link permanentemente?')) return;
        fetch(<?= json_encode(url('/links/' . $link['uuid'])) ?>, {
            method: 'DELETE',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: '_csrf=' + encodeURIComponent(csrf)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) window.location.href = <?= json_encode(url('/links')) ?>;
        });
    };
    <?php endif; ?>
})();
</script>
