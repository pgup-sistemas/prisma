<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;

class CurrencyService
{
    private const TTL = 3600;
    private const CACHE_KEY = 'currency:rates';
    private const API_URL = 'https://open.er-api.com/v6/latest/USD';
    private const SUPPORTED = ['BRL', 'USD', 'EUR'];

    /**
     * Retorna cotações com base em USD: ['USD' => 1, 'BRL' => x, 'EUR' => y].
     */
    public static function rates(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            return $cached;
        }

        $rates = self::fetchApi();
        Cache::set(self::CACHE_KEY, $rates, self::TTL);

        return $rates;
    }

    public static function convert(string $from, string $to, float $amount): ?float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if (!in_array($from, self::SUPPORTED, true) || !in_array($to, self::SUPPORTED, true)) {
            return null;
        }

        $rates = self::rates();
        if (!isset($rates[$from], $rates[$to])) {
            return null;
        }

        // Converte para USD e depois para a moeda destino
        $usd = $amount / $rates[$from];

        return round($usd * $rates[$to], 4);
    }

    private static function fetchApi(): array
    {
        $fallback = ['USD' => 1.0, 'BRL' => 5.4, 'EUR' => 0.92];

        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 4,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents(self::API_URL, false, $ctx);
        if ($raw === false) {
            return $fallback;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || ($data['result'] ?? '') !== 'success' || !isset($data['rates'])) {
            return $fallback;
        }

        $rates = ['USD' => 1.0];
        foreach (self::SUPPORTED as $code) {
            if ($code === 'USD') {
                continue;
            }
            $rates[$code] = isset($data['rates'][$code]) ? (float) $data['rates'][$code] : $fallback[$code];
        }

        return $rates;
    }
}
