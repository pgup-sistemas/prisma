<!-- Hero -->
<div class="container py-5">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <h1 class="display-font mb-3" style="font-size:2.4rem;color:var(--color-text-primary);">
                Gere um QR Code agora — <span style="color:var(--color-accent);">sem cadastro</span>
            </h1>
            <p style="color:var(--color-text-secondary);font-size:1.05rem;">
                Link, texto, WhatsApp, telefone ou Wi-Fi. Baixe seu QR Code em segundos.
                Quer PIX, vCard, salvar histórico e acompanhar leituras? <a href="<?= url('/register') ?>" style="color:var(--color-accent);">crie uma conta grátis</a>.
            </p>
            <div class="spectrum-bar rounded mt-3" style="max-width:200px;"></div>
        </div>

        <div class="col-lg-6">
            <div class="card p-4">
                <div class="mb-3">
                    <label class="form-label small">Tipo</label>
                    <select id="home-qr-type" class="form-select form-select-sm">
                        <option value="url">Link (URL)</option>
                        <option value="text">Texto</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="phone">Telefone</option>
                        <option value="wifi">Wi-Fi</option>
                    </select>
                </div>

                <div data-qr-fields="url">
                    <input type="url" class="form-control form-control-sm mb-2 home-qr-field" name="url" placeholder="https://exemplo.com">
                </div>
                <div data-qr-fields="text" class="d-none">
                    <textarea class="form-control form-control-sm mb-2 home-qr-field" name="text" rows="2" placeholder="Digite o texto"></textarea>
                </div>
                <div data-qr-fields="whatsapp" class="d-none">
                    <input type="text" class="form-control form-control-sm mb-2 home-qr-field" name="phone" placeholder="Telefone com DDI (ex: 5569999990000)">
                    <textarea class="form-control form-control-sm mb-2 home-qr-field" name="text" rows="2" placeholder="Mensagem (opcional)"></textarea>
                </div>
                <div data-qr-fields="phone" class="d-none">
                    <input type="text" class="form-control form-control-sm mb-2 home-qr-field" name="phone" placeholder="(69) 99999-0000">
                </div>
                <div data-qr-fields="wifi" class="d-none">
                    <input type="text" class="form-control form-control-sm mb-2 home-qr-field" name="ssid" placeholder="Nome da rede (SSID)">
                    <input type="text" class="form-control form-control-sm mb-2 home-qr-field" name="password" placeholder="Senha">
                </div>

                <button type="button" class="btn btn-primary w-100" onclick="generateHomeQR()">
                    <i class="bi bi-qr-code"></i> Gerar QR Code
                </button>
                <div id="home-qr-error" class="text-danger small mt-2"></div>

                <div id="home-qr-result" class="d-none text-center mt-3">
                    <img id="home-qr-img" class="img-fluid rounded mb-2" style="max-width:220px;background:#fff;padding:8px;" alt="QR Code gerado">
                    <a id="home-qr-download" class="btn btn-sm btn-outline-light d-block" download="qrcode-prisma.png">
                        <i class="bi bi-download"></i> Baixar PNG
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acesso rápido às ferramentas -->
<div class="container pb-5">
    <h2 class="display-font text-center mb-4" style="font-size:1.5rem;color:var(--color-text-primary);">Ferramentas gratuitas</h2>
    <div class="row g-3">
        <?php
        $quickTools = [
            ['bi-geo-alt', 'Consulta de CEP', 'cep'],
            ['bi-person-vcard', 'Validador CPF/CNPJ', 'docs'],
            ['bi-shield-lock', 'Gerador de Senha', 'password'],
            ['bi-currency-exchange', 'Conversor de Moedas', 'currency'],
            ['bi-heart-pulse', 'Calculadora de IMC', 'imc'],
            ['bi-rulers', 'Conversor de Unidades', 'units'],
        ];
        foreach ($quickTools as [$icon, $label, $anchor]):
        ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= url('/tools#tool-' . $anchor) ?>" class="card p-3 text-center text-decoration-none h-100 d-flex flex-column justify-content-center">
                <i class="bi <?= $icon ?>" style="font-size:1.8rem;color:var(--color-accent);"></i>
                <span class="mt-2" style="font-size:.85rem;color:var(--color-text-primary);"><?= $label ?></span>
            </a>
        </div>
        <?php endforeach; ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= url('/tools') ?>" class="card p-3 text-center text-decoration-none h-100 d-flex flex-column justify-content-center" style="border-color:var(--color-accent);">
                <i class="bi bi-grid-3x3-gap" style="font-size:1.8rem;color:var(--color-accent);"></i>
                <span class="mt-2" style="font-size:.85rem;color:var(--color-text-primary);">Ver todas</span>
            </a>
        </div>
    </div>
</div>

<!-- Recursos completos (conta) -->
<div class="container pb-5">
    <h2 class="display-font text-center mb-4" style="font-size:1.5rem;color:var(--color-text-primary);">Com uma conta grátis, você também tem</h2>
    <div class="row g-3">
        <?php
        $features = [
            ['bi-link-45deg', 'Encurtador de Links', 'Encurte URLs com estatísticas de clique, UTM, redirecionamento condicional e A/B test.'],
            ['bi-grid-3x3-gap', 'Hub Digital', 'Uma página só com todos os seus links, PIX, agenda de horários e catálogo — tipo um Linktree turbinado.'],
            ['bi-calendar-check', 'Agenda de Horários', 'Deixe visitantes agendarem horário direto no seu Hub, sem conflito de agenda.'],
            ['bi-bookmark-star', 'Importador de Favoritos', 'Importe os favoritos do navegador e acesse tudo com busca ultrarrápida (Ctrl+K) no seu site.'],
        ];
        foreach ($features as [$icon, $label, $desc]):
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="card p-4 h-100">
                <i class="bi <?= $icon ?> mb-2" style="font-size:1.6rem;color:var(--color-accent);"></i>
                <h6 class="display-font mb-2"><?= $label ?></h6>
                <p style="color:var(--color-text-secondary);font-size:.85rem;" class="mb-0"><?= $desc ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a href="<?= url('/register') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Criar conta grátis
        </a>
    </div>
</div>

<script>
(function () {
    var typeSelect = document.getElementById('home-qr-type');

    function toggleHomeFields() {
        var selected = typeSelect.value;
        document.querySelectorAll('[data-qr-fields]').forEach(function (el) {
            var isActive = el.dataset.qrFields === selected;
            el.classList.toggle('d-none', !isActive);
            el.querySelectorAll('.home-qr-field').forEach(function (field) { field.disabled = !isActive; });
        });
    }
    typeSelect.addEventListener('change', toggleHomeFields);
    toggleHomeFields();

    window.generateHomeQR = function () {
        var errorEl = document.getElementById('home-qr-error');
        var resultEl = document.getElementById('home-qr-result');
        errorEl.textContent = '';
        resultEl.classList.add('d-none');

        var body = new URLSearchParams({ type: typeSelect.value });
        document.querySelectorAll('.home-qr-field').forEach(function (field) {
            if (!field.disabled) body.set(field.name, field.value);
        });

        fetch(<?= json_encode(url('/qr/quick')) ?>, { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) { errorEl.textContent = d.error || 'Erro ao gerar QR Code.'; return; }
                document.getElementById('home-qr-img').src = d.png;
                document.getElementById('home-qr-download').href = d.png;
                resultEl.classList.remove('d-none');
            })
            .catch(function () { errorEl.textContent = 'Erro de conexão.'; });
    };
})();
</script>
