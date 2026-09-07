<?php

declare(strict_types=1);

namespace App\Services;

class StorageService
{
    private string $driver;

    public function __construct()
    {
        $this->driver = (string) env('STORAGE_DRIVER', 'local');
    }

    public function put(string $destination, string $sourcePath): string
    {
        return match ($this->driver) {
            'local' => $this->putLocal($destination, $sourcePath),
            default => throw new \RuntimeException("Driver de storage não suportado: {$this->driver}"),
        };
    }

    public function get(string $path): string
    {
        $full = ROOT . '/' . ltrim($path, '/');

        if (!is_file($full)) {
            throw new \RuntimeException("Arquivo não encontrado: {$path}");
        }

        return (string) file_get_contents($full);
    }

    public function delete(string $path): bool
    {
        $full = ROOT . '/' . ltrim($path, '/');

        return is_file($full) ? unlink($full) : false;
    }

    public function url(string $path): string
    {
        return url('/' . ltrim($path, '/'));
    }

    private function putLocal(string $destination, string $sourcePath): string
    {
        $destination = ltrim($destination, '/');
        $full = ROOT . '/' . $destination;
        $dir = dirname($full);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!copy($sourcePath, $full)) {
            throw new \RuntimeException("Falha ao salvar arquivo em {$destination}");
        }

        return $destination;
    }
}
