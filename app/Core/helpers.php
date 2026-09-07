<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}

if (!function_exists('e')) {
    function e(string $val): string
    {
        return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim((string) env('APP_URL', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('storageUrl')) {
    function storageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        // storage/qrcodes e storage/qr-logos são simlinkados em public/ (únicas pastas de storage públicas)
        return url('/' . preg_replace('#^storage/#', '', $path));
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $full = ROOT . '/public/assets/' . ltrim($path, '/');
        $version = is_file($full) ? substr((string) filemtime($full), -6) : '1';
        return url('/assets/' . ltrim($path, '/')) . '?v=' . $version;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never
    {
        header('Location: ' . $url, true, $code);
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $msg): void
    {
        $_SESSION['_flash'][$type][] = $msg;
    }
}

if (!function_exists('renderFlash')) {
    function renderFlash(): string
    {
        if (empty($_SESSION['_flash'])) {
            return '';
        }

        $map = [
            'success' => 'success',
            'error'   => 'danger',
            'warning' => 'warning',
            'info'    => 'info',
        ];

        $html = '';
        foreach ($_SESSION['_flash'] as $type => $messages) {
            $class = $map[$type] ?? 'secondary';
            foreach ($messages as $msg) {
                $html .= '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">'
                    . e($msg)
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                    . '</div>';
            }
        }

        unset($_SESSION['_flash']);

        return $html;
    }
}

if (!function_exists('uuid4')) {
    function uuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('shortCode')) {
    function shortCode(int $len = 8): string
    {
        $bytes = random_bytes($len);
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}

if (!function_exists('validMime')) {
    function validMime(string $path, array $allowed): bool
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return in_array($mime, $allowed, true);
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }
}

if (!function_exists('detectDevice')) {
    function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);

        if ($ua === '') {
            return 'unknown';
        }

        if (preg_match('/bot|crawl|spider|slurp|facebookexternalhit/', $ua)) {
            return 'bot';
        }

        if (preg_match('/tablet|ipad/', $ua)) {
            return 'tablet';
        }

        if (preg_match('/mobi|android|iphone/', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }
}

if (!function_exists('truncate')) {
    function truncate(string $str, int $len = 80): string
    {
        if (mb_strlen($str) <= $len) {
            return $str;
        }

        return mb_substr($str, 0, $len - 1) . '…';
    }
}

if (!function_exists('nplural')) {
    function nplural(int $n, string $s, string $p): string
    {
        return $n === 1 ? $s : $p;
    }
}

if (!function_exists('ipHash')) {
    function ipHash(string $ip): string
    {
        return hash('sha256', $ip . env('APP_KEY', ''));
    }
}

if (!function_exists('isJson')) {
    function isJson(string $str): bool
    {
        if ($str === '') {
            return false;
        }

        json_decode($str);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('jsonDecode')) {
    function jsonDecode(string $json): array
    {
        return json_decode($json, true) ?? [];
    }
}
