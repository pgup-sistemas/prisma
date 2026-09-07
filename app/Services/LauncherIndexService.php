<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;
use App\Models\Bookmark;
use App\Models\LauncherLink;
use App\Models\Link;

class LauncherIndexService
{
    private const TTL = 300;

    public static function build(int $userId): array
    {
        $items = [];

        foreach (LauncherLink::forUser($userId) as $l) {
            $items[] = [
                'id'           => 'll-' . $l['id'],
                'source'       => $l['source'],
                'source_id'    => $l['source_id'] !== null ? (int) $l['source_id'] : null,
                'title'        => $l['title'],
                'url'          => $l['url'],
                'icon'         => $l['icon'] ?: 'bi-link-45deg',
                'tags'         => jsonDecode((string) ($l['tags_json'] ?? '[]')),
                'use_count'    => (int) $l['use_count'],
                'last_used_at' => $l['last_used_at'],
            ];
        }

        $knownSources = array_flip(array_map(
            static fn (array $i) => $i['source'] . ':' . $i['source_id'],
            $items
        ));

        foreach (Bookmark::inLauncher($userId) as $b) {
            $key = 'bookmark:' . $b['id'];
            if (isset($knownSources[$key])) {
                continue;
            }
            $items[] = [
                'id'           => 'bm-' . $b['id'],
                'source'       => 'bookmark',
                'source_id'    => (int) $b['id'],
                'title'        => $b['title'],
                'url'          => $b['url'],
                'icon'         => 'bi-bookmark-star',
                'tags'         => [],
                'use_count'    => 0,
                'last_used_at' => null,
            ];
        }

        foreach (Link::recentForUser($userId, 10) as $link) {
            $key = 'shortlink:' . $link['uuid'];
            if (isset($knownSources[$key])) {
                continue;
            }
            $items[] = [
                'id'           => 'sl-' . $link['uuid'],
                'source'       => 'shortlink',
                'source_id'    => $link['uuid'],
                'title'        => $link['title'] ?: $link['slug'],
                'url'          => $link['destination'],
                'icon'         => 'bi-link-45deg',
                'tags'         => [],
                'use_count'    => 0,
                'last_used_at' => null,
            ];
        }

        usort($items, static function (array $a, array $b): int {
            if ($a['use_count'] !== $b['use_count']) {
                return $b['use_count'] <=> $a['use_count'];
            }
            return strcmp((string) $b['last_used_at'], (string) $a['last_used_at']);
        });

        return [
            'user_id'       => $userId,
            'generated_at'  => date('c'),
            'links'         => $items,
        ];
    }

    public static function get(int $userId): array
    {
        $cacheKey = 'launcher:' . $userId;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $index = self::build($userId);
        Cache::set($cacheKey, $index, (int) env('LAUNCHER_CACHE_TTL', self::TTL));

        return $index;
    }

    public static function invalidate(int $userId): void
    {
        Cache::del('launcher:' . $userId);
    }
}
