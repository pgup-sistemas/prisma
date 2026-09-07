<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Bookmark extends Model
{
    protected static string $table = 'bookmarks';

    public static function forUser(int $userId, ?int $folderId = null): array
    {
        if ($folderId !== null) {
            return static::rows(
                'SELECT * FROM bookmarks WHERE user_id = ? AND folder_id = ? ORDER BY position ASC, title ASC',
                [$userId, $folderId]
            );
        }

        return static::rows(
            'SELECT * FROM bookmarks WHERE user_id = ? ORDER BY position ASC, title ASC',
            [$userId]
        );
    }

    public static function paginateForUser(int $userId, ?int $folderId, string $search, int $page, int $perPage): array
    {
        $where  = 'user_id = ?';
        $params = [$userId];

        if ($folderId !== null) {
            $where   .= ' AND folder_id = ?';
            $params[] = $folderId;
        }

        if ($search !== '') {
            $where   .= ' AND (title LIKE ? OR url LIKE ?)';
            $like     = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $total = static::count($where, $params);
        $pages = (int) ceil($total / $perPage);

        $data = static::rows(
            'SELECT * FROM bookmarks WHERE ' . $where . ' ORDER BY position ASC, title ASC LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        );

        return ['data' => $data, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    public static function inLauncher(int $userId): array
    {
        return static::rows(
            'SELECT * FROM bookmarks WHERE user_id = ? AND in_launcher = 1 ORDER BY title ASC',
            [$userId]
        );
    }

    public static function urlExists(int $userId, string $url): bool
    {
        return (bool) static::row(
            'SELECT id FROM bookmarks WHERE user_id = ? AND url = ? LIMIT 1',
            [$userId, $url]
        );
    }

    public static function countForUser(int $userId): int
    {
        return static::count('user_id = ?', [$userId]);
    }

    public static function markHealth(int $id, string $health): void
    {
        static::update($id, [
            'health'            => $health,
            'health_checked_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ativa/desativa in_launcher para vários favoritos de uma vez, restrito ao dono.
     * Retorna quantas linhas foram afetadas.
     */
    public static function bulkSetLauncher(array $ids, int $userId, bool $inLauncher): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$inLauncher ? 1 : 0], $ids, [$userId]);

        $stmt = static::query(
            "UPDATE bookmarks SET in_launcher = ? WHERE id IN ({$placeholders}) AND user_id = ?",
            $params
        );

        return $stmt->rowCount();
    }

    /**
     * Exclui vários favoritos de uma vez, restrito ao dono.
     * Retorna quantas linhas foram afetadas.
     */
    public static function bulkDelete(array $ids, int $userId): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge($ids, [$userId]);

        $stmt = static::query(
            "DELETE FROM bookmarks WHERE id IN ({$placeholders}) AND user_id = ?",
            $params
        );

        return $stmt->rowCount();
    }
}
