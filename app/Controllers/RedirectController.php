<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Cache;
use App\Models\Link;
use App\Models\LinkClick;
use App\Models\QRCode;
use App\Services\AnalyticsService;
use App\Services\GeoService;

class RedirectController extends Controller
{
    public function handle(string $slug): void
    {
        // ── 1. Tenta resolver como Link (encurtador/encapsulador) ─────────
        $linkCacheKey = 'link:' . $slug;
        $link = Cache::get($linkCacheKey);

        if ($link === null) {
            $link = Link::findBySlug($slug);
            if ($link !== null) {
                Cache::set($linkCacheKey, $link, 300);
            }
        }

        if ($link !== null) {
            $this->handleLink($link);
            return;
        }

        // ── 2. Fallback: tenta resolver como QR Code short URL ───────────
        $qrCacheKey = 'qr_short:' . $slug;
        $qr = Cache::get($qrCacheKey);

        if ($qr === null) {
            $qr = QRCode::findBy('short_code', $slug);
            if ($qr !== null) {
                Cache::set($qrCacheKey, $qr, 300);
            }
        }

        if ($qr !== null) {
            $this->handleQR($qr);
            return;
        }

        // ── 3. Não encontrado ────────────────────────────────────────────
        http_response_code(404);
        require ROOT . '/app/Views/errors/404.php';
    }

    // ── Link handler ─────────────────────────────────────────────────────

