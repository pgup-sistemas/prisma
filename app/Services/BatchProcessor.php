<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\QRCode;

class BatchProcessor
{
    public const MAX_ITEMS = 200;

    /**
     * Gera um QR Code por linha de texto, agrupa em um Batch e zipa os PNGs.
     * Processamento síncrono (sem fila) — adequado ao limite de MAX_ITEMS itens.
     */
    public function process(int $userId, string $name, string $type, array $lines, array $style): array
    {
        $lines = array_slice(
            array_values(array_filter(array_map('trim', $lines), static fn (string $l) => $l !== '')),
            0,
            self::MAX_ITEMS
        );

        if (empty($lines)) {
            throw new \InvalidArgumentException('Nenhum item válido para processar.');
        }

        $batchId = Batch::create([
            'user_id' => $userId,
            'name'    => $name,
            'total'   => count($lines),
            'done'    => 0,
        ]);

        $generator = new QRGenerator();
        $pngFiles  = [];

        foreach ($lines as $i => $line) {
            $uuid    = uuid4();
            $content = $generator->buildContent($type, $this->lineToData($type, $line));

            $files = $generator->render($content, $style, $uuid);

            $shortCode = ShortURLService::generateCode();

            $qrId = QRCode::create([
                'uuid'          => $uuid,
                'user_id'       => $userId,
                'type'          => $type,
                'content'       => $content,
                'label'         => mb_substr($line, 0, 120),
                'fg_color'      => $style['fg_color'],
                'bg_color'      => $style['bg_color'],
                'transparent'   => !empty($style['transparent']) ? 1 : 0,
                'ecc_level'     => $style['ecc_level'],
                'size'          => $style['size'],
                'file_png'      => $files['png'],
                'file_svg'      => $files['svg'],
                'short_code'    => $shortCode,
                'short_url'     => ShortURLService::buildUrl($shortCode),
                'render_status' => 'done',
            ]);

            BatchItem::create(['batch_id' => $batchId, 'qr_id' => $qrId]);

            $pngFiles[] = ['label' => $line, 'path' => ROOT . '/' . $files['png']];

            Batch::update($batchId, ['done' => $i + 1]);
        }

        $zipPath = $this->buildZip($batchId, $pngFiles);
        Batch::update($batchId, ['zip_file' => $zipPath]);

        return Batch::find($batchId);
    }

    private function lineToData(string $type, string $line): array
    {
        return match ($type) {
            'text'     => ['text' => $line],
            'phone'    => ['phone' => $line],
            'whatsapp' => ['phone' => $line],
            default    => ['url' => $line],
        };
    }

    private function buildZip(int $batchId, array $pngFiles): string
    {
        $dir = ROOT . '/storage/batches';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $relative = 'storage/batches/batch-' . $batchId . '.zip';
        $full     = ROOT . '/' . $relative;

        $zip = new \ZipArchive();
        if ($zip->open($full, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Não foi possível criar o arquivo ZIP do lote.');
        }

        $usedNames = [];
        foreach ($pngFiles as $item) {
            if (!is_file($item['path'])) {
                continue;
            }

            $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', trim($item['label']));
            $base = trim($base, '_');
            $base = $base !== '' ? mb_substr($base, 0, 60) : 'qr';

            $name = $base . '.png';
            $n = 1;
            while (in_array($name, $usedNames, true)) {
                $n++;
                $name = $base . '-' . $n . '.png';
            }
            $usedNames[] = $name;

            $zip->addFile($item['path'], $name);
        }

        $zip->close();

        return $relative;
    }
}
