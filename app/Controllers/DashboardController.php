<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = $this->user()['id'];
        $db  = Database::get();

        // QR Codes
        $qr_total = (int)$db->prepare('SELECT COUNT(*) FROM qrcodes WHERE user_id=?')->execute([$uid]) ?: 0;
        $stmt = $db->prepare('SELECT COUNT(*) FROM qrcodes WHERE user_id=?');
        $stmt->execute([$uid]);
        $qr_total = (int)$stmt->fetchColumn();

        // Scans totais
        $stmt = $db->prepare('SELECT COALESCE(SUM(scan_count),0) FROM qrcodes WHERE user_id=?');
        $stmt->execute([$uid]);
        $scan_total = (int)$stmt->fetchColumn();

        // Links
        $stmt = $db->prepare('SELECT COUNT(*) FROM links WHERE user_id=?');
        $stmt->execute([$uid]);
        $link_total = (int)$stmt->fetchColumn();

        // Cliques em links
        $stmt = $db->prepare('SELECT COALESCE(SUM(click_count),0) FROM links WHERE user_id=?');
        $stmt->execute([$uid]);
        $click_total = (int)$stmt->fetchColumn();

        // QR Codes recentes (5)
        $stmt = $db->prepare('SELECT uuid, type, label, scan_count, created_at FROM qrcodes WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
        $stmt->execute([$uid]);
        $recent_qr = $stmt->fetchAll();

        // Links recentes (5)
        $stmt = $db->prepare('SELECT uuid, slug, title, click_count, created_at FROM links WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
        $stmt->execute([$uid]);
        $recent_links = $stmt->fetchAll();

        // Créditos
        $stmt = $db->prepare('SELECT credits FROM users WHERE id=?');
        $stmt->execute([$uid]);
        $credits = (int)$stmt->fetchColumn();

        $this->render('dashboard/index', [
            'title'        => 'Dashboard',
            'qr_total'     => $qr_total,
            'scan_total'   => $scan_total,
            'link_total'   => $link_total,
            'click_total'  => $click_total,
            'credits'      => $credits,
            'recent_qr'    => $recent_qr,
            'recent_links' => $recent_links,
        ]);
    }
}