    private function handleLink(array $link): void
    {
        // Link inativo
        if (!(bool) $link['active']) {
            http_response_code(410);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        // Expirado
        if (!empty($link['expires_at']) && strtotime($link['expires_at']) < time()) {
            http_response_code(410);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        $geo    = GeoService::lookup($ip);
        $device = detectDevice($ua);

        $destination = $link['destination'];
        $variant     = null;

        switch ($link['wrapper_type']) {
            case 'utm':
                $destination = $this->applyUtm($destination, $link['utm_json']);
                break;

            case 'conditional':
                $destination = $this->applyConditional(
                    $destination, $link['conditions_json'], $device, $geo['country'] ?? ''
                );
                break;

            case 'ab':
                [$destination, $variant] = $this->applyAB($destination, $link['ab_variants'], $ip);
                break;

            case 'intersticial':
            case 'cloaking':
                // Registra o clique antes de renderizar a página
                $this->recordLinkClick((int) $link['id'], $ip, $ua, $referer, $geo, $device, $variant);
                Link::incrementClickCount((int) $link['id']);

                if ($link['wrapper_type'] === 'intersticial') {
                    $this->renderIntersticial($link, $destination);
                } else {
                    $this->renderCloaking($link, $destination);
                }
                return;

            default: // 'none'
                break;
        }

        $this->recordLinkClick((int) $link['id'], $ip, $ua, $referer, $geo, $device, $variant);
        Link::incrementClickCount((int) $link['id']);

        header('Location: ' . $destination, true, 302);
        exit;
    }

    private function applyUtm(string $url, ?string $utmJson): string
    {
        if (empty($utmJson)) {
            return $url;
        }

        $utm = json_decode($utmJson, true);
        if (!is_array($utm)) {
            return $url;
        }

        $map = [
            'source'   => 'utm_source',
            'medium'   => 'utm_medium',
            'campaign' => 'utm_campaign',
            'term'     => 'utm_term',
            'content'  => 'utm_content',
        ];

        $params = [];
        foreach ($map as $key => $param) {
            if (!empty($utm[$key])) {
                $params[$param] = $utm[$key];
            }
        }

        if (empty($params)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . http_build_query($params);
    }

    private function applyConditional(string $fallback, ?string $condJson,
                                      string $device, string $country): string
    {
        if (empty($condJson)) {
            return $fallback;
        }

        $conditions = json_decode($condJson, true);
        if (!is_array($conditions)) {
            return $fallback;
        }

        $hour = (int) date('G'); // 0-23

        foreach ($conditions as $cond) {
            if (empty($cond['field']) || empty($cond['destination'])) {
                continue;
            }

            $matches = match ($cond['field']) {
                'device'   => $this->evalEq($cond['operator'], $device, $cond['value']),
                'country'  => $this->evalEq($cond['operator'], $country, $cond['value']),
                'hour'     => $this->evalHour($cond['operator'], $hour, $cond['value']),
                default    => false,
            };

            if ($matches) {
                return (string) $cond['destination'];
            }
        }

        return $fallback;
    }

    private function evalEq(string $operator, string $actual, mixed $value): bool
    {
        return match ($operator) {
            'eq'  => $actual === (string) $value,
            'neq' => $actual !== (string) $value,
            'in'  => is_array($value) && in_array($actual, $value, true),
            default => false,
        };
    }

    private function evalHour(string $operator, int $hour, mixed $value): bool
    {
        return match ($operator) {
            'between' => is_array($value) && count($value) === 2
                         && $hour >= (int) $value[0] && $hour <= (int) $value[1],
            'eq'      => $hour === (int) $value,
            default   => false,
        };
    }

    private function applyAB(string $fallback, ?string $abJson, string $ip): array
    {
        if (empty($abJson)) {
            return [$fallback, null];
        }

        $variants = json_decode($abJson, true);
        if (!is_array($variants) || empty($variants)) {
            return [$fallback, null];
        }

        // Hash do IP → número consistente para o mesmo visitante
        $hash   = crc32($ip . date('Y-m-d'));
        $bucket = abs($hash) % 100;

        $cumulative = 0;
        foreach ($variants as $i => $v) {
            $cumulative += (int) ($v['weight'] ?? 0);
            if ($bucket < $cumulative) {
                return [(string) ($v['url'] ?? $fallback), 'v' . ($i + 1)];
            }
        }

        // Fallback se pesos não somam 100
        return [(string) ($variants[0]['url'] ?? $fallback), 'v1'];
    }

    private function recordLinkClick(int $linkId, string $ip, string $ua, string $referer,
                                     array $geo, string $device, ?string $variant): void
    {
        LinkClick::create([
            'link_id' => $linkId,
            'ip_hash' => ipHash($ip),
            'country' => $geo['country'],
            'device'  => $device,
            'referer' => mb_substr($referer, 0, 512),
            'variant' => $variant,
        ]);
    }

    private function renderIntersticial(array $link, string $destination): void
    {
        $title   = e($link['title'] ?: 'Você está sendo redirecionado…');
        $destEnc = json_encode($destination);
        $baseUrl = e(url('/'));

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$title}</title>
            <style>
                *{box-sizing:border-box;margin:0;padding:0}
                body{background:#0D1B2A;color:#E8F4F8;font-family:'Inter',system-ui,sans-serif;
                     min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;}
                .card{background:#152233;border:1px solid #1E3A5F;border-radius:16px;padding:2.5rem;
                      max-width:420px;width:90%;}
                .logo{font-size:1.5rem;font-weight:800;color:#2E86AB;margin-bottom:.25rem;}
                .spectrum{height:3px;background:linear-gradient(90deg,#FF6B6B,#FF9F43,#FFD93D,#6BCB77,#4D96FF,#C77DFF);
                           border-radius:2px;margin-bottom:1.5rem;}
                h2{font-size:1.1rem;margin-bottom:.5rem;color:#E8F4F8;}
                .dest{font-size:.85rem;color:#94A3B8;margin-bottom:1.5rem;word-break:break-all;}
                .counter{font-size:3rem;font-weight:800;color:#2E86AB;line-height:1;margin-bottom:.5rem;}
                .label{color:#94A3B8;font-size:.9rem;}
                .btn{display:inline-block;margin-top:1.5rem;padding:.6rem 1.5rem;background:#2E86AB;
                     color:#fff;border-radius:8px;text-decoration:none;font-size:.9rem;}
            </style>
        </head>
        <body>
            <div class="card">
                <div class="logo">PRISMA</div>
                <div class="spectrum"></div>
                <h2>{$title}</h2>
                <div class="dest">{$destination}</div>
                <div class="counter" id="n">5</div>
                <div class="label">segundos</div>
                <a href="{$destination}" class="btn" id="btn">Ir agora</a>
            </div>
            <script>
                var dest = {$destEnc};
                var n = 5;
                var t = setInterval(function(){
                    n--;
                    document.getElementById('n').textContent = n;
                    if(n <= 0){ clearInterval(t); window.location.replace(dest); }
                }, 1000);
            </script>
        </body>
        </html>
        HTML;

        exit;
    }

    private function renderCloaking(array $link, string $destination): void
    {
        $destEnc = e($destination);

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$destEnc}</title>
            <style>
                *{margin:0;padding:0;box-sizing:border-box;}
                html,body,iframe{width:100%;height:100%;border:none;display:block;}
            </style>
        </head>
        <body>
            <iframe src="{$destEnc}" title="Conteúdo" sandbox="allow-scripts allow-same-origin allow-forms allow-popups"></iframe>
        </body>
        </html>
        HTML;

        exit;
    }

    // ── QR Code handler ──────────────────────────────────────────────────

    private function handleQR(array $qr): void
    {
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        AnalyticsService::recordScanAsync((int) $qr['id'], $ip, $ua, $referer);

        // Para tipo URL: redireciona direto. Para outros: mostra a página do QR.
        if ($qr['type'] === 'url' && filter_var($qr['content'], FILTER_VALIDATE_URL)) {
            header('Location: ' . $qr['content'], true, 302);
        } else {
            header('Location: ' . url('/qr/' . $qr['uuid']), true, 302);
        }
        exit;
    }
}
