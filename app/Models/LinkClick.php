<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LinkClick extends Model
{
    protected static string $table = 'link_clicks';

    // link_clicks has no created_at/updated_at — only clicked_at (MySQL DEFAULT)
    protected static function timestamps(array $data, bool $update = false): array
    {
        return $data;
    }

    public static function countByDay(int $linkId, int $days = 30): array
    {
        return static::rows(
            'SELECT DATE(clicked_at) AS day, COUNT(*) AS total FROM link_clicks
             WHERE link_id = ? AND clicked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY day ORDER BY day ASC',
            [$linkId, $days]
        );
    }

    public static function countByDevice(int $linkId): array
    {
        return static::rows(
            'SELECT device, COUNT(*) AS total FROM link_clicks WHERE link_id = ?
             GROUP BY device ORDER BY total DESC',
            [$linkId]
        );
    }

    public static function countByCountry(int $linkId): array
    {
        return static::rows(
            'SELECT country, COUNT(*) AS total FROM link_clicks
             WHERE link_id = ? AND country IS NOT NULL AND country != ""
             GROUP BY country ORDER BY total DESC LIMIT 20',
            [$linkId]
        );
    }

    public static function countByVariant(int $linkId): array
    {
        return static::rows(
            'SELECT COALESCE(variant, "default") AS variant, COUNT(*) AS total
             FROM link_clicks WHERE link_id = ?
             GROUP BY variant ORDER BY total DESC',
            [$linkId]
        );
    }

    public static function recent(int $linkId, int $limit = 20): array
    {
        return static::rows(
            'SELECT * FROM link_clicks WHERE link_id = ?
             ORDER BY clicked_at DESC LIMIT ?',
            [$linkId, $limit]
        );
    }
}
