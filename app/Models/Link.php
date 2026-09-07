<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Link extends Model
{
    protected static string $table = 'links';

    public static function findBySlug(string $slug): ?array
    {
        return static::findBy('slug', $slug);
    }

    public static function findByUuidForUser(string $uuid, int $userId): ?array
    {
        return static::row(
            'SELECT * FROM links WHERE uuid = ? AND user_id = ?',
            [$uuid, $userId]
        );
    }

    public static function forUser(int $userId, int $page = 1, int $perPage = 15,
                                   string $search = '', string $type = ''): array
    {
        $where  = 'user_id = ?';
        $params = [$userId];

        if ($search !== '') {
            $where   .= ' AND (title LIKE ? OR destination LIKE ? OR slug LIKE ?)';
            $like     = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($type !== '') {
            $where   .= ' AND wrapper_type = ?';
            $params[] = $type;
        }

        return static::paginate($page, $perPage, $where, $params);
    }

    public static function incrementClickCount(int $id): void
    {
        static::query('UPDATE links SET click_count = click_count + 1 WHERE id = ?', [$id]);
    }

    public static function slugExists(string $slug): bool
    {
        return static::findBy('slug', $slug) !== null;
    }

    public static function topForUser(int $userId, int $limit = 10): array
    {
        return static::rows(
            'SELECT uuid, slug, title, destination, wrapper_type, click_count, created_at
             FROM links WHERE user_id = ? ORDER BY click_count DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public static function totalForUser(int $userId): int
    {
        return static::count('user_id = ?', [$userId]);
    }

    public static function recentForUser(int $userId, int $limit = 10): array
    {
        return static::rows(
            'SELECT uuid, slug, title, destination
             FROM links WHERE user_id = ? ORDER BY created_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public static function totalClicksForUser(int $userId): int
    {
        $row = static::row(
            'SELECT COALESCE(SUM(click_count), 0) AS total FROM links WHERE user_id = ?',
            [$userId]
        );
        return (int) ($row['total'] ?? 0);
    }
}
