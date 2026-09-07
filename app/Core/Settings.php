<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Configurações globais persistentes do painel Admin.
 * Não há tabela dedicada no schema (seção 5 do CLAUDE.md) — armazenamos em
 * um arquivo JSON fora do webroot, fora das pastas bloqueadas por padrão
 * (storage/uploads|batches|bookmarks|logs), mas ainda assim nunca servido via rota.
 */
class Settings
{
    private const PATH = ROOT . '/storage/settings.json';

    private const DEFAULTS = [
        'site_name'           => 'PRISMA',
        'maintenance_mode'    => false,
        'allow_registration'  => true,
        'default_credits'     => 0,
    ];

    public static function all(): array
    {
        if (!is_file(self::PATH)) {
            return self::DEFAULTS;
        }

        $raw = file_get_contents(self::PATH);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? array_merge(self::DEFAULTS, $data) : self::DEFAULTS;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return $all[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function set(array $values): void
    {
        $data = array_merge(self::all(), $values);

        $dir = dirname(self::PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(self::PATH, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
