<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\QRScannerService;

class ScannerController extends Controller
{
    private const MAX_SIZE = 8 * 1024 * 1024; // 8MB

    public function index(): void
    {
        $this->requireAuth();
        $this->render('scanner/index', ['title' => 'Scanner de QR Code']);
    }

    public function scan(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $file = $this->request->file('image');

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
            $this->json(['success' => false, 'error' => 'Envie uma imagem contendo um QR Code.'], 422);
            return;
        }

        if ($file['size'] > self::MAX_SIZE) {
            $this->json(['success' => false, 'error' => 'Imagem muito grande (máx. 8MB).'], 422);
            return;
        }

        if (!validMime($file['tmp_name'], ['image/png', 'image/jpeg', 'image/webp', 'image/gif'])) {
            $this->json(['success' => false, 'error' => 'Formato inválido — envie PNG, JPEG, WEBP ou GIF.'], 422);
            return;
        }

        $result = (new QRScannerService())->decode($file['tmp_name']);

        if ($result === null) {
            $this->json(['success' => false, 'error' => 'Nenhum QR Code encontrado na imagem.'], 422);
            return;
        }

        $this->json(['success' => true, 'text' => $result['text'], 'type' => $result['type']]);
    }
}
