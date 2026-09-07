<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="display-font mb-0"><i class="bi bi-calendar-check" style="color:var(--color-accent);"></i> Agenda</h2>
</div>

<ul class="nav nav-pills mb-4 gap-1">
    <?php
    $tabs = ['' => 'Todos', 'pending' => 'Pendentes', 'confirmed' => 'Confirmados', 'cancelled' => 'Cancelados'];
    foreach ($tabs as $value => $label):
    ?>
    <li class="nav-item">
        <a class="nav-link <?= $status === $value ? 'active' : '' ?>" href="<?= url('/agenda' . ($value !== '' ? '?status=' . $value : '')) ?>"
           style="<?= $status === $value ? '' : 'color:var(--color-text-secondary);' ?>">
            <?= $label ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<?php if (empty($bookings)): ?>
<div class="card p-5 text-center">
    <i class="bi bi-calendar-check" style="font-size:3rem;color:var(--color-accent);opacity:.5;"></i>
    <p class="mt-3 mb-0" style="color:var(--color-text-muted);">Nenhum agendamento encontrado.</p>
</div>
<?php else: ?>
<div class="card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr style="color:var(--color-text-muted);font-size:.8rem;">
                    <th class="ps-3">Data / Hora</th>
                    <th>Hub</th>
                    <th>Cliente</th>
                    <th>Contato</th>
                    <th>Observações</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr data-booking-id="<?= (int) $b['id'] ?>">
                    <td class="ps-3">
                        <?= date('d/m/Y', strtotime($b['booking_date'])) ?>
                        <div style="color:var(--color-text-muted);font-size:.8rem;">
                            <?= substr($b['start_time'], 0, 5) ?> – <?= substr($b['end_time'], 0, 5) ?>
                        </div>
                    </td>
                    <td>
                        <?= e($b['hub_title']) ?>
                        <div style="color:var(--color-text-muted);font-size:.8rem;"><?= e($b['block_title'] ?: 'Agenda') ?></div>
                    </td>
                    <td><?= e($b['customer_name']) ?></td>
                    <td style="color:var(--color-text-secondary);"><?= e($b['customer_contact']) ?></td>
                    <td style="color:var(--color-text-secondary);font-size:.85rem;"><?= e(truncate((string) ($b['notes'] ?? ''), 60)) ?></td>
                    <td>
                        <?php
                        $statusMap = [
                            'pending'   => ['warning', 'pendente'],
                            'confirmed' => ['success', 'confirmado'],
                            'cancelled' => ['danger', 'cancelado'],
                        ];
                        [$cls, $label] = $statusMap[$b['status']] ?? ['secondary', $b['status']];
                        ?>
                        <span class="badge status-badge" style="background:var(--color-<?= $cls ?>);"><?= $label ?></span>
                    </td>
                    <td class="text-end pe-3">
                        <?php if ($b['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-outline-light" onclick="updateBooking(<?= (int) $b['id'] ?>, 'confirm')">
                            <i class="bi bi-check-lg"></i> Confirmar
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="updateBooking(<?= (int) $b['id'] ?>, 'cancel')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <?php elseif ($b['status'] === 'confirmed'): ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="updateBooking(<?= (int) $b['id'] ?>, 'cancel')">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </button>
                        <?php else: ?>
                        <span style="color:var(--color-text-muted);font-size:.8rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
const CSRF = <?= json_encode($csrf_token) ?>;
const AGENDA_BASE = <?= json_encode(url('/agenda/')) ?>;

function updateBooking(id, action) {
    if (action === 'cancel' && !confirm('Cancelar este agendamento? O horário voltará a ficar disponível.')) return;

    fetch(AGENDA_BASE + id + '/' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(CSRF),
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.error || 'Erro ao atualizar agendamento.');
    });
}
</script>
