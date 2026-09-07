<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;

class GeoService
{
    private const TTL = 3600;
    private const API_URL = 'http://ip-api.com/json/';

    public static function lookup(string $ip): array
    {
        if (!env('GEOIP_ENABLED', true)) {
            return ['country' => null, 'city' => null];
        }

        if (self::isPrivate($ip)) {
            return ['country' => null, 'city' => null];
        }

        $cacheKey = 'geo:' . hash('sha256', $ip);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = self::fetchApi($ip);
        Cache::set($cacheKey, $result, self::TTL);

        return $result;
    }

    private static function fetchApi(string $ip): array
    {
        $url = self::API_URL . urlencode($ip) . '?fields=status,countryCode,city';

        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 3,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);

        if ($raw === false) {
            return ['country' => null, 'city' => null];
        }

        $data = json_decode($raw, true);

        if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
            return ['country' => null, 'city' => null];
        }

        return [
            'country' => $data['countryCode'] ?? null,
            'city'    => $data['city'] ?? null,
        ];
    }

    private static function isPrivate(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
