<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class HubBlock extends Model
{
    protected static string $table = 'hub_blocks';
    protected static bool $hasUpdatedAt = false;

    public static function forHub(int $hubId): array
    {
        return static::rows(
            'SELECT * FROM hub_blocks WHERE hub_id = ? AND active = 1 ORDER BY position ASC',
            [$hubId]
        );
    }

    public static function forHubAll(int $hubId): array
    {
        return static::rows(
            'SELECT * FROM hub_blocks WHERE hub_id = ? ORDER BY position ASC',
            [$hubId]
        );
    }

    public static function countForHub(int $hubId): int
    {
        return static::count('hub_id = ?', [$hubId]);
    }

    public static function maxPosition(int $hubId): int
    {
        $row = static::row(
            'SELECT MAX(position) AS pos FROM hub_blocks WHERE hub_id = ?',
            [$hubId]
        );
        return (int)($row['pos'] ?? 0);
    }

    public static function reorder(int $hubId, array $ids): void
    {
        foreach ($ids as $pos => $id) {
            static::query(
                'UPDATE hub_blocks SET position = ? WHERE id = ? AND hub_id = ?',
                [$pos, $id, $hubId]
            );
        }
    }
}
