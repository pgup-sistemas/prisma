<?php
$themeColor = e($hub['theme_color'] ?? '#2E86AB');

// Blocos "pesados" (podem ter várias linhas/itens/iframe) ficam dentro de um
// cartão recolhível — só o título fica visível até o visitante clicar, pra a
// página não ficar gigante quando o Hub tem muitos blocos.
$collapsibleDefaults = [
    'group'        => ['bi-collection',   'Links'],
    'map'          => ['bi-geo-alt',      'Localização'],
    'schedule'     => ['bi-clock',        'Horários'],
    'catalog'      => ['bi-grid',         'Produtos'],
    'video'        => ['bi-play-circle',  'Vídeo'],
    'contact_form' => ['bi-envelope',     'Contato'],
    'pix'          => ['bi-qr-code-scan', 'Pagar com PIX'],
    'agenda'       => ['bi-calendar-check', 'Agendar horário'],
];
?>
<style>
    .hub-block-toggle[aria-expanded="true"] .hub-block-chevron { transform: rotate(180deg); }
    .hub-block-chevron { transition: transform .2s ease; }
</style>
<div class="container py-5" style="max-width:600px;">
    <!-- Cabeçalho do Hub -->
    <div class="text-center mb-5">
        <?php if (!empty($hub['avatar'])): ?>
            <img src="<?= e(storageUrl($hub['avatar'])) ?>" class="rounded-circle mb-3"
                 style="width:96px;height:96px;object-fit:cover;border:3px solid <?= $themeColor ?>;" alt="">
        <?php else: ?>
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:96px;height:96px;background:<?= $themeColor ?>;font-size:2.5rem;color:#fff;">
                <?= mb_substr($hub['title'], 0, 1) ?>
            </div>
        <?php endif; ?>
        <h1 class="display-font mb-1" style="font-size:1.8rem;"><?= e($hub['title']) ?></h1>
        <?php if (!empty($hub['bio'])): ?>
            <p style="color:var(--color-text-secondary);max-width:400px;margin:0 auto;"><?= nl2br(e($hub['bio'])) ?></p>
        <?php endif; ?>
        <div class="spectrum-bar mt-3 rounded"></div>
    </div>

    <!-- Blocos -->
    <?php foreach ($blocks as $block):
        if (!$block['active']) continue;
        $data = is_array($block['blocks_json']) ? $block['blocks_json'] : (json_decode($block['blocks_json'] ?? '{}', true) ?: []);
    ?>
    <div class="mb-3">

    <?php if ($block['type'] === 'link'): ?>
        <a href="<?= e($data['url'] ?? '#') ?>" target="_blank" rel="noopener"
           class="d-flex align-items-center gap-3 card p-3 text-decoration-none"
           style="border-left:4px solid <?= $themeColor ?>;">
            <?php if (!empty($data['icon'])): ?>
                <i class="bi <?= e($data['icon']) ?>" style="font-size:1.4rem;color:<?= $themeColor ?>;"></i>
            <?php endif; ?>
            <span style="font-size:1rem;font-weight:500;color:var(--color-text-primary);"><?= e($block['title'] ?: ($data['label'] ?? 'Link')) ?></span>
            <i class="bi bi-arrow-right ms-auto" style="color:var(--color-text-muted);"></i>
        </a>

    <?php elseif ($block['type'] === 'whatsapp'): ?>
        <?php $phone = preg_replace('/\D/', '', $data['phone'] ?? ''); $msg = urlencode($data['message'] ?? ''); ?>
        <a href="https://wa.me/<?= $phone ?>?text=<?= $msg ?>" target="_blank" rel="noopener"
           class="d-flex align-items-center justify-content-center gap-2 card p-3 text-decoration-none"
           style="background:linear-gradient(135deg,#25D366,#128C7E);border:none;color:#fff;">
            <i class="bi bi-whatsapp" style="font-size:1.4rem;"></i>
            <span style="font-size:1rem;font-weight:500;"><?= e($block['title'] ?: 'Fale no WhatsApp') ?></span>
        </a>

    <?php elseif ($block['type'] === 'social'): ?>
        <?php
        $iconMap = ['instagram'=>'bi-instagram','facebook'=>'bi-facebook','twitter'=>'bi-twitter-x','youtube'=>'bi-youtube','tiktok'=>'bi-tiktok','linkedin'=>'bi-linkedin','github'=>'bi-github','telegram'=>'bi-telegram','pinterest'=>'bi-pinterest','spotify'=>'bi-spotify','whatsapp'=>'bi-whatsapp'];
        $hostMap = [
            'instagram.com' => 'instagram', 'facebook.com' => 'facebook', 'fb.com' => 'facebook',
            'twitter.com' => 'twitter', 'x.com' => 'twitter', 'tiktok.com' => 'tiktok',
            'youtube.com' => 'youtube', 'youtu.be' => 'youtube', 'linkedin.com' => 'linkedin',
            'github.com' => 'github', 't.me' => 'telegram', 'telegram.me' => 'telegram',
            'pinterest.com' => 'pinterest', 'open.spotify.com' => 'spotify',
            'wa.me' => 'whatsapp', 'whatsapp.com' => 'whatsapp',
        ];
        $detectNetwork = function (string $url, string $fallback) use ($hostMap): string {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $host = preg_replace('/^www\./', '', $host);
            return $hostMap[$host] ?? $fallback;
        };
        $networks = $data['networks'] ?? [];
        ?>
        <div class="card p-0">
            <?php $netCount = count($networks); foreach ($networks as $i => $net):
                $network = $detectNetwork((string) ($net['url'] ?? ''), (string) ($net['name'] ?? ''));
                $icon = $iconMap[$network] ?? 'bi-globe';
                $label = $block['title'] ?: ucfirst($network);
            ?>
                <a href="<?= e($net['url']) ?>" target="_blank" rel="noopener"
                   class="d-flex align-items-center gap-2 p-3 text-decoration-none"
                   style="color:var(--color-text-primary);<?= $i < $netCount - 1 ? 'border-bottom:1px solid var(--color-border);' : '' ?>">
                    <i class="bi <?= $icon ?>" style="color:<?= $themeColor ?>;font-size:1.1rem;"></i>
                    <span style="font-weight:500;flex:1;"><?= e($label) ?></span>
                    <i class="bi bi-arrow-right" style="color:var(--color-text-muted);"></i>
                </a>
            <?php endforeach; ?>
        </div>

    <?php elseif (isset($collapsibleDefaults[$block['type']])):
        [$blockIcon, $blockDefaultLabel] = $collapsibleDefaults[$block['type']];
        $collapseId = 'hub-block-' . (int) $block['id'];
    ?>
        <div class="card p-0">
            <button type="button" class="btn hub-block-toggle w-100 d-flex align-items-center gap-2 p-3 text-decoration-none"
                    data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>"
                    <?= $block['type'] === 'agenda' ? 'onclick="initAgendaDates(' . (int) $block['id'] . ', \'agenda-' . (int) $block['id'] . '\')"' : '' ?>
                    style="background:transparent;border:none;color:var(--color-text-primary);text-align:left;">
                <i class="bi <?= $blockIcon ?>" style="color:<?= $themeColor ?>;font-size:1.1rem;"></i>
                <span style="font-weight:500;flex:1;"><?= e($block['title'] ?: $blockDefaultLabel) ?></span>
                <i class="bi bi-chevron-down hub-block-chevron" style="color:var(--color-text-muted);"></i>
            </button>
            <div class="collapse" id="<?= $collapseId ?>">
                <div class="px-3 pb-3">

    <?php if ($block['type'] === 'group'): ?>
        <?php $links = $data['links'] ?? []; foreach ($links as $lnk): ?>
        <a href="<?= e($lnk['url'] ?? '#') ?>" target="_blank" rel="noopener"
           class="d-flex align-items-center gap-2 text-decoration-none py-2"
           style="border-bottom:1px solid var(--color-border);color:var(--color-text-secondary);">
            <i class="bi bi-link-45deg" style="color:<?= $themeColor ?>;"></i>
            <?= e($lnk['label'] ?? $lnk['url']) ?>
            <i class="bi bi-chevron-right ms-auto" style="font-size:.75rem;"></i>
        </a>
        <?php endforeach; ?>

    <?php elseif ($block['type'] === 'map'): ?>
        <div class="ratio ratio-16x9 rounded overflow-hidden">
            <iframe
                src="https://maps.google.com/maps?q=<?= urlencode($data['address'] ?? '') ?>&output=embed&z=<?= (int)($data['zoom'] ?? 15) ?>"
                style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        <?php if (!empty($data['address'])): ?>
            <p class="mt-2 mb-0" style="color:var(--color-text-muted);font-size:.85rem;"><i class="bi bi-geo-alt"></i> <?= e($data['address']) ?></p>
        <?php endif; ?>

    <?php elseif ($block['type'] === 'schedule'): ?>
        <?php foreach (($data['hours'] ?? []) as $h): ?>
        <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid var(--color-border);">
            <span style="color:var(--color-text-secondary);"><?= e($h['day'] ?? '') ?></span>
            <span style="color:var(--color-text-primary);"><?= e($h['open'] ?? '') ?> – <?= e($h['close'] ?? '') ?></span>
        </div>
        <?php endforeach; ?>

    <?php elseif ($block['type'] === 'catalog'): ?>
        <div class="row g-2">
        <?php foreach (($data['items'] ?? []) as $item): ?>
            <div class="col-6 col-sm-4">
                <div class="card p-2 h-100 text-center" style="border-color:var(--color-border);">
                    <?php if (!empty($item['img'])): ?>
                        <img src="<?= e($item['img']) ?>" class="rounded mb-2" style="width:100%;height:80px;object-fit:cover;" alt="">
                    <?php endif; ?>
                    <div style="font-size:.85rem;font-weight:500;color:var(--color-text-primary);"><?= e($item['title'] ?? '') ?></div>
                    <?php if (!empty($item['price'])): ?>
                        <div style="color:<?= $themeColor ?>;font-weight:600;"><?= e($item['price']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($item['url'])): ?>
                        <a href="<?= e($item['url']) ?>" target="_blank" class="btn btn-sm mt-1" style="background:<?= $themeColor ?>;color:#fff;font-size:.75rem;">Ver</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    <?php elseif ($block['type'] === 'video'): ?>
        <?php
        $vurl = $data['video_url'] ?? '';
        $embed = '';
        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $vurl, $m) || preg_match('/youtu\.be\/([^?]+)/', $vurl, $m)) {
            $ap = ($data['autoplay'] ?? false) ? '?autoplay=1' : '';
            $embed = "https://www.youtube.com/embed/{$m[1]}{$ap}";
        } elseif (preg_match('/vimeo\.com\/(\d+)/', $vurl, $m)) {
            $embed = "https://player.vimeo.com/video/{$m[1]}";
        }
        ?>
        <?php if ($embed): ?>
        <div class="ratio ratio-16x9 rounded overflow-hidden">
            <iframe src="<?= e($embed) ?>" allowfullscreen></iframe>
        </div>
        <?php else: ?>
        <a href="<?= e($vurl) ?>" target="_blank" class="btn btn-sm btn-outline-light"><i class="bi bi-play-circle"></i> Ver vídeo</a>
        <?php endif; ?>

    <?php elseif ($block['type'] === 'contact_form'): $formId = 'contact-' . (int) $block['id']; ?>
        <form id="<?= $formId ?>" onsubmit="return submitHubContact(event, <?= (int) $block['id'] ?>, '<?= $formId ?>')">
            <?php foreach (($data['fields'] ?? []) as $field): ?>
            <div class="mb-2">
                <label class="form-label small"><?= e(ucfirst($field['name'] ?? '')) ?></label>
                <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                    <textarea name="<?= e($field['name']) ?>" class="form-control form-control-sm" rows="3" <?= ($field['required'] ?? false) ? 'required' : '' ?>></textarea>
                <?php else: ?>
                    <input type="<?= e($field['type'] ?? 'text') ?>" name="<?= e($field['name']) ?>" class="form-control form-control-sm" <?= ($field['required'] ?? false) ? 'required' : '' ?>>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-sm mt-1" style="background:<?= $themeColor ?>;color:#fff;">Enviar</button>
            <div id="<?= $formId ?>-feedback" class="small mt-2"></div>
        </form>

    <?php elseif ($block['type'] === 'pix'): $pixId = 'pix-' . (int) $block['id']; ?>
        <div id="<?= $pixId ?>-form">
            <?php if (!empty($data['allow_custom_amount'])): ?>
                <div class="mb-2">
                    <label class="form-label small">Valor a pagar (R$)</label>
                    <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" id="<?= $pixId ?>-amount"
                           placeholder="0,00" value="<?= !empty($data['fixed_amount']) ? e($data['fixed_amount']) : '' ?>">
                </div>
            <?php endif; ?>

            <button type="button" class="btn btn-sm w-100" style="background:<?= $themeColor ?>;color:#fff;"
                    onclick="generatePix(<?= (int) $block['id'] ?>, '<?= $pixId ?>')">
                <i class="bi bi-qr-code"></i> Gerar código PIX
            </button>
            <div id="<?= $pixId ?>-error" class="text-danger small mt-2"></div>
        </div>

        <div id="<?= $pixId ?>-result" class="d-none text-center mt-2">
            <img id="<?= $pixId ?>-img" class="img-fluid rounded mb-2" style="max-width:220px;" alt="QR Code PIX">
            <div class="input-group input-group-sm">
                <input type="text" id="<?= $pixId ?>-copypaste" class="form-control" readonly style="font-family:var(--font-mono);font-size:.7rem;">
                <button class="btn btn-outline-light" type="button" onclick="copyPix('<?= $pixId ?>')"><i class="bi bi-clipboard"></i></button>
            </div>

            <?php if (!empty($data['description'])): ?>
                <p class="mt-2 mb-0" style="color:var(--color-text-secondary);font-size:.9rem;"><?= e($data['description']) ?></p>
            <?php endif; ?>

            <?php if (empty($data['allow_custom_amount']) && !empty($data['fixed_amount'])): ?>
                <p class="mt-1 mb-0" style="font-weight:600;color:<?= $themeColor ?>;font-size:1.1rem;">
                    R$ <?= number_format((float) $data['fixed_amount'], 2, ',', '.') ?>
                </p>
            <?php elseif (empty($data['allow_custom_amount'])): ?>
                <p class="mt-1 mb-0" style="color:var(--color-text-muted);font-size:.85rem;">Valor livre — você digita no app do banco.</p>
            <?php endif; ?>

            <small class="d-block mt-2" style="color:var(--color-text-muted);">Escaneie o QR Code ou copie o código e cole no app do seu banco.</small>
        </div>

    <?php elseif ($block['type'] === 'agenda'):
        $agendaId = 'agenda-' . (int) $block['id'];
        $configuredDays = array_values(array_unique(array_column($data['hours'] ?? [], 'day')));
    ?>
        <div id="<?= $agendaId ?>-step1" data-days='<?= json_encode($configuredDays) ?>'>
            <label class="form-label small">Escolha uma data</label>
            <div id="<?= $agendaId ?>-dates" class="d-flex gap-2 flex-nowrap overflow-auto pb-2 mb-1"></div>
            <div id="<?= $agendaId ?>-slots" class="d-flex flex-wrap gap-2"></div>
        </div>

        <div id="<?= $agendaId ?>-step2" class="d-none mt-2">
            <p class="small mb-2">
                Horário selecionado: <strong id="<?= $agendaId ?>-selected-label"></strong>
                <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="resetAgendaSelection('<?= $agendaId ?>')">trocar</button>
            </p>
            <div class="mb-2">
                <label class="form-label small">Seu nome</label>
                <input type="text" class="form-control form-control-sm" id="<?= $agendaId ?>-name">
            </div>
            <div class="mb-2">
                <label class="form-label small">Telefone ou e-mail</label>
                <input type="text" class="form-control form-control-sm" id="<?= $agendaId ?>-contact">
            </div>
            <div class="mb-2">
                <label class="form-label small">Observações (opcional)</label>
                <textarea class="form-control form-control-sm" id="<?= $agendaId ?>-notes" rows="2"></textarea>
            </div>
            <button type="button" class="btn btn-sm w-100" style="background:<?= $themeColor ?>;color:#fff;"
                    onclick="confirmAgendaBooking(<?= (int) $block['id'] ?>, '<?= $agendaId ?>')">
                Confirmar agendamento
            </button>
        </div>

        <div id="<?= $agendaId ?>-success" class="d-none small mt-2" style="color:var(--color-success);"></div>
        <div id="<?= $agendaId ?>-error" class="text-danger small mt-2"></div>

    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
