<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Batch;
use App\Models\BatchItem;
use App\Services\BatchProcessor;

class BatchController extends Controller
{
    private const TYPES = ['url', 'text', 'phone', 'whatsapp'];

    public function index(): void
    {
        $this->requireAuth();

        $this->render('batch/index', [
            'title'  => 'Lote de QR Codes',
            'batches' => Batch::forUser((int) $this->user()['id']),
            'maxItems' => BatchProcessor::MAX_ITEMS,
        ]);
    }

    public function process(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $user = $this->user();
        $name = trim((string) $this->request->input('name', ''));
        $type = (string) $this->request->input('type', 'url');
        $itemsText = (string) $this->request->input('items_text', '');

        if ($name === '') {
            $name = 'Lote ' . date('d/m/Y H:i');
        }

        if (!in_array($type, self::TYPES, true)) {
            $type = 'url';
        }

        $lines = preg_split('/\r\n|\r|\n/', $itemsText) ?: [];

        $style = [
            'fg_color'    => $this->sanitizeColor((string) $this->request->input('fg_color', '#000000')),
            'bg_color'    => $this->sanitizeColor((string) $this->request->input('bg_color', '#FFFFFF')),
            'ecc_level'   => in_array($this->request->input('ecc_level', 'M'), ['L', 'M', 'Q', 'H'], true)
                                ? (string) $this->request->input('ecc_level', 'M') : 'M',
            'size'        => max(128, min(2048, (int) $this->request->input('size', 512))),
            'transparent' => (bool) $this->request->input('transparent', false),
        ];

        try {
            $processor = new BatchProcessor();
            $batch = $processor->process((int) $user['id'], $name, $type, $lines, $style);

            $truncated = count(array_filter(array_map('trim', $lines), static fn (string $l) => $l !== '')) > BatchProcessor::MAX_ITEMS;
            $msg = "Lote \"{$batch['name']}\" gerado com {$batch['total']} QR Codes.";
            if ($truncated) {
                $msg .= ' (limitado a ' . BatchProcessor::MAX_ITEMS . ' itens)';
            }
            $this->flash('success', $msg);
        } catch (\InvalidArgumentException $e) {
            $this->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->flash('error', 'Falha ao processar o lote: ' . $e->getMessage());
        }

        $this->redirect(url('/batch'));
    }

    public function download(string $id): void
    {
        $this->requireAuth();

        $batch = Batch::find((int) $id);

        if ($batch === null || (int) $batch['user_id'] !== (int) $this->user()['id'] || empty($batch['zip_file'])) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        $full = ROOT . '/' . $batch['zip_file'];
        if (!is_file($full)) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        $filename = 'prisma-lote-' . preg_replace('/[^A-Za-z0-9_\-]+/', '_', $batch['name']) . '.zip';

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($full));
        readfile($full);
    }

    private function sanitizeColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#000000';
    }
}
