<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Core\Validator;
use App\Models\QRCode;
use App\Models\User;
use App\Models\CreditTransaction;
use App\Services\QRGenerator;
use App\Services\QRLogoService;
use App\Services\QueueService;
use App\Services\ShortURLService;

class QRController extends Controller
{
    private const TYPE_FIELDS = QRGenerator::TYPE_FIELDS;

    private const TYPE_RULES = QRGenerator::TYPE_RULES;

    public function showGenerate(): void
    {
        $this->requireAuth();

        $this->render('qr/generate', [
            'title' => 'Gerar QR Code',
            'types' => array_keys(self::TYPE_FIELDS),
        ]);
    }

    public function generate(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $type = (string) $this->request->input('type', 'url');

        if (!isset(self::TYPE_FIELDS[$type])) {
            $this->flash('error', 'Tipo de QR Code inválido.');
            $this->redirect(url('/generate'));
        }

        $data = $this->request->only(self::TYPE_FIELDS[$type]);

        $validator = Validator::make($data, self::TYPE_RULES[$type]);

        if ($validator->fails()) {
            foreach ($validator->errors() as $fieldErrors) {
                foreach ($fieldErrors as $message) {
                    $this->flash('error', $message);
                }
            }
            $this->redirect(url('/generate'));
        }

        $generator = new QRGenerator();
        $content = $generator->buildContent($type, $data);

        $uuid = uuid4();
        $fgColor = (string) $this->request->input('fg_color', '#000000');
        $bgColor = (string) $this->request->input('bg_color', '#FFFFFF');
        $eccLevel = (string) $this->request->input('ecc_level', 'M');
        $size = (int) $this->request->input('size', 512);
        $transparent = (bool) $this->request->input('transparent', false);

        $files = $generator->render($content, [
            'size'        => $size,
            'ecc_level'   => $eccLevel,
            'fg_color'    => $fgColor,
            'bg_color'    => $bgColor,
            'transparent' => $transparent,
        ], $uuid);

        $shortCode = ShortURLService::generateCode();
        $shortUrl  = ShortURLService::buildUrl($shortCode);

        $qrId = QRCode::create([
            'uuid'            => $uuid,
            'user_id'         => $this->user()['id'],
            'type'            => $type,
            'content'         => $content,
            'label'           => (string) $this->request->input('label', ''),
            'fg_color'        => $fgColor,
            'bg_color'        => $bgColor,
            'transparent'     => $transparent ? 1 : 0,
            'ecc_level'       => $eccLevel,
            'size'            => $size,
            'file_png'        => $files['png'],
            'file_svg'        => $files['svg'],
            'short_code'      => $shortCode,
            'short_url'       => $shortUrl,
            'render_status'   => 'done',
        ]);

        $this->flash('success', 'QR Code gerado com sucesso.');
        $this->redirect(url('/qr/' . $uuid));
    }

    public function preview(): void
    {
        $this->requireAuth();

        $type = (string) $this->request->input('type', 'url');

        if (!isset(self::TYPE_FIELDS[$type])) {
            $this->json(['error' => 'Tipo inválido'], 422);

            return;
        }

        $data = $this->request->only(self::TYPE_FIELDS[$type]);
        $generator = new QRGenerator();

        try {
            $content = $generator->buildContent($type, $data);
        } catch (\Throwable) {
            $this->json(['error' => 'Não foi possível gerar a prévia.'], 422);

            return;
        }

        if (trim($content) === '') {
            $this->json(['svg' => null]);

            return;
        }

        $tmpBase = 'preview-' . session_id();
        $files = $generator->render($content, [
            'size'        => (int) $this->request->input('size', 300),
            'ecc_level'   => (string) $this->request->input('ecc_level', 'M'),
            'fg_color'    => (string) $this->request->input('fg_color', '#000000'),
            'bg_color'    => (string) $this->request->input('bg_color', '#FFFFFF'),
            'transparent' => (bool) $this->request->input('transparent', false),
        ], $tmpBase);

        $svg = @file_get_contents(ROOT . '/' . $files['svg']);

        // Prévia é temporária — remove os arquivos gerados no disco imediatamente.
        @unlink(ROOT . '/' . $files['png']);
        @unlink(ROOT . '/' . $files['svg']);

        $this->json(['svg' => $svg]);
    }

