<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0">Analytics</h2>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size:2rem;color:var(--color-accent);"><i class="bi bi-qr-code"></i></div>
                <div>
                    <div class="display-font" style="font-size:1.8rem;line-height:1;"><?= count($topQRs) ?>+</div>
                    <small style="color:var(--color-text-secondary);">QR Codes</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size:2rem;color:var(--color-success);"><i class="bi bi-bar-chart-line"></i></div>
                <div>
                    <div class="display-font" style="font-size:1.8rem;line-height:1;"><?= number_format($totalScans) ?></div>
                    <small style="color:var(--color-text-secondary);">Total de scans</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size:2rem;color:var(--color-warning);"><i class="bi bi-calendar-week"></i></div>
                <div>
                    <?php $last30 = array_sum(array_column($dailyScans, 'total')); ?>
                    <div class="display-font" style="font-size:1.8rem;line-height:1;"><?= number_format($last30) ?></div>
                    <small style="color:var(--color-text-secondary);">Últimos 30 dias</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size:2rem;color:var(--color-accent-alt);"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <?php
                    $avg = count($topQRs) > 0
                        ? array_sum(array_column($topQRs, 'scan_count')) / count($topQRs)
                        : 0;
                    ?>
                    <div class="display-font" style="font-size:1.8rem;line-height:1;"><?= number_format($avg, 1) ?></div>
                    <small style="color:var(--color-text-secondary);">Média por QR</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Gráfico de scans diários -->
    <div class="col-lg-8">
        <div class="card p-4">
            <h6 class="mb-3 display-font">Scans — últimos 30 dias</h6>
            <?php if (empty($dailyScans)): ?>
                <p style="color:var(--color-text-muted);" class="text-center py-4">Nenhum scan registrado ainda.</p>
            <?php else: ?>
                <canvas id="chartDaily" height="100"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top QR Codes -->
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="mb-3 display-font">Top QR Codes</h6>
            <?php if (empty($topQRs)): ?>
                <p style="color:var(--color-text-muted);">Nenhum QR Code ainda.</p>
            <?php else: ?>
                <?php $maxScans = max(1, (int) $topQRs[0]['scan_count']); ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($topQRs as $qr): ?>
                        <li class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <a href="<?= url('/analytics/' . $qr['uuid']) ?>"
                                   style="color:var(--color-text-primary);text-decoration:none;font-size:.9rem;">
                                    <?= e(truncate($qr['label'] ?: $qr['uuid'], 28)) ?>
                                </a>
                                <span class="badge badge-type"><?= (int) $qr['scan_count'] ?></span>
                            </div>
                            <div class="progress" style="height:4px;background:var(--color-elevated);">
                                <div class="progress-bar" role="progressbar"
                                     style="width:<?= round((int) $qr['scan_count'] / $maxScans * 100) ?>%;background:var(--color-accent);">
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scans recentes -->
<?php if (!empty($recentScans)): ?>
<div class="card mt-4 p-4">
    <h6 class="mb-3 display-font">Scans recentes</h6>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr style="color:var(--color-text-secondary);">
                    <th>QR Code</th>
                    <th>Dispositivo</th>
                    <th>País</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentScans as $s): ?>
                    <tr>
                        <td>
                            <a href="<?= url('/analytics/' . $s['qr_uuid']) ?>"
                               style="color:var(--color-accent);text-decoration:none;">
                                <?= e($s['label'] ?: $s['qr_uuid']) ?>
                            </a>
                            <small class="ms-1 badge badge-type"><?= e($s['qr_type']) ?></small>
                        </td>
                        <td>
                            <?php $icons = ['desktop'=>'bi-monitor','mobile'=>'bi-phone','tablet'=>'bi-tablet','bot'=>'bi-robot','unknown'=>'bi-question-circle']; ?>
                            <i class="bi <?= $icons[$s['device']] ?? 'bi-question-circle' ?>"></i>
                            <small style="color:var(--color-text-secondary);"> <?= e($s['device']) ?></small>
                        </td>
                        <td style="color:var(--color-text-secondary);"><?= e($s['country'] ?: '—') ?></td>
                        <td style="color:var(--color-text-muted);"><?= e(date('d/m/Y H:i', strtotime($s['scanned_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($dailyScans)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = <?= json_encode(array_column($dailyScans, 'day')) ?>;
    const values = <?= json_encode(array_map('intval', array_column($dailyScans, 'total'))) ?>;

    new Chart(document.getElementById('chartDaily'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Scans',
                data: values,
                borderColor: '#2E86AB',
                backgroundColor: 'rgba(46,134,171,.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    ticks: { color: '#94A3B8', maxTicksLimit: 10 },
                    grid: { color: '#1E3A5F' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#94A3B8', precision: 0 },
                    grid: { color: '#1E3A5F' }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>
