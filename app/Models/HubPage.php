<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class HubPage extends Model
{
    protected static string $table = 'hub_pages';

    public static function findBySlug(string $slug): ?array
    {
        return static::row(
            'SELECT * FROM hub_pages WHERE slug = ? AND active = 1 LIMIT 1',
            [$slug]
        );
    }

    public static function findByUuid(string $uuid): ?array
    {
        return static::row(
            'SELECT * FROM hub_pages WHERE uuid = ? LIMIT 1',
            [$uuid]
        );
    }

    public static function forUser(int $userId): array
    {
        return static::rows(
            'SELECT * FROM hub_pages WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }

    public static function incrementViews(int $id): void
    {
        static::query(
            'UPDATE hub_pages SET view_count = view_count + 1 WHERE id = ?',
            [$id]
        );
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            return (bool) static::row(
                'SELECT id FROM hub_pages WHERE slug = ? AND id != ? LIMIT 1',
                [$slug, $excludeId]
            );
        }
        return (bool) static::row(
            'SELECT id FROM hub_pages WHERE slug = ? LIMIT 1',
            [$slug]
        );
    }
}