var HUB_BASE = <?= json_encode(url('/hub/' . $hub['slug'])) ?>;

function generatePix(blockId, pixId) {
    var amountInput = document.getElementById(pixId + '-amount');
    var errorEl = document.getElementById(pixId + '-error');
    errorEl.textContent = '';

    var body = new URLSearchParams();
    if (amountInput && amountInput.value) body.set('amount', amountInput.value);

    fetch(<?= json_encode(url('/hub/' . $hub['slug'] . '/pix/')) ?> + blockId, { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) { errorEl.textContent = d.error || 'Erro ao gerar PIX.'; return; }
            document.getElementById(pixId + '-img').src = d.qr_image;
            document.getElementById(pixId + '-copypaste').value = d.copy_paste;
            document.getElementById(pixId + '-result').classList.remove('d-none');
        })
        .catch(function () { errorEl.textContent = 'Erro de conexão.'; });
}

function copyPix(pixId) {
    var el = document.getElementById(pixId + '-copypaste');
    el.select();
    navigator.clipboard.writeText(el.value);
}

function submitHubContact(evt, blockId, formId) {
    evt.preventDefault();
    var form = document.getElementById(formId);
    var feedback = document.getElementById(formId + '-feedback');
    var submitBtn = form.querySelector('button[type="submit"]');

    feedback.textContent = '';
    feedback.style.color = '';
    submitBtn.disabled = true;

    var body = new URLSearchParams(new FormData(form));
    body.set('block_id', blockId);

    fetch(<?= json_encode(url('/hub/' . $hub['slug'] . '/contact')) ?>, { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            submitBtn.disabled = false;
            feedback.style.color = d.success ? 'var(--color-success)' : 'var(--color-danger)';
            feedback.textContent = d.success ? d.message : (d.error || 'Erro ao enviar.');
            if (d.success) form.reset();
        })
        .catch(function () {
            submitBtn.disabled = false;
            feedback.style.color = 'var(--color-danger)';
            feedback.textContent = 'Erro de conexão.';
        });

    return false;
}

