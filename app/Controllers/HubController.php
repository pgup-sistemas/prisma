<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Cache;
use App\Core\Middleware;
use App\Models\HubPage;
use App\Models\HubBlock;
use App\Models\HubBooking;
use App\Services\MailerService;
use App\Services\QRGenerator;

class HubController extends Controller
{
    private const MAX_BLOCKS = 50;

    // ── Admin (autenticado) ───────────────────────────────────────────────

    public function index(): void
    {
        $this->requireAuth();
        $pages = HubPage::forUser($this->user()['id']);
        $this->render('hub/index', ['pages' => $pages], 'main');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->render('hub/editor', [
            'hub'    => null,
            'blocks' => [],
            'mode'   => 'create',
        ], 'main');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $user = $this->user();
        $data = $this->request->all();

        $slug = trim($data['slug'] ?? '');
        if ($slug === '') {
            $slug = $this->generateSlug($data['title'] ?? 'meu-hub');
        }
        $slug = $this->sanitizeSlug($slug);

        if (HubPage::slugExists($slug)) {
            $this->flash('error', 'Este slug já está em uso. Escolha outro.');
            $this->redirect(url('/hub/create'));
            return;
        }

        $id = HubPage::create([
            'uuid'        => uuid4(),
            'user_id'     => $user['id'],
            'slug'        => $slug,
            'title'       => trim($data['title'] ?? 'Meu Hub'),
            'bio'         => trim($data['bio'] ?? ''),
            'theme_color' => $this->sanitizeColor($data['theme_color'] ?? '#2E86AB'),
            'active'      => 1,
        ]);

        $hub = HubPage::find($id);
        $this->flash('success', 'Hub criado com sucesso!');
        $this->redirect(url('/hub/' . $hub['uuid'] . '/edit'));
    }

    public function edit(string $uuid): void
    {
        $this->requireAuth();
        $hub = $this->ownedHub($uuid);
        $blocks = HubBlock::forHubAll($hub['id']);

        // Decodifica blocks_json para exibição
        foreach ($blocks as &$b) {
            $b['blocks_data'] = json_decode($b['blocks_json'] ?? '{}', true) ?: [];
        }

        $this->render('hub/editor', [
            'hub'    => $hub,
            'blocks' => $blocks,
            'mode'   => 'edit',
        ], 'main');
    }

    public function update(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $hub  = $this->ownedHub($uuid);
        $data = $this->request->all();

        $slug = $this->sanitizeSlug(trim($data['slug'] ?? $hub['slug']));
        if ($slug !== $hub['slug'] && HubPage::slugExists($slug, $hub['id'])) {
            $this->flash('error', 'Slug já em uso. Escolha outro.');
            $this->redirect(url('/hub/' . $uuid . '/edit'));
            return;
        }

        $avatar = $hub['avatar'];
        if (!empty($_FILES['avatar']['tmp_name']) && $_FILES['avatar']['size'] > 0) {
            $avatar = $this->uploadAvatar($_FILES['avatar'], $uuid);
        }

        HubPage::update($hub['id'], [
            'slug'        => $slug,
            'title'       => trim($data['title'] ?? $hub['title']),
            'bio'         => trim($data['bio'] ?? ''),
            'theme_color' => $this->sanitizeColor($data['theme_color'] ?? $hub['theme_color']),
            'avatar'      => $avatar,
            'active'      => isset($data['active']) ? 1 : 0,
        ]);

        $this->flash('success', 'Hub atualizado.');
        $this->redirect(url('/hub/' . $uuid . '/edit'));
    }

    public function delete(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $hub = $this->ownedHub($uuid);
        HubPage::delete($hub['id']);
        $this->flash('success', 'Hub excluído.');
        $this->json(['success' => true]);
    }

    // ── Blocos ──────────────────────────────────────────────────────────

    public function addBlock(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $hub  = $this->ownedHub($uuid);
        $data = $this->request->all();

        if (HubBlock::countForHub($hub['id']) >= self::MAX_BLOCKS) {
            $this->json(['success' => false, 'error' => 'Limite de ' . self::MAX_BLOCKS . ' blocos atingido.'], 422);
            return;
        }

        $type  = $data['type'] ?? 'link';
        $title = trim($data['title'] ?? '');
        $bData = $this->parseBlockData($type, $data);

        $id = HubBlock::create([
            'hub_id'      => $hub['id'],
            'type'        => $type,
            'title'       => $title,
            'blocks_json' => json_encode($bData),
            'position'    => HubBlock::maxPosition($hub['id']) + 1,
            'active'      => 1,
        ]);

        $this->json(['success' => true, 'id' => $id]);
    }

