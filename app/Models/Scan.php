<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Scan extends Model
{
    protected static string $table = 'scans';

    // scans table has no created_at/updated_at — scanned_at uses MySQL DEFAULT
    protected static function timestamps(array $data, bool $update = false): array
    {
        return $data;
    }

    public static function forQR(int $qrId, int $limit = 500): array
    {
        return static::rows(
            'SELECT * FROM scans WHERE qr_id = ? ORDER BY scanned_at DESC LIMIT ?',
            [$qrId, $limit]
        );
    }

    public static function countByCountry(int $qrId): array
    {
        return static::rows(
            'SELECT country, COUNT(*) AS total FROM scans
             WHERE qr_id = ? AND country IS NOT NULL AND country != ""
             GROUP BY country ORDER BY total DESC LIMIT 20',
            [$qrId]
        );
    }

    public static function countByDevice(int $qrId): array
    {
        return static::rows(
            'SELECT device, COUNT(*) AS total FROM scans WHERE qr_id = ?
             GROUP BY device ORDER BY total DESC',
            [$qrId]
        );
    }

    public static function countByDay(int $qrId, int $days = 30): array
    {
        return static::rows(
            'SELECT DATE(scanned_at) AS day, COUNT(*) AS total FROM scans
             WHERE qr_id = ? AND scanned_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY day ORDER BY day ASC',
            [$qrId, $days]
        );
    }

    public static function totalForUser(int $userId): int
    {
        $row = static::row(
            'SELECT COUNT(*) AS total FROM scans s
             JOIN qrcodes q ON q.id = s.qr_id WHERE q.user_id = ?',
            [$userId]
        );
        return (int) ($row['total'] ?? 0);
    }

    public static function recentForUser(int $userId, int $limit = 10): array
    {
        return static::rows(
            'SELECT s.*, q.label, q.uuid AS qr_uuid, q.type AS qr_type
             FROM scans s JOIN qrcodes q ON q.id = s.qr_id
             WHERE q.user_id = ? ORDER BY s.scanned_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public static function scansLastDays(int $userId, int $days = 30): array
    {
        return static::rows(
            'SELECT DATE(s.scanned_at) AS day, COUNT(*) AS total
             FROM scans s JOIN qrcodes q ON q.id = s.qr_id
             WHERE q.user_id = ? AND s.scanned_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY day ORDER BY day ASC',
            [$userId, $days]
        );
    }
}
