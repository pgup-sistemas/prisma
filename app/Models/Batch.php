<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Batch extends Model
{
    protected static string $table = 'batches';
    protected static bool $hasUpdatedAt = false;

    public static function forUser(int $userId): array
    {
        return static::rows(
            'SELECT * FROM batches WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }
}