// ── Agenda ───────────────────────────────────────────────────────────────
var AGENDA_DAY_INDEX = { 'Domingo': 0, 'Segunda': 1, 'Terça': 2, 'Quarta': 3, 'Quinta': 4, 'Sexta': 5, 'Sábado': 6 };
var AGENDA_WEEKDAY_SHORT = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

function initAgendaDates(blockId, agendaId) {
    var step1 = document.getElementById(agendaId + '-step1');
    if (step1.dataset.initialized) return;
    step1.dataset.initialized = '1';

    var configuredDays = JSON.parse(step1.dataset.days || '[]');
    var validIndexes = configuredDays.map(function (d) { return AGENDA_DAY_INDEX[d]; }).filter(function (i) { return i !== undefined; });

    var datesEl = document.getElementById(agendaId + '-dates');
    if (!validIndexes.length) {
        datesEl.innerHTML = '<span class="small" style="color:var(--color-text-muted);">Nenhum dia disponível configurado.</span>';
        return;
    }

    var dates = [];
    var today = new Date();
    for (var i = 0; dates.length < 7 && i < 90; i++) {
        var d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
        if (validIndexes.indexOf(d.getDay()) !== -1) dates.push(d);
    }

    if (!dates.length) {
        datesEl.innerHTML = '<span class="small" style="color:var(--color-text-muted);">Nenhuma data disponível nos próximos 90 dias.</span>';
        return;
    }

    datesEl.innerHTML = dates.map(function (d, idx) {
        var iso = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        return '<button type="button" class="btn btn-sm ' + (idx === 0 ? 'btn-primary' : 'btn-outline-light') + ' flex-shrink-0" ' +
            'data-date="' + iso + '" onclick="selectAgendaDate(' + blockId + ',\'' + agendaId + '\',\'' + iso + '\',this)">' +
            AGENDA_WEEKDAY_SHORT[d.getDay()] + '<br>' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') +
            '</button>';
    }).join('');

    var firstIso = dates[0].getFullYear() + '-' + String(dates[0].getMonth() + 1).padStart(2, '0') + '-' + String(dates[0].getDate()).padStart(2, '0');
    loadAgendaSlots(blockId, agendaId, firstIso);
}