    public function updateBlock(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $id = (int) $id;
        $block = $this->ownedBlock($id);
        $data  = $this->request->all();

        $type  = $block['type'];
        $bData = $this->parseBlockData($type, $data);

        HubBlock::update($id, [
            'title'       => trim($data['title'] ?? $block['title']),
            'blocks_json' => json_encode($bData),
            'active'      => isset($data['active']) ? 1 : 0,
        ]);

        $this->json(['success' => true]);
    }

    public function deleteBlock(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $id = (int) $id;
        $this->ownedBlock($id);
        HubBlock::delete($id);
        $this->json(['success' => true]);
    }

    public function reorderBlocks(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $hub  = $this->ownedHub($uuid);
        $ids  = $this->request->input('ids', []);

        if (!is_array($ids)) {
            $this->json(['success' => false], 422);
            return;
        }

        HubBlock::reorder($hub['id'], array_map('intval', $ids));
        $this->json(['success' => true]);
    }

    // ── Página Pública ────────────────────────────────────────────────────

    public function public(string $slug): void
    {
        $hub = HubPage::findBySlug($slug);

        if ($hub === null) {
            http_response_code(404);
            $this->render('errors/404', [], 'public');
            return;
        }

        // Conta visita única por IP (TTL 1h)
        $ipHash  = ipHash($_SERVER['REMOTE_ADDR'] ?? '');
        $viewKey = 'hub:view:' . $slug . ':' . $ipHash;

        if (!Cache::get($viewKey)) {
            HubPage::incrementViews($hub['id']);
            Cache::set($viewKey, 1, 3600);
        }

        $blocks = HubBlock::forHub($hub['id']);
        foreach ($blocks as &$b) {
            $b['blocks_data'] = json_decode($b['blocks_json'] ?? '{}', true) ?: [];
        }

        $this->render('hub/public', [
            'hub'             => $hub,
            'blocks'          => $blocks,
            'show_public_nav' => false,
        ], 'public');
    }

