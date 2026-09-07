<?php

declare(strict_types=1);

namespace App\Core;

use Predis\Client as Predis;

class Cache
{
    private static ?Predis $client = null;

    public static function client(): Predis
    {
        if (self::$client === null) {
            $cfg = require ROOT . '/config/redis.php';
            self::$client = new Predis([
                'scheme'             => 'tcp',
                'host'               => $cfg['host'],
                'port'               => $cfg['port'],
                'password'           => $cfg['password'],
                'database'           => $cfg['database'],
                'read_write_timeout' => $cfg['timeout'],
            ]);
        }

        return self::$client;
    }

    public static function get(string $key): mixed
    {
        $v = self::client()->get($key);

        return $v !== null ? json_decode($v, true) : null;
    }

    public static function set(string $key, mixed $value, int $ttl = 300): void
    {
        self::client()->setex($key, $ttl, json_encode($value));
    }

    public static function del(string $key): void
    {
        self::client()->del([$key]);
    }

    public static function incr(string $key): int
    {
        return self::client()->incr($key);
    }

    public static function expire(string $key, int $ttl): void
    {
        self::client()->expire($key, $ttl);
    }
}
