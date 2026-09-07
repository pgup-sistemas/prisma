<div class="container py-5">
    <div class="text-center mb-4">
        <a href="<?= url('/') ?>" class="d-inline-block mb-3">
            <img src="<?= asset('img/logo.svg') ?>" height="40" alt="PRISMA">
        </a>
        <h1 class="display-font" style="color:var(--color-text-primary);">Mini Ferramentas</h1>
        <p style="color:var(--color-text-secondary);">Gratuitas, sem login, sem cadastro. Tudo roda no seu navegador.</p>
        <div class="spectrum-bar rounded mx-auto" style="max-width:240px;"></div>
    </div>

    <ul class="nav nav-pills flex-nowrap overflow-auto gap-1 mb-4 pb-2" id="tools-nav" style="scrollbar-width:thin;">
        <?php
        $tools = [
            'image'     => ['bi-image',              'Imagens'],
            'pdf'       => ['bi-file-earmark-pdf',    'Reduzir PDF'],
            'cep'       => ['bi-geo-alt',            'CEP'],
            'docs'      => ['bi-person-vcard',       'CPF/CNPJ'],
            'password'  => ['bi-shield-lock',        'Senha'],
            'currency'  => ['bi-currency-exchange',  'Moedas'],
            'imc'       => ['bi-heart-pulse',        'IMC'],
            'units'     => ['bi-rulers',             'Unidades'],
            'words'     => ['bi-file-text',          'Palavras'],
            'lorem'     => ['bi-textarea-t',         'Lorem Ipsum'],
            'colors'    => ['bi-eyedropper',         'Cores'],
            'uuid'      => ['bi-fingerprint',        'UUID'],
            'base64'    => ['bi-code-square',        'Base64'],
            'json'      => ['bi-braces',             'JSON'],
            'timestamp' => ['bi-clock-history',      'Timestamp'],
        ];
        $first = true;
        foreach ($tools as $key => [$icon, $label]):
        ?>
        <li class="nav-item flex-shrink-0">
            <button class="nav-link <?= $first ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tool-<?= $key ?>" type="button">
                <i class="bi <?= $icon ?> me-1"></i><?= $label ?>
            </button>
        </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <div class="tab-content">

        <!-- Imagens: redimensionar, reduzir, converter -->
        <div class="tab-pane fade show active" id="tool-image">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-image"></i> Redimensionar, Reduzir e Converter Imagem</h5>
                <input type="file" id="image-input" class="form-control form-control-sm mb-3" accept="image/*" onchange="Tools.image.load(this)">

                <div id="image-controls" class="d-none">
                    <p class="small mb-3" style="color:var(--color-text-secondary);">
                        Original: <span id="image-original-info"></span>
                    </p>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Largura (px)</label>
                            <input type="number" id="image-width" class="form-control form-control-sm" oninput="Tools.image.onWidthChange()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Altura (px)</label>
                            <input type="number" id="image-height" class="form-control form-control-sm" oninput="Tools.image.onHeightChange()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Formato</label>
                            <select id="image-format" class="form-select form-select-sm" onchange="Tools.image.onFormatChange()">
                                <option value="original">Manter original</option>
                                <option value="image/jpeg">JPEG</option>
                                <option value="image/png">PNG</option>
                                <option value="image/webp">WebP</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3" id="image-quality-wrap" style="display:none;">
                            <label class="form-label small">Qualidade: <span id="image-quality-val">80</span>%</label>
                            <input type="range" id="image-quality" class="form-range" min="10" max="100" value="80"
                                   oninput="document.getElementById('image-quality-val').textContent = this.value">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" id="image-keep-ratio" class="form-check-input" checked>
                        <label class="form-check-label small" for="image-keep-ratio">Manter proporção</label>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="Tools.image.process()"><i class="bi bi-magic"></i> Processar</button>
                </div>

                <div id="image-result" class="d-none mt-3 text-center">
                    <img id="image-result-preview" class="img-fluid rounded mb-2" style="max-height:280px;background:var(--color-elevated);" alt="Resultado">
                    <p class="small mb-2" style="color:var(--color-text-secondary);" id="image-result-info"></p>
                    <a id="image-download" class="btn btn-sm btn-outline-light"><i class="bi bi-download"></i> Baixar imagem</a>
                </div>
            </div>
        </div>

        <!-- Reduzir PDF -->
        <div class="tab-pane fade" id="tool-pdf">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-file-earmark-pdf"></i> Reduzir PDF</h5>
                <p class="small mb-3" style="color:var(--color-text-muted);">
                    Esta ferramenta processa o arquivo no servidor (as demais rodam no seu navegador). Máx. 15MB.
                </p>
                <input type="file" id="pdf-input" class="form-control form-control-sm mb-3" accept="application/pdf" style="max-width:400px;">
                <div class="mb-3">
                    <label class="form-label small">Nível de compressão</label>
                    <select id="pdf-level" class="form-select form-select-sm" style="max-width:260px;">
                        <option value="low">Leve (melhor qualidade)</option>
                        <option value="medium" selected>Média (recomendado)</option>
                        <option value="high">Forte (menor tamanho, pode perder qualidade de imagens)</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm" id="pdf-submit" onclick="Tools.pdf.compress(this)">
                    <i class="bi bi-file-earmark-zip"></i> Reduzir PDF
                </button>
                <div id="pdf-error" class="text-danger small mt-2"></div>
                <div id="pdf-result" class="d-none mt-3 p-3 rounded" style="background:var(--color-elevated);max-width:420px;">
                    <div id="pdf-sizes" style="color:var(--color-text-secondary);"></div>
                    <a id="pdf-download" class="btn btn-sm btn-outline-light mt-2" download="pdf-reduzido.pdf"><i class="bi bi-download"></i> Baixar PDF reduzido</a>
                </div>
            </div>
        </div>

        <!-- Consulta de CEP -->
        <div class="tab-pane fade" id="tool-cep">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-geo-alt"></i> Consulta de CEP</h5>
                <div class="input-group input-group-sm mb-3" style="max-width:320px;">
                    <input type="text" id="cep-input" class="form-control" placeholder="00000-000" maxlength="9" inputmode="numeric">
                    <button class="btn btn-primary" onclick="Tools.cep.lookup()"><i class="bi bi-search"></i> Buscar</button>
                </div>
                <div id="cep-error" class="text-danger small mb-2"></div>
                <div id="cep-result" class="d-none">
                    <table class="table table-sm mb-0" style="max-width:480px;">
                        <tr><td style="color:var(--color-text-muted);width:40%;">Logradouro</td><td id="cep-logradouro"></td></tr>
                        <tr><td style="color:var(--color-text-muted);">Bairro</td><td id="cep-bairro"></td></tr>
                        <tr><td style="color:var(--color-text-muted);">Cidade / UF</td><td id="cep-cidade"></td></tr>
                        <tr><td style="color:var(--color-text-muted);">DDD</td><td id="cep-ddd"></td></tr>
                    </table>
                    <button class="btn btn-sm btn-outline-light mt-2" onclick="Tools.cep.copy()"><i class="bi bi-clipboard"></i> Copiar endereço</button>
                </div>
            </div>
        </div>

        <!-- Calculadora de IMC -->
        <div class="tab-pane fade" id="tool-imc">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-heart-pulse"></i> Calculadora de IMC</h5>
                <div class="row g-3" style="max-width:480px;">
                    <div class="col-6">
                        <label class="form-label small">Peso (kg)</label>
                        <input type="number" id="imc-weight" class="form-control form-control-sm" step="0.1" min="1" placeholder="70">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Altura (cm)</label>
                        <input type="number" id="imc-height" class="form-control form-control-sm" step="1" min="1" placeholder="170">
                    </div>
                </div>
                <button class="btn btn-primary btn-sm mt-3" onclick="Tools.imc.calculate()"><i class="bi bi-calculator"></i> Calcular</button>
                <div class="mt-3 p-3 rounded d-none" id="imc-result-box" style="background:var(--color-elevated);max-width:480px;">
                    <span style="color:var(--color-text-muted);">Seu IMC:</span>
                    <strong id="imc-result" class="display-font" style="font-size:1.4rem;color:var(--color-accent);">—</strong>
                    <div id="imc-classification" class="mt-1" style="font-weight:500;"></div>
                    <small style="color:var(--color-text-muted);">Referência OMS — não substitui avaliação médica.</small>
                </div>
            </div>
        </div>

        <!-- Conversor de Unidades -->
        <div class="tab-pane fade" id="tool-units">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-rulers"></i> Conversor de Unidades</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Categoria</label>
                        <select id="units-category" class="form-select form-select-sm">
                            <option value="length">Comprimento</option>
                            <option value="weight">Peso</option>
                            <option value="temperature">Temperatura</option>
                            <option value="volume">Volume</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">De</label>
                        <select id="units-from" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Para</label>
                        <select id="units-to" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Valor</label>
                        <input type="number" id="units-value" class="form-control form-control-sm" value="1">
                    </div>
                </div>
                <div class="mt-3 p-3 rounded" style="background:var(--color-elevated);">
                    <span style="color:var(--color-text-muted);">Resultado:</span>
                    <strong id="units-result" class="display-font" style="font-size:1.4rem;color:var(--color-accent);">—</strong>
                </div>
            </div>
        </div>

        <!-- Conversor de Moedas -->
        <div class="tab-pane fade" id="tool-currency">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-currency-exchange"></i> Conversor de Moedas</h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Valor</label>
                        <input type="number" id="currency-amount" class="form-control form-control-sm" value="1" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">De</label>
                        <select id="currency-from" class="form-select form-select-sm">
                            <option value="BRL">BRL — Real</option>
                            <option value="USD" selected>USD — Dólar</option>
                            <option value="EUR">EUR — Euro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Para</label>
                        <select id="currency-to" class="form-select form-select-sm">
                            <option value="BRL" selected>BRL — Real</option>
                            <option value="USD">USD — Dólar</option>
                            <option value="EUR">EUR — Euro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm w-100" onclick="Tools.currency.convert()">
                            <i class="bi bi-arrow-repeat"></i> Converter
                        </button>
                    </div>
                </div>
                <div class="mt-3 p-3 rounded" style="background:var(--color-elevated);">
                    <span style="color:var(--color-text-muted);">Resultado:</span>
                    <strong id="currency-result" class="display-font" style="font-size:1.4rem;color:var(--color-accent);">—</strong>
                </div>
            </div>
        </div>

        <!-- Gerador de Senha -->
        <div class="tab-pane fade" id="tool-password">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-shield-lock"></i> Gerador de Senha</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Tamanho: <span id="password-length-val">16</span></label>
                        <input type="range" id="password-length" class="form-range" min="6" max="64" value="16">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="password-upper" checked>
                                <label class="form-check-label small" for="password-upper">Maiúsculas</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="password-numbers" checked>
                                <label class="form-check-label small" for="password-numbers">Números</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="password-symbols" checked>
                                <label class="form-check-label small" for="password-symbols">Símbolos</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="input-group mt-3">
                    <input type="text" id="password-output" class="form-control" readonly style="font-family:var(--font-mono);">
                    <button class="btn btn-outline-light" onclick="Tools.password.copy()"><i class="bi bi-clipboard"></i></button>
                    <button class="btn btn-primary" onclick="Tools.password.generate()"><i class="bi bi-arrow-repeat"></i> Gerar</button>
                </div>
            </div>
        </div>

        <!-- Validador CPF/CNPJ -->
        <div class="tab-pane fade" id="tool-docs">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-person-vcard"></i> Validador de CPF/CNPJ</h5>
                <div class="input-group">
                    <input type="text" id="docs-input" class="form-control" placeholder="Digite o CPF ou CNPJ">
                    <button class="btn btn-primary" onclick="Tools.docs.validate()"><i class="bi bi-check2-circle"></i> Validar</button>
                </div>
                <div class="mt-3" id="docs-result"></div>
            </div>
        </div>

        <!-- Gerador UUID -->
        <div class="tab-pane fade" id="tool-uuid">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-fingerprint"></i> Gerador de UUID</h5>
                <div class="input-group mb-2">
                    <input type="text" id="uuid-output" class="form-control" readonly style="font-family:var(--font-mono);">
                    <button class="btn btn-outline-light" onclick="Tools.uuid.copy()"><i class="bi bi-clipboard"></i></button>
                    <button class="btn btn-primary" onclick="Tools.uuid.generate()"><i class="bi bi-arrow-repeat"></i> Gerar</button>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Quantidade em lote</label>
                    <div class="input-group" style="max-width:220px;">
                        <input type="number" id="uuid-batch-count" class="form-control form-control-sm" value="5" min="1" max="100">
                        <button class="btn btn-outline-light btn-sm" onclick="Tools.uuid.generateBatch()">Gerar lote</button>
                    </div>
                </div>
                <textarea id="uuid-batch-output" class="form-control form-control-sm" rows="6" readonly style="font-family:var(--font-mono);font-size:.8rem;"></textarea>
            </div>
        </div>

        <!-- Base64 -->
        <div class="tab-pane fade" id="tool-base64">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-code-square"></i> Base64 Encode / Decode</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Texto</label>
                        <textarea id="base64-text" class="form-control form-control-sm" rows="6"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Base64</label>
                        <textarea id="base64-encoded" class="form-control form-control-sm" rows="6" style="font-family:var(--font-mono);"></textarea>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="Tools.base64.encode()"><i class="bi bi-arrow-right"></i> Codificar</button>
                    <button class="btn btn-outline-light btn-sm" onclick="Tools.base64.decode()"><i class="bi bi-arrow-left"></i> Decodificar</button>
                </div>
            </div>
        </div>

        <!-- Formatador JSON -->
        <div class="tab-pane fade" id="tool-json">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-braces"></i> Formatador JSON</h5>
                <textarea id="json-input" class="form-control form-control-sm mb-2" rows="10" style="font-family:var(--font-mono);font-size:.85rem;" placeholder='{"exemplo": true}'></textarea>
                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-primary btn-sm" onclick="Tools.json.format(true)"><i class="bi bi-text-indent-left"></i> Formatar</button>
                    <button class="btn btn-outline-light btn-sm" onclick="Tools.json.format(false)"><i class="bi bi-arrows-collapse"></i> Minificar</button>
                </div>
                <div id="json-error" class="text-danger small"></div>
            </div>
        </div>

        <!-- Conversor de Timestamp -->
        <div class="tab-pane fade" id="tool-timestamp">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-clock-history"></i> Conversor de Timestamp</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Timestamp Unix (segundos)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="timestamp-input" class="form-control">
                            <button class="btn btn-outline-light" onclick="Tools.timestamp.now()">Agora</button>
                        </div>
                        <div class="mt-2" id="timestamp-to-date" style="color:var(--color-text-secondary);"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Data/Hora</label>
                        <input type="datetime-local" id="date-input" class="form-control form-control-sm">
                        <div class="mt-2" id="date-to-timestamp" style="color:var(--color-text-secondary);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contador de Palavras -->
        <div class="tab-pane fade" id="tool-words">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-file-text"></i> Contador de Palavras</h5>
                <textarea id="words-input" class="form-control form-control-sm mb-2" rows="8" placeholder="Cole seu texto aqui..."></textarea>
                <div class="d-flex gap-4 flex-wrap" style="color:var(--color-text-secondary);font-size:.9rem;">
                    <span>Palavras: <strong id="words-count">0</strong></span>
                    <span>Caracteres: <strong id="chars-count">0</strong></span>
                    <span>Caracteres (sem espaços): <strong id="chars-nospace-count">0</strong></span>
                    <span>Frases: <strong id="sentences-count">0</strong></span>
                    <span>Parágrafos: <strong id="paragraphs-count">0</strong></span>
                </div>
            </div>
        </div>

        <!-- Lorem Ipsum -->
        <div class="tab-pane fade" id="tool-lorem">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-textarea-t"></i> Gerador de Lorem Ipsum</h5>
                <div class="row g-3 align-items-end mb-2">
                    <div class="col-md-4">
                        <label class="form-label small">Parágrafos</label>
                        <input type="number" id="lorem-count" class="form-control form-control-sm" value="3" min="1" max="20">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary btn-sm w-100" onclick="Tools.lorem.generate()"><i class="bi bi-arrow-repeat"></i> Gerar</button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-light btn-sm w-100" onclick="Tools.lorem.copy()"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                </div>
                <textarea id="lorem-output" class="form-control form-control-sm" rows="8" readonly></textarea>
            </div>
        </div>

        <!-- Extrator de Cores -->
        <div class="tab-pane fade" id="tool-colors">
            <div class="card p-4">
                <h5 class="display-font mb-3"><i class="bi bi-eyedropper"></i> Extrator de Cores</h5>
                <input type="file" id="colors-input" class="form-control form-control-sm mb-3" accept="image/*">
                <div class="row g-3">
                    <div class="col-md-6">
                        <canvas id="colors-canvas" class="w-100 rounded" style="max-height:280px;object-fit:contain;background:var(--color-elevated);"></canvas>
                        <small style="color:var(--color-text-muted);">Clique na imagem para pegar a cor de um ponto.</small>
                    </div>
                    <div class="col-md-6">
                        <div id="colors-palette" class="d-flex flex-wrap gap-2 mb-3"></div>
                        <div id="colors-picked"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    'use strict';
    window.Tools = {};

    // ── Conversor de Unidades ───────────────────────────────────────────
    var UNIT_DEFS = {
        length: { base: 'm', units: { mm: 0.001, cm: 0.01, m: 1, km: 1000, in: 0.0254, ft: 0.3048, yd: 0.9144, mi: 1609.344 } },
        weight: { base: 'kg', units: { mg: 0.000001, g: 0.001, kg: 1, ton: 1000, lb: 0.45359237, oz: 0.028349523 } },
        volume: { base: 'l', units: { ml: 0.001, l: 1, m3: 1000, gal: 3.785411784, qt: 0.946352946 } },
        temperature: { base: 'c', units: { c: 'c', f: 'f', k: 'k' } },
    };
    var UNIT_LABELS = { mm:'Milímetro',cm:'Centímetro',m:'Metro',km:'Quilômetro',in:'Polegada',ft:'Pé',yd:'Jarda',mi:'Milha',
        mg:'Miligrama',g:'Grama',kg:'Quilograma',ton:'Tonelada',lb:'Libra',oz:'Onça',
        ml:'Mililitro',l:'Litro',m3:'Metro cúbico',gal:'Galão',qt:'Quarto',
        c:'Celsius',f:'Fahrenheit',k:'Kelvin' };

    function tempToCelsius(v, u) { return u === 'f' ? (v - 32) * 5/9 : u === 'k' ? v - 273.15 : v; }
    function celsiusTo(v, u) { return u === 'f' ? v * 9/5 + 32 : u === 'k' ? v + 273.15 : v; }

    function populateUnitSelects() {
        var cat = document.getElementById('units-category').value;
        var def = UNIT_DEFS[cat];
        var from = document.getElementById('units-from');
        var to = document.getElementById('units-to');
        from.innerHTML = to.innerHTML = '';
        Object.keys(def.units).forEach(function (u) {
            var label = UNIT_LABELS[u] || u;
            from.innerHTML += '<option value="' + u + '">' + label + '</option>';
            to.innerHTML += '<option value="' + u + '">' + label + '</option>';
        });
        if (to.options.length > 1) to.selectedIndex = 1;
        convertUnits();
    }

    function convertUnits() {
        var cat = document.getElementById('units-category').value;
        var def = UNIT_DEFS[cat];
        var from = document.getElementById('units-from').value;
        var to = document.getElementById('units-to').value;
        var val = parseFloat(document.getElementById('units-value').value) || 0;
        var result;

        if (cat === 'temperature') {
            result = celsiusTo(tempToCelsius(val, from), to);
        } else {
            var base = val * def.units[from];
            result = base / def.units[to];
        }
        document.getElementById('units-result').textContent =
            (Math.round(result * 1e6) / 1e6).toLocaleString('pt-BR', { maximumFractionDigits: 6 }) + ' ' + (UNIT_LABELS[to] || to);
    }

    document.getElementById('units-category').addEventListener('change', populateUnitSelects);
    ['units-from', 'units-to', 'units-value'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', convertUnits);
        document.getElementById(id).addEventListener('change', convertUnits);
    });
    populateUnitSelects();

    // Ativa a aba indicada na URL (ex: vindo da Home via /tools#tool-cep)
    if (window.location.hash) {
        var targetPane = document.querySelector(window.location.hash);
        var targetTab = document.querySelector('[data-bs-target="' + window.location.hash + '"]');
        if (targetPane && targetTab) {
            document.querySelectorAll('#tools-nav .nav-link').forEach(function (btn) { btn.classList.remove('active'); });
            document.querySelectorAll('.tab-pane').forEach(function (pane) { pane.classList.remove('show', 'active'); });
            targetTab.classList.add('active');
            targetPane.classList.add('show', 'active');
            targetTab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }

    // ── Conversor de Moedas ─────────────────────────────────────────────
    Tools.currency = {
        convert: function () {
            var amount = parseFloat(document.getElementById('currency-amount').value) || 0;
            var from = document.getElementById('currency-from').value;
            var to = document.getElementById('currency-to').value;
            var out = document.getElementById('currency-result');
            out.textContent = 'Convertendo…';

            var body = new URLSearchParams({ amount: amount, from: from, to: to });
            fetch(<?= json_encode(url('/tools/currency')) ?>, { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    out.textContent = d.success
                        ? d.result.toLocaleString('pt-BR', { maximumFractionDigits: 4 }) + ' ' + d.to
                        : 'Erro: ' + (d.error || 'falha na conversão');
                })
                .catch(function () { out.textContent = 'Erro de conexão.'; });
        }
    };

    // ── Utilitário compartilhado ────────────────────────────────────────
    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    }

    // ── Imagens: redimensionar, reduzir, converter ───────────────────────
    Tools.image = {
        file: null,
        img: null,

        load: function (input) {
            var file = input.files[0];
            if (!file) return;
            Tools.image.file = file;

            var img = new Image();
            img.onload = function () {
                Tools.image.img = img;
                document.getElementById('image-original-info').textContent =
                    img.width + ' x ' + img.height + ' px — ' + formatBytes(file.size);
                document.getElementById('image-width').value = img.width;
                document.getElementById('image-height').value = img.height;
                document.getElementById('image-controls').classList.remove('d-none');
                document.getElementById('image-result').classList.add('d-none');
            };
            img.src = URL.createObjectURL(file);
        },

        onFormatChange: function () {
            var format = document.getElementById('image-format').value;
            document.getElementById('image-quality-wrap').style.display =
                (format === 'image/jpeg' || format === 'image/webp') ? '' : 'none';
        },

        onWidthChange: function () {
            if (!document.getElementById('image-keep-ratio').checked || !Tools.image.img) return;
            var ratio = Tools.image.img.height / Tools.image.img.width;
            var w = parseInt(document.getElementById('image-width').value, 10) || 0;
            document.getElementById('image-height').value = Math.round(w * ratio);
        },

        onHeightChange: function () {
            if (!document.getElementById('image-keep-ratio').checked || !Tools.image.img) return;
            var ratio = Tools.image.img.width / Tools.image.img.height;
            var h = parseInt(document.getElementById('image-height').value, 10) || 0;
            document.getElementById('image-width').value = Math.round(h * ratio);
        },

        process: function () {
            if (!Tools.image.img) return;

            var w = parseInt(document.getElementById('image-width').value, 10) || Tools.image.img.width;
            var h = parseInt(document.getElementById('image-height').value, 10) || Tools.image.img.height;
            var format = document.getElementById('image-format').value;
            var quality = parseInt(document.getElementById('image-quality').value, 10) / 100;
            var mime = format === 'original' ? (Tools.image.file.type || 'image/png') : format;

            var canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext('2d');
            if (mime !== 'image/png') {
                // evita fundo preto ao converter PNG com transparência pra JPEG
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, w, h);
            }
            ctx.drawImage(Tools.image.img, 0, 0, w, h);

            canvas.toBlob(function (blob) {
                if (!blob) return;
                var url = URL.createObjectURL(blob);
                var ext = mime.split('/')[1].replace('jpeg', 'jpg');

                document.getElementById('image-result-preview').src = url;
                document.getElementById('image-result-info').textContent =
                    w + ' x ' + h + ' px — ' + formatBytes(blob.size) +
                    (blob.size < Tools.image.file.size
                        ? ' (-' + Math.round((1 - blob.size / Tools.image.file.size) * 100) + '%)'
                        : '');

                var downloadBtn = document.getElementById('image-download');
                downloadBtn.href = url;
                downloadBtn.download = 'imagem-editada.' + ext;

                document.getElementById('image-result').classList.remove('d-none');
            }, mime, quality);
        }
    };

    // ── Reduzir PDF ───────────────────────────────────────────────────────
    Tools.pdf = {
        compress: function (btn) {
            var input = document.getElementById('pdf-input');
            var errorEl = document.getElementById('pdf-error');
            var resultEl = document.getElementById('pdf-result');
            errorEl.textContent = '';
            resultEl.classList.add('d-none');

            if (!input.files[0]) { errorEl.textContent = 'Selecione um arquivo PDF.'; return; }
            if (input.files[0].size > 15 * 1024 * 1024) { errorEl.textContent = 'Arquivo muito grande (máx. 15MB).'; return; }

            var fd = new FormData();
            fd.append('pdf', input.files[0]);
            fd.append('level', document.getElementById('pdf-level').value);

            var originalLabel = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Reduzindo…';

            fetch(<?= json_encode(url('/tools/pdf-compress')) ?>, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.disabled = false;
                    btn.innerHTML = originalLabel;
                    if (!d.success) { errorEl.textContent = d.error || 'Erro ao reduzir PDF.'; return; }

                    var reduction = Math.round((1 - d.compressed_size / d.original_size) * 100);
                    document.getElementById('pdf-sizes').innerHTML =
                        'Original: ' + formatBytes(d.original_size) + ' → Reduzido: ' + formatBytes(d.compressed_size) +
                        (reduction > 0
                            ? ' <strong style="color:var(--color-success);">(-' + reduction + '%)</strong>'
                            : ' <span style="color:var(--color-text-muted);">(já estava otimizado)</span>');
                    document.getElementById('pdf-download').href = d.pdf;
                    resultEl.classList.remove('d-none');
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = originalLabel;
                    errorEl.textContent = 'Erro de conexão.';
                });
        }
    };

    // ── Consulta de CEP ─────────────────────────────────────────────────
    var cepInput = document.getElementById('cep-input');
    cepInput.addEventListener('input', function () {
        var digits = cepInput.value.replace(/\D/g, '').slice(0, 8);
        cepInput.value = digits.length > 5 ? digits.slice(0, 5) + '-' + digits.slice(5) : digits;
    });
    cepInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') Tools.cep.lookup();
    });

    Tools.cep = {
        lookup: function () {
            var digits = cepInput.value.replace(/\D/g, '');
            var errEl = document.getElementById('cep-error');
            var resultEl = document.getElementById('cep-result');
            errEl.textContent = '';
            resultEl.classList.add('d-none');

            if (digits.length !== 8) {
                errEl.textContent = 'Digite um CEP com 8 dígitos.';
                return;
            }

            var body = new URLSearchParams({ cep: digits });
            fetch(<?= json_encode(url('/tools/cep')) ?>, { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d.success) { errEl.textContent = d.error || 'CEP não encontrado.'; return; }
                    document.getElementById('cep-logradouro').textContent = d.data.logradouro || '—';
                    document.getElementById('cep-bairro').textContent = d.data.bairro || '—';
                    document.getElementById('cep-cidade').textContent = d.data.cidade + ' / ' + d.data.uf;
                    document.getElementById('cep-ddd').textContent = d.data.ddd || '—';
                    resultEl.classList.remove('d-none');
                    resultEl.dataset.address = [d.data.logradouro, d.data.bairro, d.data.cidade + '/' + d.data.uf, digits.slice(0,5) + '-' + digits.slice(5)].filter(Boolean).join(', ');
                })
                .catch(function () { errEl.textContent = 'Erro de conexão.'; });
        },
        copy: function () {
            var resultEl = document.getElementById('cep-result');
            if (resultEl.dataset.address) navigator.clipboard.writeText(resultEl.dataset.address);
        }
    };

    // ── Calculadora de IMC ──────────────────────────────────────────────
    Tools.imc = {
        calculate: function () {
            var weight = parseFloat(document.getElementById('imc-weight').value);
            var heightCm = parseFloat(document.getElementById('imc-height').value);
            var box = document.getElementById('imc-result-box');

            if (!weight || !heightCm || weight <= 0 || heightCm <= 0) {
                box.classList.add('d-none');
                return;
            }

            var heightM = heightCm / 100;
            var imc = weight / (heightM * heightM);

            var classification, color;
            if (imc < 18.5) { classification = 'Abaixo do peso'; color = 'var(--color-warning)'; }
            else if (imc < 25) { classification = 'Peso normal'; color = 'var(--color-success)'; }
            else if (imc < 30) { classification = 'Sobrepeso'; color = 'var(--color-warning)'; }
            else if (imc < 35) { classification = 'Obesidade grau I'; color = 'var(--color-danger)'; }
            else if (imc < 40) { classification = 'Obesidade grau II'; color = 'var(--color-danger)'; }
            else { classification = 'Obesidade grau III'; color = 'var(--color-danger)'; }

            document.getElementById('imc-result').textContent = imc.toFixed(1);
            var classEl = document.getElementById('imc-classification');
            classEl.textContent = classification;
            classEl.style.color = color;
            box.classList.remove('d-none');
        }
    };

    // ── Gerador de Senha ────────────────────────────────────────────────
    var lenInput = document.getElementById('password-length');
    lenInput.addEventListener('input', function () {
        document.getElementById('password-length-val').textContent = lenInput.value;
    });
    Tools.password = {
        generate: function () {
            var len = parseInt(lenInput.value, 10);
            var lower = 'abcdefghijklmnopqrstuvwxyz';
            var upper = document.getElementById('password-upper').checked ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '';
            var numbers = document.getElementById('password-numbers').checked ? '0123456789' : '';
            var symbols = document.getElementById('password-symbols').checked ? '!@#$%^&*()-_=+[]{}' : '';
            var pool = lower + upper + numbers + symbols;
            var arr = new Uint32Array(len);
            crypto.getRandomValues(arr);
            var pass = '';
            for (var i = 0; i < len; i++) pass += pool[arr[i] % pool.length];
            document.getElementById('password-output').value = pass;
        },
        copy: function () {
            var el = document.getElementById('password-output');
            if (el.value) navigator.clipboard.writeText(el.value);
        }
    };
    Tools.password.generate();

    // ── Validador CPF/CNPJ ──────────────────────────────────────────────
    function onlyDigits(s) { return (s || '').replace(/\D/g, ''); }

    function validateCPF(cpf) {
        cpf = onlyDigits(cpf);
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        var sum = 0, i;
        for (i = 0; i < 9; i++) sum += parseInt(cpf[i], 10) * (10 - i);
        var d1 = (sum * 10) % 11; if (d1 === 10) d1 = 0;
        if (d1 !== parseInt(cpf[9], 10)) return false;
        sum = 0;
        for (i = 0; i < 10; i++) sum += parseInt(cpf[i], 10) * (11 - i);
        var d2 = (sum * 10) % 11; if (d2 === 10) d2 = 0;
        return d2 === parseInt(cpf[10], 10);
    }

    function validateCNPJ(cnpj) {
        cnpj = onlyDigits(cnpj);
        if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
        var calc = function (base) {
            var weights = base.length === 12 ? [5,4,3,2,9,8,7,6,5,4,3,2] : [6,5,4,3,2,9,8,7,6,5,4,3,2];
            var sum = 0;
            for (var i = 0; i < base.length; i++) sum += parseInt(base[i], 10) * weights[i];
            var r = sum % 11;
            return r < 2 ? 0 : 11 - r;
        };
        var d1 = calc(cnpj.substr(0, 12));
        if (d1 !== parseInt(cnpj[12], 10)) return false;
        var d2 = calc(cnpj.substr(0, 12) + d1);
        return d2 === parseInt(cnpj[13], 10);
    }

    Tools.docs = {
        validate: function () {
            var raw = document.getElementById('docs-input').value;
            var digits = onlyDigits(raw);
            var el = document.getElementById('docs-result');
            var valid, type;
            if (digits.length === 11) { valid = validateCPF(digits); type = 'CPF'; }
            else if (digits.length === 14) { valid = validateCNPJ(digits); type = 'CNPJ'; }
            else { el.innerHTML = '<span class="text-warning">Informe um CPF (11 dígitos) ou CNPJ (14 dígitos).</span>'; return; }

            el.innerHTML = valid
                ? '<span class="text-success"><i class="bi bi-check-circle-fill"></i> ' + type + ' válido</span>'
                : '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + type + ' inválido</span>';
        }
    };

    // ── Gerador UUID ─────────────────────────────────────────────────────
    Tools.uuid = {
        generate: function () {
            document.getElementById('uuid-output').value = crypto.randomUUID();
        },
        copy: function () {
            var el = document.getElementById('uuid-output');
            if (el.value) navigator.clipboard.writeText(el.value);
        },
        generateBatch: function () {
            var n = Math.min(100, Math.max(1, parseInt(document.getElementById('uuid-batch-count').value, 10) || 1));
            var out = [];
            for (var i = 0; i < n; i++) out.push(crypto.randomUUID());
            document.getElementById('uuid-batch-output').value = out.join('\n');
        }
    };
    Tools.uuid.generate();

    // ── Base64 ───────────────────────────────────────────────────────────
    Tools.base64 = {
        encode: function () {
            var text = document.getElementById('base64-text').value;
            try {
                document.getElementById('base64-encoded').value = btoa(unescape(encodeURIComponent(text)));
            } catch (e) { document.getElementById('base64-encoded').value = 'Erro ao codificar.'; }
        },
        decode: function () {
            var b64 = document.getElementById('base64-encoded').value;
            try {
                document.getElementById('base64-text').value = decodeURIComponent(escape(atob(b64)));
            } catch (e) { document.getElementById('base64-text').value = 'Base64 inválido.'; }
        }
    };

    // ── Formatador JSON ──────────────────────────────────────────────────
    Tools.json = {
        format: function (pretty) {
            var input = document.getElementById('json-input');
            var errEl = document.getElementById('json-error');
            try {
                var parsed = JSON.parse(input.value);
                input.value = pretty ? JSON.stringify(parsed, null, 2) : JSON.stringify(parsed);
                errEl.textContent = '';
            } catch (e) {
                errEl.textContent = 'JSON inválido: ' + e.message;
            }
        }
    };

    // ── Conversor de Timestamp ────────────────────────────────────────────
    Tools.timestamp = {
        now: function () {
            document.getElementById('timestamp-input').value = Math.floor(Date.now() / 1000);
            Tools.timestamp.toDate();
        },
        toDate: function () {
            var ts = parseInt(document.getElementById('timestamp-input').value, 10);
            var el = document.getElementById('timestamp-to-date');
            if (isNaN(ts)) { el.textContent = ''; return; }
            el.textContent = new Date(ts * 1000).toLocaleString('pt-BR');
        }
    };
    document.getElementById('timestamp-input').addEventListener('input', Tools.timestamp.toDate);
    document.getElementById('date-input').addEventListener('input', function () {
        var el = document.getElementById('date-to-timestamp');
        var v = this.value;
        if (!v) { el.textContent = ''; return; }
        el.textContent = Math.floor(new Date(v).getTime() / 1000);
    });
    Tools.timestamp.now();

    // ── Contador de Palavras ──────────────────────────────────────────────
    document.getElementById('words-input').addEventListener('input', function () {
        var text = this.value;
        var words = text.trim() === '' ? [] : text.trim().split(/\s+/);
        var sentences = text.trim() === '' ? [] : text.split(/[.!?]+/).filter(function (s) { return s.trim() !== ''; });
        var paragraphs = text.trim() === '' ? [] : text.split(/\n+/).filter(function (p) { return p.trim() !== ''; });
        document.getElementById('words-count').textContent = words.length;
        document.getElementById('chars-count').textContent = text.length;
        document.getElementById('chars-nospace-count').textContent = text.replace(/\s/g, '').length;
        document.getElementById('sentences-count').textContent = sentences.length;
        document.getElementById('paragraphs-count').textContent = paragraphs.length;
    });

    // ── Lorem Ipsum ────────────────────────────────────────────────────────
    var LOREM_WORDS = ('lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor ' +
        'incididunt ut labore et dolore magna aliqua enim ad minim veniam quis nostrud exercitation ' +
        'ullamco laboris nisi aliquip ex ea commodo consequat duis aute irure in reprehenderit ' +
        'voluptate velit esse cillum eu fugiat nulla pariatur excepteur sint occaecat cupidatat ' +
        'non proident sunt culpa qui officia deserunt mollit anim id est laborum').split(' ');

    function loremSentence() {
        var len = 6 + Math.floor(Math.random() * 10);
        var words = [];
        for (var i = 0; i < len; i++) words.push(LOREM_WORDS[Math.floor(Math.random() * LOREM_WORDS.length)]);
        var s = words.join(' ');
        return s.charAt(0).toUpperCase() + s.slice(1) + '.';
    }

    Tools.lorem = {
        generate: function () {
            var n = Math.min(20, Math.max(1, parseInt(document.getElementById('lorem-count').value, 10) || 1));
            var paragraphs = [];
            for (var p = 0; p < n; p++) {
                var sentences = [];
                var sc = 3 + Math.floor(Math.random() * 4);
                for (var s = 0; s < sc; s++) sentences.push(loremSentence());
                paragraphs.push(sentences.join(' '));
            }
            document.getElementById('lorem-output').value = paragraphs.join('\n\n');
        },
        copy: function () {
            var el = document.getElementById('lorem-output');
            if (el.value) navigator.clipboard.writeText(el.value);
        }
    };
    Tools.lorem.generate();

    // ── Extrator de Cores ──────────────────────────────────────────────────
    var colorsCanvas = document.getElementById('colors-canvas');
    var colorsCtx = colorsCanvas.getContext('2d');

    document.getElementById('colors-input').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var img = new Image();
        img.onload = function () {
            var maxW = colorsCanvas.parentElement.clientWidth || 400;
            var scale = Math.min(1, maxW / img.width);
            colorsCanvas.width = img.width * scale;
            colorsCanvas.height = img.height * scale;
            colorsCtx.drawImage(img, 0, 0, colorsCanvas.width, colorsCanvas.height);
            extractPalette();
        };
        img.src = URL.createObjectURL(file);
    });

    function rgbToHex(r, g, b) {
        return '#' + [r, g, b].map(function (v) { return v.toString(16).padStart(2, '0'); }).join('');
    }

    function extractPalette() {
        var w = colorsCanvas.width, h = colorsCanvas.height;
        if (!w || !h) return;
        var data = colorsCtx.getImageData(0, 0, w, h).data;
        var buckets = {};
        var step = Math.max(1, Math.floor((w * h) / 4000));

        for (var i = 0; i < data.length; i += 4 * step) {
            var r = data[i], g = data[i + 1], b = data[i + 2], a = data[i + 3];
            if (a < 128) continue;
            var key = (r >> 4) + ',' + (g >> 4) + ',' + (b >> 4);
            if (!buckets[key]) buckets[key] = { r: 0, g: 0, b: 0, count: 0 };
            buckets[key].r += r; buckets[key].g += g; buckets[key].b += b; buckets[key].count++;
        }

        var sorted = Object.values(buckets).sort(function (a, b) { return b.count - a.count; }).slice(0, 8);
        var html = sorted.map(function (bucket) {
            var hex = rgbToHex(Math.round(bucket.r / bucket.count), Math.round(bucket.g / bucket.count), Math.round(bucket.b / bucket.count));
            return '<div style="cursor:pointer;text-align:center;" onclick="navigator.clipboard.writeText(\'' + hex + '\')">' +
                '<div style="width:44px;height:44px;border-radius:8px;background:' + hex + ';border:1px solid var(--color-border);"></div>' +
                '<small style="color:var(--color-text-muted);">' + hex + '</small></div>';
        }).join('');
        document.getElementById('colors-palette').innerHTML = html || '<span style="color:var(--color-text-muted);">Nenhuma cor detectada.</span>';
    }

    colorsCanvas.addEventListener('click', function (e) {
        var rect = colorsCanvas.getBoundingClientRect();
        var x = Math.floor((e.clientX - rect.left) * (colorsCanvas.width / rect.width));
        var y = Math.floor((e.clientY - rect.top) * (colorsCanvas.height / rect.height));
        if (x < 0 || y < 0 || x >= colorsCanvas.width || y >= colorsCanvas.height) return;
        var d = colorsCtx.getImageData(x, y, 1, 1).data;
        var hex = rgbToHex(d[0], d[1], d[2]);
        document.getElementById('colors-picked').innerHTML =
            '<div class="d-flex align-items-center gap-2">' +
            '<div style="width:32px;height:32px;border-radius:6px;background:' + hex + ';border:1px solid var(--color-border);"></div>' +
            '<code>' + hex + '</code>' +
            '<button class="btn btn-sm btn-outline-light py-0" onclick="navigator.clipboard.writeText(\'' + hex + '\')"><i class="bi bi-clipboard"></i></button>' +
            '</div>';
    });
})();
</script>
