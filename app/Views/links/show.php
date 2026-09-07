<?php
$typeColors = [
    'none' => 'var(--color-text-muted)', 'intersticial' => '#F59E0B',
    'utm'  => '#2E86AB', 'conditional' => '#A23B72',
    'ab'   => '#22C55E', 'cloaking'    => '#EF4444',
];
$typeColor = $typeColors[$link['wrapper_type']] ?? 'var(--color-text-muted)';
$shortUrl  = url('/r/' . $link['slug']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.85rem;">
                <li class="breadcrumb-item"><a href="<?= url('/links') ?>" style="color:var(--color-accent);">Links</a></li>
                <li class="breadcrumb-item active" style="color:var(--color-text-muted);"><?= e($link['slug']) ?></li>
            </ol>
        </nav>
        <h2 class="display-font mb-0"><?= e($link['title'] ?: '/r/' . $link['slug']) ?></h2>
    </div>
    <a href="<?= url('/links/create?edit=' . $link['uuid']) ?>"
       onclick="window.location='<?= url('/links/' . $link['uuid'] . '/edit') ?>'; return false;"
       class="btn btn-outline-light btn-sm">
        <i class="bi bi-pencil"></i> Editar
    </a>
</div>

<!-- Info cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <div class="display-font" style="font-size:2rem;color:var(--color-accent);"><?= number_format((int) $link['click_count']) ?></div>
            <small style="color:var(--color-text-secondary);">Total de cliques</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <?php $last30 = array_sum(array_column($byDay, 'total')); ?>
            <div class="display-font" style="font-size:2rem;color:var(--color-success);"><?= number_format($last30) ?></div>
            <small style="color:var(--color-text-secondary);">Últimos 30 dias</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center" style="cursor:default;">
            <div style="font-size:1.1rem;font-weight:700;color:<?= $typeColor ?>;padding-top:.3rem;"><?= e($link['wrapper_type']) ?></div>
            <small style="color:var(--color-text-secondary);">Encapsulador</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <?php $status = (bool)$link['active'] ? 'ativo' : 'inativo'; ?>
            <div style="font-size:1.1rem;padding-top:.3rem;
                 color:<?= (bool)$link['active'] ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                <?= $status ?>
            </div>
            <small style="color:var(--color-text-secondary);">Status</small>
        </div>
    </div>
</div>

<!-- Short URL -->
<div class="card p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="display-font mb-0">URL Curta</h6>
        <a href="<?= url('/links/' . $link['uuid'] . '/edit') ?>"
           class="btn btn-sm btn-outline-light" style="display:none;" id="editBtn">Editar</a>
    </div>
    <div class="input-group input-group-sm">
        <input type="text" class="form-control" id="shortUrlInput"
               value="<?= e($shortUrl) ?>" readonly>
        <button class="btn btn-outline-light" type="button" onclick="copyUrl()">
            <i class="bi bi-clipboard" id="copyIcon"></i>
        </button>
        <a href="<?= e($shortUrl) ?>" target="_blank" class="btn btn-outline-light">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
    </div>
    <small style="color:var(--color-text-muted);" class="mt-1 d-block">
        Destino: <?= e($link['destination']) ?>
    </small>
</div>

<div class="row g-4 mb-4">
    <!-- Gráfico cliques por dia -->
    <div class="col-lg-8">
        <div class="card p-4">
            <h6 class="display-font mb-3">Cliques por dia (30 dias)</h6>
            <?php if (empty($byDay)): ?>
                <p style="color:var(--color-text-muted);" class="text-center py-4">Nenhum clique ainda.</p>
            <?php else: ?>
                <canvas id="chartDaily" height="120"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dispositivos -->
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="display-font mb-3"><?= $link['wrapper_type'] === 'ab' ? 'Variantes A/B' : 'Dispositivos' ?></h6>
            <?php if ($link['wrapper_type'] === 'ab' && !empty($byVariant)): ?>
                <canvas id="chartVariant" height="180"></canvas>
            <?php elseif (!empty($byDevice)): ?>
                <canvas id="chartDevice" height="180"></canvas>
            <?php else: ?>
                <p style="color:var(--color-text-muted);">Sem dados.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Países + Cliques recentes -->
<div class="row g-4">
    <?php if (!empty($byCountry)): ?>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="display-font mb-3">Países</h6>
            <?php $maxC = (int) $byCountry[0]['total']; ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($byCountry as $c): ?>
                    <li class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span style="color:var(--color-text-primary);"><?= e($c['country']) ?></span>
                            <span style="color:var(--color-text-secondary);"><?= $c['total'] ?></span>
                        </div>
                        <div class="progress" style="height:4px;background:var(--color-elevated);">
                            <div class="progress-bar"
                                 style="width:<?= round($c['total'] / $maxC * 100) ?>%;background:var(--color-accent);">
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($recent)): ?>
    <div class="col">
        <div class="card p-4">
            <h6 class="display-font mb-3">Últimos cliques</h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr style="color:var(--color-text-secondary);">
                            <th>Dispositivo</th>
                            <th>País</th>
                            <?php if ($link['wrapper_type'] === 'ab'): ?><th>Variante</th><?php endif; ?>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $cl):
                            $devIcons = ['desktop'=>'bi-monitor','mobile'=>'bi-phone','tablet'=>'bi-tablet','bot'=>'bi-robot','unknown'=>'bi-question-circle'];
                        ?>
                            <tr>
                                <td>
                                    <i class="bi <?= $devIcons[$cl['device']] ?? 'bi-question-circle' ?>"></i>
                                    <small style="color:var(--color-text-secondary);"> <?= e($cl['device']) ?></small>
                                </td>
                                <td style="color:var(--color-text-secondary);"><?= e($cl['country'] ?: '—') ?></td>
                                <?php if ($link['wrapper_type'] === 'ab'): ?>
                                    <td><span class="badge badge-type"><?= e($cl['variant'] ?: '—') ?></span></td>
                                <?php endif; ?>
                                <td style="color:var(--color-text-muted);"><?= e(date('d/m H:i', strtotime($cl['clicked_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($byDay) || !empty($byDevice) || !empty($byVariant)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const gridColor = '#1E3A5F', tickColor = '#94A3B8';

    <?php if (!empty($byDay)): ?>
    new Chart(document.getElementById('chartDaily'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($byDay, 'day')) ?>,
            datasets: [{
                label: 'Cliques',
                data: <?= json_encode(array_map('intval', array_column($byDay, 'total'))) ?>,
                backgroundColor: 'rgba(46,134,171,.7)', borderColor: '#2E86AB',
                borderWidth: 1, borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: tickColor, maxTicksLimit: 10 }, grid: { color: gridColor } },
                y: { beginAtZero: true, ticks: { color: tickColor, precision: 0 }, grid: { color: gridColor } }
            }
        }
    });
    <?php endif; ?>

    <?php if (!empty($byVariant)): ?>
    const varColors = ['#2E86AB','#22C55E','#F59E0B','#A23B72','#EF4444'];
    const varData = <?= json_encode($byVariant) ?>;
    new Chart(document.getElementById('chartVariant'), {
        type: 'doughnut',
        data: {
            labels: varData.map(v => v.variant),
            datasets: [{ data: varData.map(v => parseInt(v.total)), backgroundColor: varColors, borderWidth: 0 }]
        },
        options: { responsive: true, plugins: { legend: { position:'bottom', labels:{ color:tickColor, boxWidth:12 } } } }
    });
    <?php elseif (!empty($byDevice)): ?>
    const devColors = {desktop:'#2E86AB',mobile:'#22C55E',tablet:'#F59E0B',bot:'#EF4444',unknown:'#4E6B87'};
    const devData = <?= json_encode($byDevice) ?>;
    new Chart(document.getElementById('chartDevice'), {
        type: 'doughnut',
        data: {
            labels: devData.map(d => d.device),
            datasets: [{ data: devData.map(d => parseInt(d.total)), backgroundColor: devData.map(d => devColors[d.device]||'#4E6B87'), borderWidth: 0 }]
        },
        options: { responsive: true, plugins: { legend: { position:'bottom', labels:{ color:tickColor, boxWidth:12 } } } }
    });
    <?php endif; ?>
})();
</script>
<?php endif; ?>

<script>
function copyUrl() {
    navigator.clipboard.writeText(document.getElementById('shortUrlInput').value).then(() => {
        const i = document.getElementById('copyIcon');
        i.className = 'bi bi-check-lg';
        setTimeout(() => { i.className = 'bi bi-clipboard'; }, 1500);
    });
}
</script>
