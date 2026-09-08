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
    <h2 class="display-font text-center mb-2" style="font-size:1.5rem;color:var(--color-text-primary);">Com uma conta grátis, você também tem</h2>
    <p class="text-center mb-4" style="color:var(--color-text-secondary);max-width:560px;margin-inline:auto;">
        Tudo isso fica salvo na sua conta — histórico, estatísticas e configurações — em vez de se perder depois que a aba fecha.
    </p>
    <div class="row g-3">
        <?php
        $features = [
            ['bi-link-45deg', 'Encurtador de Links', 'Encurte URLs com estatísticas de clique, UTM, redirecionamento condicional e A/B test.', 'Ex: descubra que 60% dos cliques no seu link vêm do Instagram e só à noite.'],
            ['bi-grid-3x3-gap', 'Hub Digital', 'Uma página só com todos os seus links, PIX, agenda de horários e catálogo — tipo um Linktree turbinado.', 'Ex: coloque o link do Hub na bio e receba pedido, pagamento PIX e agendamento sem sair de lá.'],
            ['bi-calendar-check', 'Agenda de Horários', 'Deixe visitantes agendarem horário direto no seu Hub, sem conflito de agenda.', 'Ex: cliente escolhe terça 14h, você recebe a notificação e confirma com um clique.'],
            ['bi-bookmark-star', 'Importador de Favoritos', 'Importe os favoritos do navegador pra sua conta PRISMA e ache tudo com busca ultrarrápida.', 'Combina com o Launcher desktop — veja abaixo.'],
        ];
        foreach ($features as [$icon, $label, $desc, $example]):
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="card p-4 h-100">
                <i class="bi <?= $icon ?> mb-2" style="font-size:1.6rem;color:var(--color-accent);"></i>
                <h6 class="display-font mb-2"><?= $label ?></h6>
                <p style="color:var(--color-text-secondary);font-size:.85rem;" class="mb-2"><?= $desc ?></p>
                <p style="color:var(--color-text-muted);font-size:.78rem;font-style:italic;" class="mb-0"><?= $example ?></p>
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

