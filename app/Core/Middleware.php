<?php

declare(strict_types=1);

namespace App\Core;

class Middleware
{
    public static function handle(string $name): void
    {
        [$type, $param] = array_pad(explode(':', $name, 2), 2, null);

        match ($type) {
            'auth'    => self::auth(),
            'role'    => self::role((string) $param),
            'api_key' => self::apiKey(),
            default   => null,
        };
    }

    private static function apiKey(): void
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
        $user = $key !== '' ? \App\Models\User::findByApiKey($key) : null;

        if ($user === null || (int) $user['active'] !== 1) {
            Response::json([
                'success' => false,
                'error'   => ['code' => 401, 'message' => 'API key inválida ou ausente. Envie o header X-API-Key.'],
            ], 401);
            exit;
        }

        self::rateLimit('api:' . $key, 300, 60);
    }

    private static function auth(): void
    {
        if (!Auth::check()) {
            redirect(url('/login'));
        }
    }

    private static function role(string $role): void
    {
        self::auth();

        if (!Auth::hasRole($role)) {
            http_response_code(403);
            echo '403 - Acesso negado';
            exit;
        }
    }

    public static function rateLimit(string $key, int $max, int $windowSec): void
    {
        $count = Cache::incr("rl:{$key}");

        if ($count === 1) {
            Cache::expire("rl:{$key}", $windowSec);
        }

        if ($count > $max) {
            Response::json(['error' => 'Too Many Requests'], 429);
            exit;
        }
    }
}
