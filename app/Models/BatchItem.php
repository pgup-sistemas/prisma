<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class BatchItem extends Model
{
    protected static string $table = 'batch_items';

    // batch_items não tem created_at/updated_at
    protected static function timestamps(array $data, bool $update = false): array
    {
        return $data;
    }

    public static function forBatch(int $batchId): array
    {
        return static::rows(
            'SELECT bi.*, q.uuid AS qr_uuid, q.label AS qr_label, q.file_png, q.short_url
             FROM batch_items bi
             JOIN qrcodes q ON q.id = bi.qr_id
             WHERE bi.batch_id = ?
             ORDER BY bi.id ASC',
            [$batchId]
        );
    }
}