<!-- PRISMA Launcher — seção dedicada -->
<div class="container pb-5">
    <div class="card p-4 p-md-5" style="background:var(--gradient-brand);border-color:var(--color-accent);">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge rounded-pill mb-3" style="background:rgba(46,134,171,.15);color:var(--color-accent);padding:.5rem 1rem;">
                    <i class="bi bi-lightning-charge-fill me-1"></i>Novo — Agente de Desktop
                </span>
                <h2 class="display-font mb-3" style="font-size:1.9rem;color:var(--color-text-primary);">
                    Ache qualquer link seu sem tirar as mãos do teclado
                </h2>
                <p style="color:var(--color-text-secondary);font-size:1rem;">
                    O PRISMA Launcher fica na bandeja do seu computador. Aperte o atalho, comece a
                    digitar — ele busca nos seus favoritos, links encurtados e QR Codes salvos na
                    sua conta PRISMA, mesmo errando a digitação.
                </p>

                <ul class="list-unstyled d-flex flex-column gap-3 my-4">
                    <li class="d-flex gap-3">
                        <i class="bi bi-search flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Digite <code>"finance"</code> e ele acha o favorito <strong style="color:var(--color-text-primary);">"Sistema Financeiro"</strong> que você salvou mês passado — mesmo com erro de digitação.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-qr-code flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Cole uma URL e gere <strong style="color:var(--color-text-primary);">QR Code ou link curto</strong> ali mesmo — baixe o PNG ou copie a imagem direto pra área de transferência, sem abrir o navegador.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-terminal flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Comandos rápidos no mesmo campo de busca: <code>wa:</code>, <code>pix:</code> e <code>vcard:</code> geram QR Code de WhatsApp, Pix e cartão de contato sem sair do teclado.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-funnel flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Prefira <code>favorito:</code> ou <code>arquivo:</code> pra restringir a busca só a uma dessas fontes quando o resultado certo está perdido no meio dos outros.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-clock-history flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Os últimos QR Codes e links gerados ficam em <strong style="color:var(--color-text-primary);">"Recentes"</strong> na tela inicial da busca — nada se perde se você fechar antes de copiar.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-bookmark-star flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Favoritos do Chrome, Edge, Brave ou Firefox <strong style="color:var(--color-text-primary);">sincronizados automaticamente</strong> pra sua conta — sem importar de novo toda vez.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-folder2-open flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Ache também arquivos das pastas <strong style="color:var(--color-text-primary);">Downloads, Documentos e Área de Trabalho</strong> — opcional, desligado por padrão.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-window flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            O atalho abre em <strong style="color:var(--color-text-primary);">qualquer programa</strong> — não precisa estar com o navegador aberto.
                        </span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-arrow-repeat flex-shrink-0 mt-1" style="color:var(--color-accent);"></i>
                        <span style="color:var(--color-text-secondary);font-size:.92rem;">
                            Atualiza sozinho quando sai uma versão nova — sem precisar baixar e instalar de novo manualmente.
                        </span>
                    </li>
                </ul>

                <p style="color:var(--color-text-muted);font-size:.78rem;max-width:440px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Por padrão busca só o que está na sua conta PRISMA (favoritos, links, QR Codes).
                    A busca de arquivos locais e a sincronização de favoritos (Chrome, Edge, Brave e
                    Firefox) são opt-in — nada é lido nem enviado sem você habilitar explicitamente
                    nas Configurações. Arquivos locais nunca saem do seu computador.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <a href="<?= url('/download') ?>" class="btn btn-primary">
                        <i class="bi bi-download"></i> Baixar grátis
                    </a>
                    <span style="color:var(--color-text-muted);font-size:.82rem;">
                        Grátis · Linux disponível agora, Windows/Mac em breve
                    </span>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Mockup da busca do Launcher -->
                <div style="max-width:440px;margin-inline:auto;">
                    <div class="text-center mb-2">
                        <kbd style="background:var(--color-elevated);border:1px solid var(--color-border);color:var(--color-text-secondary);padding:.35rem .6rem;border-radius:6px;font-size:.8rem;">Ctrl</kbd>
                        <span style="color:var(--color-text-muted);">+</span>
                        <kbd style="background:var(--color-elevated);border:1px solid var(--color-border);color:var(--color-text-secondary);padding:.35rem .6rem;border-radius:6px;font-size:.8rem;">Alt</kbd>
                        <span style="color:var(--color-text-muted);">+</span>
                        <kbd style="background:var(--color-elevated);border:1px solid var(--color-border);color:var(--color-text-secondary);padding:.35rem .6rem;border-radius:6px;font-size:.8rem;">K</kbd>
                        <span style="color:var(--color-text-muted);font-size:.78rem;"> — em qualquer lugar do sistema</span>
                    </div>
                    <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:14px;box-shadow:var(--shadow-lg);overflow:hidden;">
                        <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--color-border);">
                            <i class="bi bi-search" style="color:var(--color-text-muted);"></i>
                            <span style="color:var(--color-text-primary);font-size:.95rem;">finance</span>
                        </div>
                        <div style="padding:6px;">
                            <div style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;background:rgba(46,134,171,.15);">
                                <span style="width:26px;height:26px;border-radius:6px;background:var(--color-elevated);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi bi-bookmark-star" style="font-size:.8rem;color:var(--color-accent);"></i>
                                </span>
                                <span>
                                    <span style="display:block;font-size:.82rem;font-weight:500;color:var(--color-text-primary);">Sistema Financeiro</span>
                                    <span style="display:block;font-size:.72rem;color:var(--color-text-secondary);">app.financeiro.com/dashboard</span>
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;">
                                <span style="width:26px;height:26px;border-radius:6px;background:var(--color-elevated);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi bi-link-45deg" style="font-size:.8rem;color:var(--color-accent);"></i>
                                </span>
                                <span>
                                    <span style="display:block;font-size:.82rem;font-weight:500;color:var(--color-text-primary);">Relatório financeiro Q3</span>
                                    <span style="display:block;font-size:.72rem;color:var(--color-text-secondary);">localhost/prisma/r/fin-q3</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