function selectAgendaDate(blockId, agendaId, date, btn) {
    var datesEl = document.getElementById(agendaId + '-dates');
    Array.prototype.forEach.call(datesEl.querySelectorAll('button'), function (b) {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-light');
    });
    btn.classList.remove('btn-outline-light');
    btn.classList.add('btn-primary');
    loadAgendaSlots(blockId, agendaId, date);
}

function loadAgendaSlots(blockId, agendaId, date) {
    var slotsEl = document.getElementById(agendaId + '-slots');
    var errorEl = document.getElementById(agendaId + '-error');
    errorEl.textContent = '';

    slotsEl.innerHTML = '<span class="small" style="color:var(--color-text-muted);">Carregando horários…</span>';

    fetch(HUB_BASE + '/agenda/' + blockId + '/slots?date=' + encodeURIComponent(date))
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) { slotsEl.innerHTML = ''; errorEl.textContent = d.error || 'Erro ao buscar horários.'; return; }
            if (!d.slots.length) {
                slotsEl.innerHTML = '<span class="small" style="color:var(--color-text-muted);">Nenhum horário disponível nesse dia.</span>';
                return;
            }
            slotsEl.innerHTML = d.slots.map(function (s) {
                return '<button type="button" class="btn btn-sm btn-outline-light" onclick="selectAgendaSlot(\'' + agendaId + '\',\'' + date + '\',\'' + s + '\')">' + s + '</button>';
            }).join('');
        })
        .catch(function () { errorEl.textContent = 'Erro de conexão.'; });
}

