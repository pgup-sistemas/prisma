<h2 class="display-font mb-4">Gerar QR Code</h2>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <form method="POST" action="<?= url('/generate') ?>" id="qr-form">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label">Tipo de QR Code</label>
                    <select name="type" id="qr-type" class="form-select">
                        <option value="url">URL</option>
                        <option value="text">Texto</option>
                        <option value="email">E-mail</option>
                        <option value="phone">Telefone</option>
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="wifi">Wi-Fi</option>
                        <option value="vcard">vCard</option>
                        <option value="geo">Localização</option>
                        <option value="event">Evento</option>
                        <option value="bitcoin">Bitcoin</option>
                        <option value="pix">PIX</option>
                    </select>
                </div>

                <div data-fields="url">
                    <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="url" class="form-control qr-field" name="url" placeholder="https://exemplo.com">
                    </div>
                </div>

                <div data-fields="text" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Texto</label>
                        <textarea class="form-control qr-field" name="text" rows="3"></textarea>
                    </div>
                </div>

                <div data-fields="email" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Para</label>
                        <input type="email" class="form-control qr-field" name="to">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assunto</label>
                        <input type="text" class="form-control qr-field" name="subject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensagem</label>
                        <textarea class="form-control qr-field" name="body" rows="2"></textarea>
                    </div>
                </div>

                <div data-fields="phone" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control qr-field" name="phone" placeholder="+55 69 99999-0000">
                    </div>
                </div>

                <div data-fields="sms" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control qr-field" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensagem</label>
                        <textarea class="form-control qr-field" name="body" rows="2"></textarea>
                    </div>
                </div>

                <div data-fields="whatsapp" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Telefone (com DDI)</label>
                        <input type="text" class="form-control qr-field" name="phone" placeholder="5569999990000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensagem</label>
                        <textarea class="form-control qr-field" name="text" rows="2"></textarea>
                    </div>
                </div>

                <div data-fields="wifi" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Nome da rede (SSID)</label>
                        <input type="text" class="form-control qr-field" name="ssid">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="text" class="form-control qr-field" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Criptografia</label>
                        <select class="form-select qr-field" name="encryption">
                            <option value="WPA">WPA/WPA2</option>
                            <option value="WEP">WEP</option>
                            <option value="nopass">Sem senha</option>
                        </select>
                    </div>
                </div>

                <div data-fields="vcard" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" class="form-control qr-field" name="name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control qr-field" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control qr-field" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Empresa</label>
                        <input type="text" class="form-control qr-field" name="organization">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control qr-field" name="title">
                    </div>
                </div>

                <div data-fields="geo" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" class="form-control qr-field" name="lat">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" class="form-control qr-field" name="lng">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rótulo</label>
                        <input type="text" class="form-control qr-field" name="label">
                    </div>
                </div>

                <div data-fields="event" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control qr-field" name="title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Início</label>
                        <input type="datetime-local" class="form-control qr-field" name="start">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fim</label>
                        <input type="datetime-local" class="form-control qr-field" name="end">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Local</label>
                        <input type="text" class="form-control qr-field" name="location">
                    </div>
                </div>

                <div data-fields="bitcoin" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Endereço da carteira</label>
                        <input type="text" class="form-control qr-field" name="address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor (BTC, opcional)</label>
                        <input type="text" class="form-control qr-field" name="amount">
                    </div>
                </div>

                <div data-fields="pix" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Chave PIX</label>
                        <input type="text" class="form-control qr-field" name="key">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome do recebedor</label>
                        <input type="text" class="form-control qr-field" name="name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cidade</label>
                        <input type="text" class="form-control qr-field" name="city">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor (R$, opcional)</label>
                        <input type="text" class="form-control qr-field" name="amount">
                    </div>
                </div>

                <hr style="border-color:var(--color-border);">

                <div class="mb-3">
                    <label class="form-label">Rótulo (uso interno)</label>
                    <input type="text" class="form-control" name="label" maxlength="120">
                </div>

                <div class="row">
                    <div class="col-4 mb-3">
                        <label class="form-label">Cor</label>
                        <input type="color" class="form-control form-control-color w-100 qr-field" name="fg_color" value="#000000">
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">Fundo</label>
                        <input type="color" class="form-control form-control-color w-100 qr-field" name="bg_color" value="#FFFFFF">
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">ECC</label>
                        <select class="form-select qr-field" name="ecc_level">
                            <option value="L">L (7%)</option>
                            <option value="M" selected>M (15%)</option>
                            <option value="Q">Q (25%)</option>
                            <option value="H">H (30%)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tamanho: <span id="size-value">512</span>px</label>
                    <input type="range" class="form-range qr-field" name="size" min="128" max="1024" step="32" value="512" id="qr-size">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-qr-code"></i> Gerar QR Code
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-4 text-center">
            <h6 style="color:var(--color-text-secondary);">Prévia</h6>
            <div id="qr-preview" class="d-flex align-items-center justify-content-center" style="min-height:300px;">
                <span style="color:var(--color-text-muted);">Preencha os campos para ver a prévia</span>
            </div>
        </div>
    </div>
</div>

<script>
    window.PRISMA_PREVIEW_URL = <?= json_encode(url('/qr/preview')) ?>;
    window.PRISMA_CSRF = <?= json_encode($csrf_token) ?>;
</script>
