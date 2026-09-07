<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\QRCode;
use App\Models\Scan;

class AnalyticsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];

        $topQRs = QRCode::topForUser($userId, 10);

        $totalScans   = Scan::totalForUser($userId);
        $recentScans  = Scan::recentForUser($userId, 10);
        $dailyScans   = Scan::scansLastDays($userId, 30);

        $this->render('analytics/index', [
            'title'       => 'Analytics',
            'topQRs'      => $topQRs,
            'totalScans'  => $totalScans,
            'recentScans' => $recentScans,
            'dailyScans'  => $dailyScans,
        ]);
    }

    public function show(string $uuid): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];
        $qr = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== $userId) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        $qrId = (int) $qr['id'];

        $byDay     = Scan::countByDay($qrId, 30);
        $byDevice  = Scan::countByDevice($qrId);
        $byCountry = Scan::countByCountry($qrId);
        $recent    = Scan::forQR($qrId, 20);

        $this->render('analytics/show', [
            'title'     => 'Analytics — ' . ($qr['label'] ?: 'QR Code'),
            'qr'        => $qr,
            'byDay'     => $byDay,
            'byDevice'  => $byDevice,
            'byCountry' => $byCountry,
            'recent'    => $recent,
        ]);
    }
}
