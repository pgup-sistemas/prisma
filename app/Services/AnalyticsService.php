<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Scan;
use App\Models\QRCode;

class AnalyticsService
{
    public static function recordScan(int $qrId, string $ip, string $ua, string $referer = ''): void
    {
        $geo = GeoService::lookup($ip);

        Scan::create([
            'qr_id'      => $qrId,
            'ip_hash'    => ipHash($ip),
            'user_agent' => mb_substr($ua, 0, 512),
            'referer'    => mb_substr($referer, 0, 512),
            'country'    => $geo['country'],
            'city'       => $geo['city'],
            'device'     => detectDevice($ua),
        ]);

        QRCode::incrementScanCount($qrId);
    }

    public static function recordScanAsync(int $qrId, string $ip, string $ua, string $referer = ''): void
    {
        // Phase 6 will use QueueService — for now record synchronously
        self::recordScan($qrId, $ip, $ua, $referer);
    }
}
