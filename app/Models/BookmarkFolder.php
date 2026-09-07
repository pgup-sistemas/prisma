<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class BookmarkFolder extends Model
{
    protected static string $table = 'bookmark_folders';
    protected static bool $hasUpdatedAt = false;

    public static function forUser(int $userId): array
    {
        return static::rows(
            'SELECT * FROM bookmark_folders WHERE user_id = ? ORDER BY position ASC, name ASC',
            [$userId]
        );
    }

    public static function tree(int $userId): array
    {
        $folders = static::forUser($userId);
        $byParent = [];
        foreach ($folders as $folder) {
            $parentId = $folder['parent_id'] !== null ? (int) $folder['parent_id'] : 0;
            $byParent[$parentId][] = $folder;
        }

        $build = function (int $parentId) use (&$build, $byParent): array {
            $branch = [];
            foreach ($byParent[$parentId] ?? [] as $folder) {
                $folder['children'] = $build((int) $folder['id']);
                $branch[] = $folder;
            }
            return $branch;
        };

        return $build(0);
    }

    public static function findOrCreate(int $userId, ?string $name, ?int $parentId): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return $parentId;
        }

        $existing = static::row(
            'SELECT id FROM bookmark_folders WHERE user_id = ? AND name = ? AND ' .
            ($parentId === null ? 'parent_id IS NULL' : 'parent_id = ?'),
            $parentId === null ? [$userId, $name] : [$userId, $name, $parentId]
        );

        if ($existing) {
            return (int) $existing['id'];
        }

        return static::create([
            'user_id'   => $userId,
            'name'      => $name,
            'parent_id' => $parentId,
            'position'  => 0,
        ]);
    }
}