function selectAgendaSlot(agendaId, date, slot) {
    var step2 = document.getElementById(agendaId + '-step2');
    step2.dataset.date = date;
    step2.dataset.slot = slot;

    var dateObj = new Date(date + 'T00:00:00');
    document.getElementById(agendaId + '-selected-label').textContent =
        dateObj.toLocaleDateString('pt-BR') + ' às ' + slot;

    document.getElementById(agendaId + '-step1').classList.add('d-none');
    step2.classList.remove('d-none');
}

function resetAgendaSelection(agendaId) {
    document.getElementById(agendaId + '-step2').classList.add('d-none');
    document.getElementById(agendaId + '-step1').classList.remove('d-none');
    document.getElementById(agendaId + '-error').textContent = '';
}

function confirmAgendaBooking(blockId, agendaId) {
    var step2 = document.getElementById(agendaId + '-step2');
    var errorEl = document.getElementById(agendaId + '-error');
    errorEl.textContent = '';

    var name = document.getElementById(agendaId + '-name').value.trim();
    var contact = document.getElementById(agendaId + '-contact').value.trim();
    var notes = document.getElementById(agendaId + '-notes').value.trim();

    if (!name || !contact) { errorEl.textContent = 'Preencha nome e contato.'; return; }

    var body = new URLSearchParams({
        date: step2.dataset.date,
        start_time: step2.dataset.slot,
        customer_name: name,
        customer_contact: contact,
        notes: notes,
    });

    fetch(HUB_BASE + '/agenda/' + blockId + '/book', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) { errorEl.textContent = d.error || 'Erro ao agendar.'; return; }
            step2.classList.add('d-none');
            var successEl = document.getElementById(agendaId + '-success');
            successEl.textContent = d.message;
            successEl.classList.remove('d-none');
        })
        .catch(function () { errorEl.textContent = 'Erro de conexão.'; });
}
</script>
