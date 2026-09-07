<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CreditTransaction extends Model
{
    protected static string $table = 'credit_transactions';

    protected static function timestamps(array $data, bool $update = false): array
    {
        // Apenas created_at com DEFAULT CURRENT_TIMESTAMP — sem updated_at
        return $data;
    }

    public static function forUser(int $userId, int $limit = 50): array
    {
        return static::rows(
            'SELECT * FROM credit_transactions WHERE user_id = ? ORDER BY id DESC LIMIT ?',
            [$userId, $limit]
        );
    }
}
