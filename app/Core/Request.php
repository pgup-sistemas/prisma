<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $body;

    public function __construct()
    {
        $this->body = $_POST;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string) file_get_contents('php://input'), true);
            if (is_array($decoded)) {
                $this->body = array_merge($this->body, $decoded);
            }
        } elseif (in_array($method, ['PUT', 'DELETE', 'PATCH'], true)) {
            parse_str((string) file_get_contents('php://input'), $parsed);
            $this->body = array_merge($this->body, $parsed);
        }
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $this->body);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        return $_SERVER[$key] ?? null;
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest';
    }
}
