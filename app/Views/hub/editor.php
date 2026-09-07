<?php $isCreate = $mode === 'create'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0">
        <i class="bi bi-grid-1x2" style="color:var(--color-accent);"></i>
        <?= $isCreate ? 'Novo Hub' : 'Editar Hub' ?>
    </h2>
    <div class="d-flex gap-2">
        <?php if (!$isCreate): ?>
        <a href="<?= url('/hub/' . $hub['slug']) ?>" target="_blank" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-up-right"></i> Ver público
        </a>
        <?php endif; ?>
        <a href="<?= url('/hub') ?>" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Configurações do Hub -->
    <div class="col-lg-5">
        <div class="card p-4 mb-4">
            <h6 class="display-font mb-3">Configurações</h6>
            <form method="POST"
                  action="<?= $isCreate ? url('/hub') : url('/hub/' . $hub['uuid']) ?>"
                  enctype="multipart/form-data"
                  id="hub-form">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
                <?php if (!$isCreate): ?>
                <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label small">Título *</label>
                    <input type="text" name="title" class="form-control form-control-sm"
                           value="<?= e($hub['title'] ?? '') ?>" required maxlength="200">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Slug (URL)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" style="background:var(--color-surface);border-color:var(--color-border);color:var(--color-text-muted);font-size:.75rem;">/hub/</span>
                        <input type="text" name="slug" class="form-control" id="slug-input"
                               value="<?= e($hub['slug'] ?? '') ?>" placeholder="meu-hub" pattern="[a-z0-9\-]+" maxlength="80">
                    </div>
                    <small style="color:var(--color-text-muted);">Apenas letras, números e hífens.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Bio / Descrição</label>
                    <textarea name="bio" class="form-control form-control-sm" rows="2" maxlength="500"><?= e($hub['bio'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Cor de destaque</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" name="theme_color" class="form-control form-control-color"
                               value="<?= e($hub['theme_color'] ?? '#2E86AB') ?>" style="width:48px;height:38px;">
                        <span style="color:var(--color-text-muted);font-size:.85rem;">Cor principal da página</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Avatar / Foto</label>
                    <input type="file" name="avatar" class="form-control form-control-sm"
                           accept="image/png,image/jpeg,image/webp">
                    <?php if (!empty($hub['avatar'])): ?>
                    <img src="<?= e(storageUrl($hub['avatar'])) ?>" class="mt-2 rounded-circle"
                         style="width:48px;height:48px;object-fit:cover;" alt="">
                    <?php endif; ?>
                </div>

                <?php if (!$isCreate): ?>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="active" class="form-check-input" id="hub-active"
                           <?= $hub['active'] ? 'checked' : '' ?>>
                    <label for="hub-active" class="form-check-label small">Hub público (visível)</label>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-check-lg"></i> <?= $isCreate ? 'Criar Hub' : 'Salvar' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Blocos -->
    <?php if (!$isCreate): ?>
    <div class="col-lg-7">
        <!-- Adicionar bloco -->
        <div class="card p-4 mb-3">
            <h6 class="display-font mb-3">Adicionar Bloco</h6>
            <div class="row g-2">
                <?php
                $blockTypes = [
                    'link'         => ['bi-link-45deg',    'Link simples'],
                    'group'        => ['bi-collection',    'Grupo de links'],
                    'whatsapp'     => ['bi-whatsapp',      'WhatsApp'],
                    'social'       => ['bi-share',         'Redes sociais'],
                    'map'          => ['bi-geo-alt',       'Mapa'],
                    'schedule'     => ['bi-clock',         'Horários'],
                    'catalog'      => ['bi-grid',          'Catálogo'],
                    'video'        => ['bi-play-circle',   'Vídeo'],
                    'contact_form' => ['bi-envelope',      'Formulário'],
                    'pix'          => ['bi-qr-code-scan',  'PIX'],
                    'agenda'       => ['bi-calendar-check', 'Agenda'],
                ];
                foreach ($blockTypes as $type => [$icon, $label]):
                ?>
                <div class="col-6 col-sm-4">
                    <button class="btn btn-outline-light btn-sm w-100 py-2" onclick="openBlockModal('<?= $type ?>')">
                        <i class="bi <?= $icon ?> d-block mb-1" style="font-size:1.2rem;color:var(--color-accent);"></i>
                        <span style="font-size:.75rem;"><?= $label ?></span>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lista de blocos -->
        <div class="card p-4" id="blocks-list">
            <h6 class="display-font mb-3">Blocos <small style="color:var(--color-text-muted);font-size:.8rem;">(arraste para reordenar)</small></h6>
            <?php if (empty($blocks)): ?>
            <p style="color:var(--color-text-muted);" class="text-center py-3">Nenhum bloco adicionado.</p>
            <?php else: ?>
            <div id="sortable-blocks">
                <?php foreach ($blocks as $block): ?>
                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" data-block-id="<?= $block['id'] ?>"
                     style="background:var(--color-elevated);border:1px solid var(--color-border);cursor:grab;">
                    <i class="bi bi-grip-vertical" style="color:var(--color-text-muted);"></i>
                    <span style="font-size:.85rem;flex:1;">
                        <strong><?= e($block['title'] ?: $block['type']) ?></strong>
                        <span style="color:var(--color-text-muted);"> · <?= $block['type'] ?></span>
                    </span>
                    <span class="badge" style="background:<?= $block['active'] ? 'var(--color-success)' : 'var(--color-danger)' ?>;font-size:.7rem;">
                        <?= $block['active'] ? 'ativo' : 'inativo' ?>
                    </span>
                    <button class="btn btn-sm btn-outline-light py-0 px-2"
                            onclick='openBlockModal(<?= json_encode($block['type']) ?>, <?= (int) $block['id'] ?>, <?= json_encode($block['title']) ?>, <?= json_encode($block['blocks_data']) ?>, <?= json_encode((bool) $block['active']) ?>)'>
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2"
                            onclick="deleteBlock(<?= $block['id'] ?>)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de adição de bloco -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--color-elevated);border-color:var(--color-border);">
            <div class="modal-header" style="border-color:var(--color-border);">
                <h5 class="modal-title display-font" id="blockModalTitle">Adicionar Bloco</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="block-form">
                <div class="modal-body" id="block-form-body">
                    <!-- Preenchido por JS -->
                </div>
                <div class="modal-footer" style="border-color:var(--color-border);">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="block-form-submit">
                        <i class="bi bi-plus-lg"></i> Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const HUB_UUID   = <?= json_encode($hub['uuid'] ?? '') ?>;
const ADD_URL    = <?= json_encode(url('/hub/' . ($hub['uuid'] ?? '') . '/blocks')) ?>;
const BLOCK_BASE = <?= json_encode(url('/hub/block/')) ?>;
const CSRF       = <?= json_encode($csrf_token) ?>;

// Gerar slug a partir do título
document.querySelector('[name=title]')?.addEventListener('input', function() {
    const slugInput = document.getElementById('slug-input');
    if (!slugInput || slugInput.dataset.manual) return;
    slugInput.value = this.value.toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g,'')
        .replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
});
document.getElementById('slug-input')?.addEventListener('input', function() {
    this.dataset.manual = '1';
});

function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

// ── Repetidor de linhas (substitui os campos "JSON" por formulário guiado) ──
const ROW_TEMPLATES = {
    links: (row) => {
        row = row || {};
        return '<div class="input-group input-group-sm mb-1" data-row>' +
            '<input type="text" class="form-control" placeholder="Rótulo" data-field="label" value="' + esc(row.label) + '">' +
            '<input type="url" class="form-control" placeholder="https://..." data-field="url" value="' + esc(row.url) + '">' +
            '<button class="btn btn-outline-danger" type="button" onclick="Repeater.removeRow(this)"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
    },
    networks: (row) => {
        row = row || {};
        const opts = ['instagram', 'facebook', 'twitter', 'tiktok', 'youtube', 'linkedin', 'whatsapp', 'outro'];
        return '<div class="input-group input-group-sm mb-1" data-row>' +
            '<select class="form-select" data-field="name" style="max-width:130px;">' +
            (row.name ? '' : '<option value="" disabled selected>Selecione...</option>') +
            opts.map((o) => '<option value="' + o + '"' + (row.name === o ? ' selected' : '') + '>' + o + '</option>').join('') +
            '</select>' +
            '<input type="url" class="form-control" placeholder="https://..." data-field="url" value="' + esc(row.url) + '"' +
            ' oninput="Repeater.detectNetwork(this)">' +
            '<button class="btn btn-outline-danger" type="button" onclick="Repeater.removeRow(this)"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
    },
    hours: (row) => {
        row = row || {};
        const days = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
        return '<div class="input-group input-group-sm mb-1" data-row>' +
            '<select class="form-select" data-field="day" style="max-width:110px;">' +
            days.map((d) => '<option' + (row.day === d ? ' selected' : '') + '>' + d + '</option>').join('') +
            '</select>' +
            '<input type="time" class="form-control" data-field="open" value="' + esc(row.open) + '">' +
            '<input type="time" class="form-control" data-field="close" value="' + esc(row.close) + '">' +
            '<button class="btn btn-outline-danger" type="button" onclick="Repeater.removeRow(this)"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
    },
    items: (row) => {
        row = row || {};
        return '<div class="border rounded p-2 mb-2" data-row style="border-color:var(--color-border) !important;">' +
            '<div class="row g-1">' +
            '<div class="col-6"><input type="text" class="form-control form-control-sm" placeholder="Nome do produto" data-field="title" value="' + esc(row.title) + '"></div>' +
            '<div class="col-6"><input type="text" class="form-control form-control-sm" placeholder="Preço (R$ 99)" data-field="price" value="' + esc(row.price) + '"></div>' +
            '<div class="col-8"><input type="url" class="form-control form-control-sm" placeholder="Link do produto (opcional)" data-field="url" value="' + esc(row.url) + '"></div>' +
            '<div class="col-4 text-end"><button class="btn btn-sm btn-outline-danger" type="button" onclick="Repeater.removeRow(this)"><i class="bi bi-x-lg"></i></button></div>' +
            '<div class="col-12"><input type="url" class="form-control form-control-sm" placeholder="URL da imagem (opcional)" data-field="img" value="' + esc(row.img) + '"></div>' +
            '</div></div>';
    },
    fields: (row) => {
        row = row || {};
        const types = ['text', 'email', 'tel', 'textarea', 'number'];
        return '<div class="input-group input-group-sm mb-1" data-row>' +
            '<input type="text" class="form-control" placeholder="Nome do campo" data-field="name" value="' + esc(row.name) + '">' +
            '<select class="form-select" data-field="type" style="max-width:110px;">' +
            types.map((t) => '<option value="' + t + '"' + (row.type === t ? ' selected' : '') + '>' + t + '</option>').join('') +
            '</select>' +
            '<span class="input-group-text" style="background:var(--color-surface);border-color:var(--color-border);">' +
            '<input type="checkbox" class="form-check-input mt-0" data-field="required" ' + (row.required ? 'checked' : '') + ' title="Obrigatório"></span>' +
            '<button class="btn btn-outline-danger" type="button" onclick="Repeater.removeRow(this)"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
    },
};

const Repeater = {
    add(id, row) {
        const container = document.getElementById(id + '-rows');
        if (!container) return;
        const wrap = document.createElement('div');
        wrap.innerHTML = ROW_TEMPLATES[id](row);
        container.appendChild(wrap.firstElementChild);
    },
    removeRow(btn) {
        btn.closest('[data-row]').remove();
    },
    detectNetwork(urlInput) {
        const hostMap = {
            'instagram.com': 'instagram', 'facebook.com': 'facebook', 'fb.com': 'facebook',
            'twitter.com': 'twitter', 'x.com': 'twitter', 'tiktok.com': 'tiktok',
            'youtube.com': 'youtube', 'youtu.be': 'youtube', 'linkedin.com': 'linkedin',
            'wa.me': 'whatsapp', 'whatsapp.com': 'whatsapp',
        };
        let host;
        try { host = new URL(urlInput.value).hostname.replace(/^www\./, ''); } catch (e) { return; }
        const network = hostMap[host];
        if (!network) return;
        const select = urlInput.closest('[data-row]')?.querySelector('[data-field="name"]');
        if (select) select.value = network;
    },
    serialize(id) {
        const container = document.getElementById(id + '-rows');
        if (!container) return [];
        return Array.from(container.querySelectorAll('[data-row]')).map((rowEl) => {
            const obj = {};
            rowEl.querySelectorAll('[data-field]').forEach((input) => {
                const key = input.getAttribute('data-field');
                obj[key] = input.type === 'checkbox' ? input.checked : input.value;
            });
            return obj;
        });
    },
};

function renderRows(id, rows) {
    return ((rows && rows.length) ? rows : []).map((r) => ROW_TEMPLATES[id](r)).join('');
}

function repeaterField(id, label, addLabel) {
    return '<div class="mb-2"><label class="form-label small">' + label + '</label>' +
        '<div id="' + id + '-rows"></div>' +
        '<button type="button" class="btn btn-sm btn-outline-light mt-1" onclick="Repeater.add(\'' + id + '\')">' +
        '<i class="bi bi-plus-lg"></i> ' + addLabel + '</button></div>';
}

function openBlockModal(type, editId, blockTitle, data, isActive) {
    data = data || {};
    isActive = editId ? !!isActive : true;
    document.getElementById('blockModalTitle').textContent = editId ? 'Editar bloco' : 'Adicionar bloco';
    document.getElementById('block-form-body').innerHTML = getBlockForm(type, data, blockTitle || '') +
        '<div class="form-check mt-2"><input type="checkbox" name="active" class="form-check-input" id="block-active" ' +
        (isActive ? 'checked' : '') + '><label for="block-active" class="form-check-label small">Bloco ativo (visível na página)</label></div>';

    // Preenche os repetidores com as linhas existentes (modo edição)
    ['links', 'networks', 'hours', 'items', 'fields'].forEach((key) => {
        const container = document.getElementById(key + '-rows');
        if (container) container.innerHTML = renderRows(key, data[key]);
    });

    const form = document.getElementById('block-form');
    form.dataset.type = type;
    if (editId) {
        form.dataset.editId = editId;
    } else {
        delete form.dataset.editId;
    }

    const submitBtn = document.getElementById('block-form-submit');
    submitBtn.innerHTML = editId
        ? '<i class="bi bi-check-lg"></i> Salvar alterações'
        : '<i class="bi bi-plus-lg"></i> Adicionar';

    new bootstrap.Modal(document.getElementById('blockModal')).show();
}

function getBlockForm(type, data, blockTitle) {
    data = data || {};
    const field = (name, label, inputType = 'text', val = '', extra = '') =>
        `<div class="mb-2"><label class="form-label small">${label}</label>
         <input type="${inputType}" name="${name}" class="form-control form-control-sm" value="${esc(val)}" ${extra}></div>`;

    const forms = {
        link: () => field('title', 'Título', 'text', blockTitle, 'required') +
            field('url', 'URL', 'url', data.url, 'required') +
            field('icon', 'Ícone Bootstrap (ex: bi-link)', 'text', data.icon),

        group: () => field('title', 'Título do Grupo', 'text', blockTitle, 'required') +
            repeaterField('links', 'Links do grupo', 'Adicionar link') +
            '<input type="hidden" name="links_json" id="links_json_hidden">',

        whatsapp: () => field('title', 'Título', 'text', blockTitle || 'Fale no WhatsApp') +
            field('phone', 'Telefone (com DDI)', 'tel', data.phone, 'required placeholder="5569912345678"') +
            `<div class="mb-2"><label class="form-label small">Mensagem pré-definida</label>
             <textarea name="message" class="form-control form-control-sm" rows="2" placeholder="Olá, vim pelo seu hub...">${esc(data.message)}</textarea></div>`,

        social: () => field('title', 'Título', 'text', blockTitle) +
            repeaterField('networks', 'Redes sociais', 'Adicionar rede') +
            '<input type="hidden" name="networks_json" id="networks_json_hidden">',

        map: () => field('title', 'Título', 'text', blockTitle) +
            field('address', 'Endereço', 'text', data.address, 'required') +
            field('lat', 'Latitude', 'text', data.lat) +
            field('lng', 'Longitude', 'text', data.lng) +
            field('zoom', 'Zoom', 'number', data.zoom || 15),

        schedule: () => field('title', 'Título', 'text', blockTitle || 'Horários de Atendimento') +
            repeaterField('hours', 'Horários', 'Adicionar horário') +
            '<input type="hidden" name="hours_json" id="hours_json_hidden">',

        catalog: () => field('title', 'Título', 'text', blockTitle || 'Nossos Produtos') +
            repeaterField('items', 'Produtos / Serviços', 'Adicionar produto') +
            '<input type="hidden" name="items_json" id="items_json_hidden">',

        video: () => field('title', 'Título', 'text', blockTitle) +
            field('video_url', 'URL YouTube / Vimeo', 'url', data.video_url, 'required') +
            `<div class="form-check mb-2"><input type="checkbox" name="autoplay" class="form-check-input" id="autoplay" ${data.autoplay ? 'checked' : ''}>
             <label for="autoplay" class="form-check-label small">Autoplay</label></div>`,

        contact_form: () => field('title', 'Título', 'text', blockTitle || 'Fale Conosco') +
            field('email_to', 'Enviar para (e-mail)', 'email', data.email_to, 'required') +
            repeaterField('fields', 'Campos do formulário', 'Adicionar campo') +
            '<input type="hidden" name="fields_json" id="fields_json_hidden">',

        pix: () => field('title', 'Título', 'text', blockTitle || 'Pagar com PIX') +
            field('pix_key', 'Chave PIX (CPF, CNPJ, e-mail, telefone ou aleatória)', 'text', data.pix_key, 'required') +
            field('merchant_name', 'Nome do recebedor (máx. 25 caracteres)', 'text', data.merchant_name, 'required maxlength="25"') +
            field('merchant_city', 'Cidade do recebedor (máx. 15 caracteres)', 'text', data.merchant_city, 'required maxlength="15"') +
            field('description', 'Descrição (opcional)', 'text', data.description) +
            field('fixed_amount', 'Valor fixo em R$ (deixe em branco para valor livre)', 'number', data.fixed_amount, 'step="0.01" min="0"') +
            `<div class="form-check mb-2"><input type="checkbox" name="allow_custom_amount" class="form-check-input" id="pix-custom-amount" ${data.allow_custom_amount ? 'checked' : ''}>
             <label for="pix-custom-amount" class="form-check-label small">Permitir que o visitante digite o valor a pagar</label></div>
             <p class="small mb-0" style="color:var(--color-text-muted);">Se marcado, o visitante escolhe o valor no momento do pagamento (o valor fixo acima vira só uma sugestão inicial). Se desmarcado e sem valor fixo, o PIX é gerado sem valor definido — quem pagar digita no app do banco.</p>`,

        agenda: () => field('title', 'Título', 'text', blockTitle || 'Agendar horário') +
            field('duration_minutes', 'Duração de cada atendimento (minutos)', 'number', data.duration_minutes || 30, 'required min="5" step="5"') +
            repeaterField('hours', 'Dias e horários disponíveis', 'Adicionar dia') +
            '<input type="hidden" name="hours_json" id="hours_json_hidden">' +
            '<p class="small mb-0" style="color:var(--color-text-muted);">Ex: Segunda 08:00–18:00. Os horários de agendamento são gerados automaticamente dentro dessa faixa, conforme a duração escolhida acima.</p>',
    };

    return (forms[type] || (() => '<p>Tipo não reconhecido.</p>'))();
}

document.getElementById('block-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const type = this.dataset.type;
    const editId = this.dataset.editId;

    // Monta o JSON de cada repetidor presente no formulário atual
    ['links', 'networks', 'hours', 'items', 'fields'].forEach((key) => {
        const hidden = document.getElementById(key + '_json_hidden');
        if (hidden) hidden.value = JSON.stringify(Repeater.serialize(key));
    });

    if (editId) {
        // PUT não suporta multipart neste framework — envia como urlencoded (não há upload de arquivo aqui)
        const params = new URLSearchParams(new FormData(this));
        params.set('_csrf', CSRF);
        fetch(BLOCK_BASE + editId, { method: 'PUT', body: params })
            .then((r) => r.json())
            .then((d) => {
                if (d.success) location.reload();
                else alert(d.error || 'Erro ao salvar bloco.');
            });
        return;
    }

    const fd = new FormData(this);
    fd.append('_csrf', CSRF);
    fd.append('type', type);

    fetch(ADD_URL, { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((d) => {
            if (d.success) location.reload();
            else alert(d.error || 'Erro ao adicionar bloco.');
        });
});

function deleteBlock(id) {
    if (!confirm('Excluir este bloco?')) return;
    fetch(BLOCK_BASE + id, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(CSRF),
    }).then(() => location.reload());
}
</script>
