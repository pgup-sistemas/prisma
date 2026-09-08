<section class="py-5" style="background:var(--gradient-brand);">
    <div class="container" style="max-width:900px;">
        <div class="text-center mb-5">
            <span class="badge rounded-pill mb-3" style="background:rgba(46,134,171,.15);color:var(--color-accent);padding:.5rem 1rem;">
                <i class="bi bi-lightning-charge-fill me-1"></i>PRISMA Launcher
            </span>
            <h1 class="display-font fw-bold" style="color:var(--color-text-primary);font-size:2.4rem;">
                Seus favoritos e links, a um atalho de distância
            </h1>
            <p class="lead" style="color:var(--color-text-secondary);max-width:620px;margin:0 auto;">
                Instale o agente no seu computador e abra uma busca ultrarrápida
                (mesma tecnologia do Launcher embutido) em qualquer lugar do sistema —
                sem precisar abrir o navegador antes.
            </p>
        </div>

        <div class="row g-3 mb-5">
            <?php
            $cards = [
                'linux'   => ['label' => 'Linux',   'icon' => 'bi-ubuntu'],
                'windows' => ['label' => 'Windows',  'icon' => 'bi-microsoft'],
                'mac'     => ['label' => 'macOS',    'icon' => 'bi-apple'],
            ];
            ?>
            <?php foreach ($cards as $os => $meta): $d = $downloads[$os]; ?>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi <?= $meta['icon'] ?>" style="font-size:2.2rem;color:var(--color-accent);"></i>
                    </div>
                    <h5 class="display-font" style="color:var(--color-text-primary);"><?= e($meta['label']) ?></h5>
                    <p class="small mb-3" style="color:var(--color-text-muted);">
                        PRISMA Launcher <?= e($version) ?>
                    </p>
                    <?php if ($d['available']): ?>
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <?php foreach ($d['variants'] as $variant): ?>
                                <a href="<?= e($variant['url']) ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-download me-1"></i>Baixar <?= e($variant['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary mt-auto" disabled style="border-color:var(--color-border);color:var(--color-text-muted);">
                            Em breve
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card p-4 p-md-5">
            <h4 class="display-font mb-4" style="color:var(--color-text-primary);">
                <i class="bi bi-list-ol me-2" style="color:var(--color-accent);"></i>Como instalar e configurar
            </h4>

            <div class="d-flex gap-3 mb-4">
                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center display-font fw-bold"
                     style="width:36px;height:36px;background:var(--gradient-accent);color:#fff;">1</div>
                <div>
                    <strong style="color:var(--color-text-primary);">Baixe e abra o instalador</strong>
                    <p class="mb-0 small" style="color:var(--color-text-secondary);">
                        No Linux, dê permissão de execução e rode: <code>chmod +x PRISMA-Launcher-*.AppImage &amp;&amp; ./PRISMA-Launcher-*.AppImage</code>.
                        No Windows/Mac, é só abrir o instalador normalmente quando disponível.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-3 mb-4">
                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center display-font fw-bold"
                     style="width:36px;height:36px;background:var(--gradient-accent);color:#fff;">2</div>
                <div>
                    <strong style="color:var(--color-text-primary);">Pegue seu UUID e Chave de API</strong>
                    <p class="mb-0 small" style="color:var(--color-text-secondary);">
                        <?php if ($loggedIn): ?>
                            Acesse <a href="<?= url('/profile') ?>" style="color:var(--color-accent);">Perfil → Widget Launcher</a>
                            e copie os valores de <code>data-user-id</code> e <code>data-api-key</code>.
                        <?php else: ?>
                            Faça <a href="<?= url('/login') ?>" style="color:var(--color-accent);">login</a> e acesse
                            Perfil → Widget Launcher para copiar os valores de <code>data-user-id</code> e <code>data-api-key</code>.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="d-flex gap-3 mb-4">
                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center display-font fw-bold"
                     style="width:36px;height:36px;background:var(--gradient-accent);color:#fff;">3</div>
                <div>
                    <strong style="color:var(--color-text-primary);">Configure na primeira execução</strong>
                    <p class="mb-0 small" style="color:var(--color-text-secondary);">
                        Na primeira vez, a tela de Configurações abre sozinha. Cole a URL do servidor
                        (<code><?= e(url('')) ?></code>), o UUID e a Chave de API. Depois, clique no
                        campo de atalho e pressione a combinação de teclas que quiser usar —
                        recomendamos <kbd>Ctrl</kbd>+<kbd>Alt</kbd>+<kbd>K</kbd>.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-3">
                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center display-font fw-bold"
                     style="width:36px;height:36px;background:var(--gradient-accent);color:#fff;">4</div>
                <div>
                    <strong style="color:var(--color-text-primary);">Pronto — use em qualquer lugar</strong>
                    <p class="mb-0 small" style="color:var(--color-text-secondary);">
                        O agente fica na bandeja do sistema. Aperte o atalho configurado para abrir a busca,
                        digite parte do título ou URL (mesmo com erro de digitação) e pressione Enter para abrir.
                        Você também pode gerar QR Code ou encurtar um link direto pela busca — veja abaixo.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="small" style="color:var(--color-text-muted);">
                Precisa reconfigurar depois? Clique com o botão direito no ícone da bandeja
                ou use o ícone de engrenagem dentro da própria busca.
            </p>
        </div>
    </div>
</section>
