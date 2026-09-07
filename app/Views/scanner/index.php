<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-camera" style="color:var(--color-accent);"></i> Scanner de QR Code</h2>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="display-font mb-3">Enviar imagem</h6>
            <p style="color:var(--color-text-secondary);font-size:.85rem;">
                Envie uma foto ou print contendo um QR Code. No celular, você pode usar a câmera diretamente.
            </p>
            <input type="file" id="scanner-input" class="form-control form-control-sm mb-3" accept="image/*" capture="environment">
            <img id="scanner-preview" class="w-100 rounded mb-3 d-none" style="max-height:280px;object-fit:contain;background:var(--color-elevated);" alt="">
            <button class="btn btn-primary w-100" id="scanner-submit" disabled>
                <i class="bi bi-search"></i> Escanear
            </button>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-4" id="scanner-result-card" style="display:none;">
            <h6 class="display-font mb-3">Resultado</h6>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge" style="background:var(--color-accent);" id="scanner-type-badge">—</span>
            </div>
            <textarea id="scanner-text" class="form-control form-control-sm mb-3" rows="4" readonly style="font-family:var(--font-mono);font-size:.85rem;"></textarea>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-light btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('scanner-text').value)">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
                <a id="scanner-open-link" href="#" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm d-none">
                    <i class="bi bi-box-arrow-up-right"></i> Abrir link
                </a>
                <a href="<?= url('/generate') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-qr-code"></i> Gerar novo QR
                </a>
            </div>
        </div>
        <div class="card p-4 text-center" id="scanner-empty-card">
            <i class="bi bi-upc-scan" style="font-size:3rem;color:var(--color-accent);opacity:.5;"></i>
            <p class="mt-3 mb-0" style="color:var(--color-text-muted);">Envie uma imagem para ver o resultado aqui.</p>
        </div>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('scanner-input');
    var preview = document.getElementById('scanner-preview');
    var submitBtn = document.getElementById('scanner-submit');
    var resultCard = document.getElementById('scanner-result-card');
    var emptyCard = document.getElementById('scanner-empty-card');
    var CSRF = <?= json_encode($csrf_token) ?>;
    var SCAN_URL = <?= json_encode(url('/scanner')) ?>;

    input.addEventListener('change', function () {
        var file = input.files[0];
        resultCard.style.display = 'none';
        emptyCard.style.display = '';
        if (!file) {
            preview.classList.add('d-none');
            submitBtn.disabled = true;
            return;
        }
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
        submitBtn.disabled = false;
    });

    submitBtn.addEventListener('click', function () {
        var file = input.files[0];
        if (!file) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Escaneando…';

        var fd = new FormData();
        fd.append('image', file);
        fd.append('_csrf', CSRF);

        fetch(SCAN_URL, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-search"></i> Escanear';

                if (!d.success) {
                    alert(d.error || 'Não foi possível ler o QR Code.');
                    return;
                }

                document.getElementById('scanner-type-badge').textContent = d.type;
                document.getElementById('scanner-text').value = d.text;

                var openLink = document.getElementById('scanner-open-link');
                if (d.type === 'url') {
                    openLink.href = d.text;
                    openLink.classList.remove('d-none');
                } else {
                    openLink.classList.add('d-none');
                }

                emptyCard.style.display = 'none';
                resultCard.style.display = '';
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-search"></i> Escanear';
                alert('Erro de conexão ao escanear.');
            });
    });
})();
</script>