    /**
     * Gera o payload EMV do PIX (copia-e-cola) + QR Code PNG sob demanda,
     * a partir do valor informado pelo visitante do Hub público. Não persiste
     * nada em disco — o valor é dinâmico por requisição, não faz sentido cachear.
     */
    public function pixGenerate(string $slug, string $blockId): void
    {
        Middleware::rateLimit('hub:pix:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 20, 60);

        $hub = HubPage::findBySlug($slug);
        if ($hub === null) {
            $this->json(['success' => false, 'error' => 'Hub não encontrado.'], 404);
            return;
        }

        $block = HubBlock::find((int) $blockId);
        if ($block === null || (int) $block['hub_id'] !== (int) $hub['id'] || $block['type'] !== 'pix' || !$block['active']) {
            $this->json(['success' => false, 'error' => 'Bloco PIX não encontrado.'], 404);
            return;
        }

        $data = json_decode((string) $block['blocks_json'], true) ?: [];

        if (empty($data['pix_key'])) {
            $this->json(['success' => false, 'error' => 'Este bloco PIX ainda não foi configurado.'], 422);
            return;
        }

        $fixedAmount = $data['fixed_amount'] !== '' ? (float) $data['fixed_amount'] : null;
        $allowCustom = !empty($data['allow_custom_amount']);

        if ($allowCustom) {
            $requested = $this->request->input('amount', '');
            $amount = $requested !== '' ? (float) str_replace(',', '.', (string) $requested) : $fixedAmount;
            if ($amount !== null && $amount <= 0) {
                $this->json(['success' => false, 'error' => 'Informe um valor maior que zero.'], 422);
                return;
            }
        } else {
            $amount = $fixedAmount;
        }

        $generator = new QRGenerator();
        $payload = $generator->buildPix([
            'key'         => $data['pix_key'],
            'name'        => $data['merchant_name'] ?? '',
            'city'        => $data['merchant_city'] ?? '',
            'amount'      => $amount,
            'description' => $data['description'] ?? null,
        ]);

        $tmpBase = 'hub-pix-' . uuid4();
        $files = $generator->render($payload, [
            'size'        => 400,
            'ecc_level'   => 'M',
            'fg_color'    => '#000000',
            'bg_color'    => '#FFFFFF',
            'transparent' => false,
        ], $tmpBase);

        $pngData = @file_get_contents(ROOT . '/' . $files['png']);
        @unlink(ROOT . '/' . $files['png']);
        @unlink(ROOT . '/' . $files['svg']);

        if ($pngData === false) {
            $this->json(['success' => false, 'error' => 'Falha ao gerar o QR Code.'], 500);
            return;
        }

        $this->json([
            'success'    => true,
            'copy_paste' => $payload,
            'qr_image'   => 'data:image/png;base64,' . base64_encode($pngData),
            'amount'     => $amount,
        ]);
    }

    /**
     * Recebe o envio do bloco "Formulário de contato" da página pública do Hub
     * e encaminha por e-mail para o endereço configurado no bloco.
     */
    public function submitContact(string $slug): void
    {
        Middleware::rateLimit('hub:contact:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 300);

        $hub = HubPage::findBySlug($slug);
        if ($hub === null) {
            $this->json(['success' => false, 'error' => 'Hub não encontrado.'], 404);
            return;
        }

        $blockId = (int) $this->request->input('block_id', 0);
        $block = HubBlock::find($blockId);
        if ($block === null || (int) $block['hub_id'] !== (int) $hub['id'] || $block['type'] !== 'contact_form' || !$block['active']) {
            $this->json(['success' => false, 'error' => 'Formulário não encontrado.'], 404);
            return;
        }

        $data = json_decode((string) $block['blocks_json'], true) ?: [];
        $fields = $data['fields'] ?? [];
        $emailTo = trim((string) ($data['email_to'] ?? ''));

        if ($emailTo === '' || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Este formulário ainda não foi configurado.'], 422);
            return;
        }

        $lines = [];
        foreach ($fields as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $value = trim((string) $this->request->input($name, ''));

            if (!empty($field['required']) && $value === '') {
                $this->json(['success' => false, 'error' => ucfirst($name) . ' é obrigatório.'], 422);
                return;
            }

            $lines[] = '<p><strong>' . e(ucfirst($name)) . ':</strong> ' . nl2br(e($value)) . '</p>';
        }

        if (empty($lines)) {
            $this->json(['success' => false, 'error' => 'Nenhum dado enviado.'], 422);
            return;
        }

        $html = '<h3>Nova mensagem via Hub: ' . e($hub['title']) . '</h3>' . implode('', $lines);
        $sent = MailerService::send($emailTo, 'Contato via Hub — ' . $hub['title'], $html);

        if (!$sent) {
            $this->json(['success' => false, 'error' => 'Falha ao enviar a mensagem. Tente novamente.'], 500);
            return;
        }

        $this->json(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
    }

    // ── Agenda (pública) ────────────────────────────────────────────────────

    private const AGENDA_MAX_DAYS_AHEAD = 90;

    private const DAY_NAMES = [
        1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta',
        5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo',
    ];

    /**
     * Lista os horários livres de um bloco de agenda numa data específica,
     * a partir das regras de disponibilidade configuradas e descontando
     * horários já ocupados por outros agendamentos.
     */
    public function agendaSlots(string $slug, string $blockId): void
    {
        Middleware::rateLimit('hub:agenda:slots:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 60, 60);

        $block = $this->findAgendaBlock($slug, $blockId);
        if ($block === null) {
            $this->json(['success' => false, 'error' => 'Agenda não encontrada.'], 404);
            return;
        }

        $date = (string) $this->request->input('date', '');
        $error = $this->validateAgendaDate($date);
        if ($error !== null) {
            $this->json(['success' => false, 'error' => $error], 422);
            return;
        }

        $slots = $this->generateAvailableSlots($block, $date);

        $this->json(['success' => true, 'slots' => $slots]);
    }

    /**
     * Cria um agendamento. Revalida a disponibilidade do horário no servidor
     * (nunca confia no horário vindo do cliente) antes de gravar.
     */
    public function agendaBook(string $slug, string $blockId): void
    {
        Middleware::rateLimit('hub:agenda:book:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 300);

        $block = $this->findAgendaBlock($slug, $blockId);
        if ($block === null) {
            $this->json(['success' => false, 'error' => 'Agenda não encontrada.'], 404);
            return;
        }

        $date = (string) $this->request->input('date', '');
        $startTime = (string) $this->request->input('start_time', '');
        $name = trim((string) $this->request->input('customer_name', ''));
        $contact = trim((string) $this->request->input('customer_contact', ''));
        $notes = trim((string) $this->request->input('notes', ''));

        $error = $this->validateAgendaDate($date);
        if ($error !== null) {
            $this->json(['success' => false, 'error' => $error], 422);
            return;
        }

        if ($name === '' || $contact === '') {
            $this->json(['success' => false, 'error' => 'Nome e contato são obrigatórios.'], 422);
            return;
        }

        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $startTime)) {
            $this->json(['success' => false, 'error' => 'Horário inválido.'], 422);
            return;
        }

        $availableSlots = $this->generateAvailableSlots($block, $date);
        if (!in_array($startTime, $availableSlots, true)) {
            $this->json(['success' => false, 'error' => 'Este horário não está mais disponível. Escolha outro.'], 409);
            return;
        }

        // Revalida logo antes de gravar — reduz (não elimina) a janela de corrida
        // entre duas pessoas escolhendo o mesmo horário ao mesmo tempo.
        if (HubBooking::hasConflict((int) $block['id'], $date, $startTime)) {
            $this->json(['success' => false, 'error' => 'Este horário acabou de ser reservado. Escolha outro.'], 409);
            return;
        }

        $blockData = json_decode((string) $block['blocks_json'], true) ?: [];
        $duration = max(5, (int) ($blockData['duration_minutes'] ?? 30));
        $endTime = date('H:i', strtotime($startTime) + $duration * 60);

        $id = HubBooking::create([
            'uuid'             => uuid4(),
            'hub_id'           => (int) $block['hub_id'],
            'block_id'         => (int) $block['id'],
            'customer_name'    => $name,
            'customer_contact' => $contact,
            'notes'            => $notes,
            'booking_date'     => $date,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'status'           => 'pending',
        ]);

        $this->json(['success' => true, 'id' => $id, 'message' => 'Horário reservado! Aguarde a confirmação.']);
    }

    private function findAgendaBlock(string $slug, string $blockId): ?array
    {
        $hub = HubPage::findBySlug($slug);
        if ($hub === null) {
            return null;
        }

        $block = HubBlock::find((int) $blockId);
        if ($block === null || (int) $block['hub_id'] !== (int) $hub['id'] || $block['type'] !== 'agenda' || !$block['active']) {
            return null;
        }

        return $block;
    }

    private function validateAgendaDate(string $date): ?string
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return 'Data inválida.';
        }

        $today = new \DateTime('today');
        $maxDate = (clone $today)->modify('+' . self::AGENDA_MAX_DAYS_AHEAD . ' days');

        if ($d < $today) {
            return 'Escolha uma data futura.';
        }
        if ($d > $maxDate) {
            return 'Data muito distante.';
        }

        return null;
    }

    /**
     * Gera os horários disponíveis de um bloco de agenda numa data, a partir
     * das regras de disponibilidade (dia da semana + faixa de horário) menos
     * os horários já ocupados por agendamentos ativos (pendentes/confirmados).
     */
    private function generateAvailableSlots(array $block, string $date): array
    {
        $data = json_decode((string) $block['blocks_json'], true) ?: [];
        $duration = max(5, (int) ($data['duration_minutes'] ?? 30));
        $rules = $data['hours'] ?? [];

        $dayName = self::DAY_NAMES[(int) date('N', strtotime($date))];
        $dayRules = array_filter($rules, fn (array $r) => ($r['day'] ?? '') === $dayName);

        if (empty($dayRules)) {
            return [];
        }

        $isToday = $date === date('Y-m-d');
        $now = time();

        $occupied = array_column(HubBooking::activeForBlockAndDate((int) $block['id'], $date), 'start_time');
        // TIME do MySQL vem como "HH:MM:SS" — normaliza para "HH:MM"
        $occupied = array_map(fn (string $t) => substr($t, 0, 5), $occupied);

        $slots = [];
        foreach ($dayRules as $rule) {
            $start = strtotime($date . ' ' . ($rule['open'] ?? '00:00'));
            $end = strtotime($date . ' ' . ($rule['close'] ?? '00:00'));
            if ($start === false || $end === false || $end <= $start) {
                continue;
            }

            for ($t = $start; $t + $duration * 60 <= $end; $t += $duration * 60) {
                if ($isToday && $t <= $now) {
                    continue;
                }
                $slot = date('H:i', $t);
                if (!in_array($slot, $occupied, true)) {
                    $slots[] = $slot;
                }
            }
        }

        sort($slots);

        return array_values(array_unique($slots));
    }

    // ── Helpers privados ─────────────────────────────────────────────────

    private function ownedHub(string $uuid): array
    {
        $hub = HubPage::findByUuid($uuid);
        if ($hub === null || (int)$hub['user_id'] !== (int)$this->user()['id']) {
            $this->redirect(url('/hub'));
            exit;
        }
        return $hub;
    }

    private function ownedBlock(int $id): array
    {
        $block = HubBlock::find($id);
        if ($block === null) {
            $this->json(['success' => false, 'error' => 'Bloco não encontrado.'], 404);
            exit;
        }
        $hub = HubPage::find((int)$block['hub_id']);
        if ($hub === null || (int)$hub['user_id'] !== (int)$this->user()['id']) {
            $this->json(['success' => false, 'error' => 'Acesso negado.'], 403);
            exit;
        }
        return $block;
    }

    private function parseBlockData(string $type, array $post): array
    {
        return match ($type) {
            'link'         => ['url' => trim($post['url'] ?? ''), 'label' => trim($post['label'] ?? ''), 'icon' => trim($post['icon'] ?? '')],
            'group'        => ['title' => trim($post['title'] ?? ''), 'links' => $this->parseJsonField($post['links_json'] ?? '[]')],
            'whatsapp'     => ['phone' => trim($post['phone'] ?? ''), 'message' => trim($post['message'] ?? ''), 'label' => trim($post['label'] ?? 'Fale no WhatsApp')],
            'social'       => ['networks' => $this->parseJsonField($post['networks_json'] ?? '[]')],
            'map'          => ['address' => trim($post['address'] ?? ''), 'lat' => (float)($post['lat'] ?? 0), 'lng' => (float)($post['lng'] ?? 0), 'zoom' => (int)($post['zoom'] ?? 15)],
            'schedule'     => ['hours' => $this->parseJsonField($post['hours_json'] ?? '[]')],
            'catalog'      => ['items' => $this->parseJsonField($post['items_json'] ?? '[]')],
            'video'        => ['video_url' => trim($post['video_url'] ?? ''), 'autoplay' => isset($post['autoplay']) ? 1 : 0],
            'contact_form' => ['fields' => $this->parseJsonField($post['fields_json'] ?? '[]'), 'email_to' => trim($post['email_to'] ?? '')],
            'pix'          => [
                'pix_key'             => trim($post['pix_key'] ?? ''),
                'merchant_name'       => trim($post['merchant_name'] ?? ''),
                'merchant_city'       => trim($post['merchant_city'] ?? ''),
                'description'         => trim($post['description'] ?? ''),
                'fixed_amount'        => trim((string) ($post['fixed_amount'] ?? '')),
                'allow_custom_amount' => isset($post['allow_custom_amount']) ? 1 : 0,
            ],
            'agenda' => [
                'hours'            => $this->parseJsonField($post['hours_json'] ?? '[]'),
                'duration_minutes' => max(5, (int) ($post['duration_minutes'] ?? 30)),
            ],
            default        => [],
        };
    }

    private function parseJsonField(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = mb_strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-') ?: 'hub-' . substr(uuid4(), 0, 8);
    }

    private function generateSlug(string $title): string
    {
        return $this->sanitizeSlug($title);
    }

    private function sanitizeColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#2E86AB';
    }

    private function uploadAvatar(array $file, string $uuid): ?string
    {
        if (!validMime($file['tmp_name'], ['image/png', 'image/jpeg', 'image/webp'])) {
            return null;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return null;
        }

        $dir = ROOT . '/storage/uploads/hub/' . $uuid;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext  = match (mime_content_type($file['tmp_name'])) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $dest = $dir . '/avatar.' . $ext;
        move_uploaded_file($file['tmp_name'], $dest);

        return 'storage/uploads/hub/' . $uuid . '/avatar.' . $ext;
    }
}
