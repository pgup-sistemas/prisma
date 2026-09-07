<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\QRCode;

class DownloadController extends Controller
{
    private const MIME = [
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
    ];

    public function download(string $uuid): void
    {
        $this->requireAuth();

        $qr = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== (int) $this->user()['id']) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';

            return;
        }

        $format = (string) $this->request->input('format', 'png');
        $field = $format === 'svg' ? 'file_svg' : 'file_png';

        if (empty($qr[$field])) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';

            return;
        }

        $full = ROOT . '/' . $qr[$field];

        if (!is_file($full)) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';

            return;
        }

        $filename = 'prisma-qr-' . substr($uuid, 0, 8) . '.' . $format;

        header('Content-Type: ' . (self::MIME[$format] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($full));
        readfile($full);
    }
}
