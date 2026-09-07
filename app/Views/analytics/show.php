<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.85rem;">
                <li class="breadcrumb-item"><a href="<?= url('/analytics') ?>" style="color:var(--color-accent);">Analytics</a></li>
                <li class="breadcrumb-item active" style="color:var(--color-text-muted);"><?= e(truncate($qr['label'] ?: $qr['uuid'], 40)) ?></li>
            </ol>
        </nav>
        <h2 class="display-font mb-0"><?= e($qr['label'] ?: 'QR Code') ?></h2>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/qr/' . $qr['uuid']) ?>" class="btn btn-outline-light btn-sm">
            <i class="bi bi-eye"></i> Ver QR
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <div class="display-font" style="font-size:2rem;color:var(--color-accent);"><?= number_format((int) $qr['scan_count']) ?></div>
            <small style="color:var(--color-text-secondary);">Total de scans</small>
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
        <div class="card p-3 text-center">
            <?php $topDevice = !empty($byDevice) ? $byDevice[0]['device'] : '—'; ?>
            <div class="display-font" style="font-size:1.4rem;color:var(--color-warning);padding-top:.4rem;"><?= e($topDevice) ?></div>
            <small style="color:var(--color-text-secondary);">Dispositivo principal</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3 text-center">
            <?php $topCountry = !empty($byCountry) ? $byCountry[0]['country'] : '—'; ?>
            <div class="display-font" style="font-size:1.4rem;color:var(--color-accent-alt);padding-top:.4rem;"><?= e($topCountry) ?></div>
            <small style="color:var(--color-text-secondary);">País principal</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Gráfico de scans diários -->
    <div class="col-lg-8">
        <div class="card p-4">
            <h6 class="display-font mb-3">Scans por dia (30 dias)</h6>
            <?php if (empty($byDay)): ?>
                <p style="color:var(--color-text-muted);" class="text-center py-4">Nenhum scan ainda.</p>
            <?php else: ?>
                <canvas id="chartDaily" height="120"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dispositivos -->
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="display-font mb-3">Dispositivos</h6>
            <?php if (empty($byDevice)): ?>
                <p style="color:var(--color-text-muted);">Sem dados.</p>
            <?php else: ?>
                <canvas id="chartDevice" height="180"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Países -->
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="display-font mb-3">Países</h6>
            <?php if (empty($byCountry)): ?>
                <p style="color:var(--color-text-muted);">Sem dados de geolocalização.</p>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </div>

    <!-- Scans recentes -->
    <div class="col-lg-7">
        <div class="card p-4">
            <h6 class="display-font mb-3">Últimos scans</h6>
            <?php if (empty($recent)): ?>
                <p style="color:var(--color-text-muted);">Nenhum scan registrado.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr style="color:var(--color-text-secondary);">
                                <th>Dispositivo</th>
                                <th>País</th>
                                <th>Cidade</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $s): ?>
                                <tr>
                                    <td>
                                        <?php $icons = ['desktop'=>'bi-monitor','mobile'=>'bi-phone','tablet'=>'bi-tablet','bot'=>'bi-robot','unknown'=>'bi-question-circle']; ?>
                                        <i class="bi <?= $icons[$s['device']] ?? 'bi-question-circle' ?>"></i>
                                        <small style="color:var(--color-text-secondary);"> <?= e($s['device']) ?></small>
                                    </td>
                                    <td style="color:var(--color-text-secondary);"><?= e($s['country'] ?: '—') ?></td>
                                    <td style="color:var(--color-text-secondary);"><?= e($s['city'] ?: '—') ?></td>
                                    <td style="color:var(--color-text-muted);white-space:nowrap;"><?= e(date('d/m H:i', strtotime($s['scanned_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($byDay) || !empty($byDevice)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const gridColor = '#1E3A5F';
    const tickColor = '#94A3B8';

    <?php if (!empty($byDay)): ?>
    new Chart(document.getElementById('chartDaily'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($byDay, 'day')) ?>,
            datasets: [{
                label: 'Scans',
                data: <?= json_encode(array_map('intval', array_column($byDay, 'total'))) ?>,
                backgroundColor: 'rgba(46,134,171,.7)',
                borderColor: '#2E86AB',
                borderWidth: 1,
                borderRadius: 4,
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

    <?php if (!empty($byDevice)): ?>
    const deviceColors = {
        desktop: '#2E86AB', mobile: '#22C55E', tablet: '#F59E0B',
        bot: '#EF4444', unknown: '#4E6B87'
    };
    const devData = <?= json_encode($byDevice) ?>;
    new Chart(document.getElementById('chartDevice'), {
        type: 'doughnut',
        data: {
            labels: devData.map(d => d.device),
            datasets: [{
                data: devData.map(d => parseInt(d.total)),
                backgroundColor: devData.map(d => deviceColors[d.device] || '#4E6B87'),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: tickColor, boxWidth: 12 }
                }
            }
        }
    });
    <?php endif; ?>
})();
</script>
<?php endif; ?>