    /**
     * Geração pública de QR Code, sem login — usada na Home e em widgets rápidos.
     * Não persiste no banco nem em histórico: só renderiza e devolve a imagem.
     * Sem QR-Logo, sem short_url, sem analytics — pra isso o visitante precisa
     * criar conta e usar o gerador completo (/generate).
     */
    public function quickGenerate(): void
    {
        Middleware::rateLimit('qr:quick:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 20, 60);

        $type = (string) $this->request->input('type', 'url');

        if (!isset(self::TYPE_FIELDS[$type])) {
            $this->json(['success' => false, 'error' => 'Tipo de QR Code inválido.'], 422);
            return;
        }

        $data = $this->request->only(self::TYPE_FIELDS[$type]);
        $validator = Validator::make($data, self::TYPE_RULES[$type]);

        if ($validator->fails()) {
            $this->json(['success' => false, 'error' => 'Preencha os campos obrigatórios.', 'details' => $validator->errors()], 422);
            return;
        }

        $generator = new QRGenerator();

        try {
            $content = $generator->buildContent($type, $data);
        } catch (\Throwable) {
            $this->json(['success' => false, 'error' => 'Não foi possível gerar o QR Code.'], 422);
            return;
        }

        $tmpBase = 'quick-' . uuid4();
        $files = $generator->render($content, [
            'size'        => min(1024, max(128, (int) $this->request->input('size', 512))),
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

        $this->json(['success' => true, 'png' => 'data:image/png;base64,' . base64_encode($pngData)]);
    }

    public function show(string $uuid): void
    {
        $this->requireAuth();

        $qr = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== (int) $this->user()['id']) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';

            return;
        }

        $this->render('qr/show', [
            'title' => $qr['label'] !== null && $qr['label'] !== '' ? $qr['label'] : 'QR Code',
            'qr'    => $qr,
        ]);
    }

    public function delete(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $qr = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== (int) $this->user()['id']) {
            http_response_code(404);

            return;
        }

        foreach (['file_png', 'file_svg', 'file_logo_png'] as $field) {
            if (!empty($qr[$field])) {
                @unlink(ROOT . '/' . $qr[$field]);
            }
        }

        QRCode::delete((int) $qr['id']);

        $this->flash('success', 'QR Code removido.');
        $this->redirect(url('/generate'));
    }

    // ── QR-Logo ──────────────────────────────────────────────────────────────

    public function addLogo(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $this->user()['id'];
        $qr     = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== $userId) {
            $this->json(['error' => 'QR Code não encontrado.'], 404);
            return;
        }

        // Validação do upload
        $file = $_FILES['logo'] ?? null;
        if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
            $msg = $errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE
                ? 'Arquivo muito grande.'
                : 'Nenhum arquivo enviado ou erro no upload.';
            $this->json(['error' => $msg], 422);
            return;
        }

        if ((int)$file['size'] > 5 * 1024 * 1024) {
            $this->json(['error' => 'Logo muito grande. Máximo: 5MB.'], 422);
            return;
        }

        $allowedMimes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        if (!validMime($file['tmp_name'], $allowedMimes)) {
            $this->json(['error' => 'Formato não suportado. Use PNG, JPEG, GIF ou WebP.'], 422);
            return;
        }

        // Verifica créditos
        $credits = User::getCredits($userId);
        if ($credits < 1) {
            $this->json(['error' => 'Créditos insuficientes. Adquira créditos para usar o QR-Logo.'], 402);
            return;
        }

        // Determina extensão pelo MIME real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'png',
        };

        // Salva logo em storage/uploads/{qr_uuid}/
        $uploadDir = ROOT . '/storage/uploads/' . $qr['uuid'];
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $logoFilename = 'logo.' . $ext;
        $logoAbsPath  = $uploadDir . '/' . $logoFilename;

        if (!move_uploaded_file($file['tmp_name'], $logoAbsPath)) {
            $this->json(['error' => 'Erro ao salvar arquivo. Tente novamente.'], 500);
            return;
        }

        // Debita 1 crédito (atomicamente)
        if (!User::deductCredit($userId, 1)) {
            @unlink($logoAbsPath);
            $this->json(['error' => 'Falha ao debitar crédito.'], 402);
            return;
        }

        $newBalance = User::getCredits($userId);
        CreditTransaction::create([
            'user_id'      => $userId,
            'type'         => 'consume',
            'amount'       => -1,
            'description'  => 'QR-Logo — renderização solicitada',
            'reference'    => $qr['uuid'],
            'balance_after'=> $newBalance,
        ]);

        // Atualiza status e enfileira
        QRCode::update((int) $qr['id'], ['render_status' => 'processing']);

        $relLogoPath = 'storage/uploads/' . $qr['uuid'] . '/' . $logoFilename;
        $jobId = QueueService::push('qrlogo', [
            'qrcode_id' => (int) $qr['id'],
            'logo_path' => $relLogoPath,
        ]);

        $this->json([
            'success'  => true,
            'job_id'   => $jobId,
            'status'   => 'processing',
            'credits'  => $newBalance,
            'message'  => 'QR-Logo em processamento. 1 crédito debitado.',
        ], 202);
    }

    public function logoStatus(string $uuid): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];
        $qr     = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== $userId) {
            $this->json(['error' => 'Não encontrado.'], 404);
            return;
        }

        $status   = $qr['render_status'] ?? 'done';
        $response = ['status' => $status];

        if ($status === 'done' && !empty($qr['file_logo_png'])) {
            $response['logo_url']          = storageUrl($qr['file_logo_png']);
            $response['legibility_index']  = (int) $qr['legibility_index'];
        } elseif ($status === 'failed') {
            $response['message'] = 'Índice de legibilidade insuficiente. Crédito reembolsado automaticamente.';
        }

        $this->json($response);
    }
}
