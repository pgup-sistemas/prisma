<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\QRCode;

class HistoryController extends Controller
{
    private const PER_PAGE = 15;

    public function index(): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];
        $page   = max(1, (int) $this->request->input('page', 1));
        $search = trim((string) $this->request->input('q', ''));
        $type   = (string) $this->request->input('type', '');

        $where  = 'user_id = ?';
        $params = [$userId];

        if ($search !== '') {
            $where   .= ' AND (label LIKE ? OR content LIKE ?)';
            $like     = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ($type !== '') {
            $where   .= ' AND type = ?';
            $params[] = $type;
        }

        $result = QRCode::paginate($page, self::PER_PAGE, $where, $params);

        $this->render('history/index', [
            'title'  => 'Histórico de QR Codes',
            'qrcodes' => $result['data'],
            'total'  => $result['total'],
            'pages'  => $result['pages'],
            'page'   => $result['page'],
            'search' => $search,
            'type'   => $type,
        ]);
    }
}
