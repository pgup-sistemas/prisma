<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class QRCode extends Model
{
    protected static string $table = 'qrcodes';

    public static function forUser(int $userId, string $order = 'id DESC', int $limit = 1000): array
    {
        return self::rows(
            'SELECT * FROM qrcodes WHERE user_id = ? ORDER BY ' . $order . ' LIMIT ' . $limit,
            [$userId]
        );
    }

    public static function findByShortCode(string $code): ?array
    {
        return self::findBy('short_code', $code);
    }

    public static function incrementScanCount(int $id): void
    {
        static::query('UPDATE qrcodes SET scan_count = scan_count + 1 WHERE id = ?', [$id]);
    }

    public static function topForUser(int $userId, int $limit = 10): array
    {
        return static::rows(
            'SELECT uuid, label, type, scan_count, created_at
             FROM qrcodes WHERE user_id = ? ORDER BY scan_count DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public static function totalForUser(int $userId): int
    {
        return static::count('user_id = ?', [$userId]);
    }
}
