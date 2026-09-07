<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LauncherLink extends Model
{
    protected static string $table = 'launcher_links';
    protected static bool $hasUpdatedAt = false;

    public static function forUser(int $userId): array
    {
        return static::rows(
            'SELECT * FROM launcher_links WHERE user_id = ? AND active = 1
             ORDER BY use_count DESC, last_used_at DESC',
            [$userId]
        );
    }

    public static function findBySource(int $userId, string $source, ?int $sourceId): ?array
    {
        if ($sourceId === null) {
            return static::row(
                'SELECT * FROM launcher_links WHERE user_id = ? AND source = ? AND source_id IS NULL LIMIT 1',
                [$userId, $source]
            );
        }

        return static::row(
            'SELECT * FROM launcher_links WHERE user_id = ? AND source = ? AND source_id = ? LIMIT 1',
            [$userId, $source, $sourceId]
        );
    }

    /**
     * Cria ou atualiza um launcher_link identificado por (user_id, source, source_id)
     * e incrementa uso — usado pelo track() do widget.
     */
    public static function touch(int $userId, string $source, ?int $sourceId, string $title, string $url, ?string $icon = null): void
    {
        $existing = static::findBySource($userId, $source, $sourceId);

        if ($existing !== null) {
            static::update((int) $existing['id'], [
                'use_count'    => (int) $existing['use_count'] + 1,
                'last_used_at' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        static::create([
            'user_id'      => $userId,
            'source'       => $source,
            'source_id'    => $sourceId,
            'title'        => $title,
            'url'          => $url,
            'icon'         => $icon,
            'use_count'    => 1,
            'last_used_at' => date('Y-m-d H:i:s'),
            'active'       => 1,
        ]);
    }
}
