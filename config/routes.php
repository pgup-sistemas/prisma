<?php

declare(strict_types=1);

use App\Core\Router;

return function (Router $r) {
    // ── Públicas ────────────────────────────────────────────────────────
    $r->get('/',              'HomeController@index');
    $r->get('/login',         'AuthController@showLogin');
    $r->post('/login',        'AuthController@login');
    $r->get('/register',      'AuthController@showRegister');
    $r->post('/register',     'AuthController@register');
    $r->get('/forgot',        'AuthController@showForgot');
    $r->post('/forgot',       'AuthController@sendReset');
    $r->get('/reset/{token}', 'AuthController@showReset');
    $r->post('/reset',        'AuthController@resetPassword');

    // Short URL / Encapsulador — público
    $r->get('/r/{slug}', 'RedirectController@handle');

    // Mini Ferramentas — público, sem login
    $r->get('/tools',           'ToolController@index');
    $r->post('/tools/currency', 'ToolController@currency');
    $r->post('/tools/cep',      'ToolController@cep');
    $r->post('/tools/pdf-compress', 'ToolController@pdfCompress');
    $r->post('/tools/pdf-to-markdown', 'ToolController@pdfToMarkdown');

    // PRISMA Launcher — agente de desktop, público
    $r->get('/download',              'LauncherAgentController@index');
    $r->get('/download/version.json', 'LauncherAgentController@version');
    $r->post('/agent/qr',             'LauncherAgentController@quickQr');
    $r->post('/agent/link',           'LauncherAgentController@quickLink');
    $r->post('/agent/bookmarks/sync', 'LauncherAgentController@syncBookmarks');

    // QR Code rápido — público, sem login, sem persistência (usado na Home)
    $r->post('/qr/quick', 'QRController@quickGenerate');

    // Launcher — público (widget embutido em domínio de terceiros, autentica via uid+api_key, não sessão)
    $r->get('/launcher/index',  'LauncherController@getIndex');
    $r->post('/launcher/track', 'LauncherController@track');
    $r->get('/launcher/embed',  'LauncherController@embedScript');

    // ── Autenticadas ─────────────────────────────────────────────────────
    $r->group(['middleware' => 'auth'], function (Router $r) {
        $r->get('/logout',    'AuthController@logout');
        $r->get('/dashboard', 'DashboardController@index');

        // QR Code
        $r->get('/generate',              'QRController@showGenerate');
        $r->post('/generate',             'QRController@generate');
        $r->post('/qr/preview',           'QRController@preview');
        $r->get('/qr/{uuid}',             'QRController@show');
        $r->delete('/qr/{uuid}',          'QRController@delete');
        $r->post('/qr/{uuid}/logo',       'QRController@addLogo');
        $r->get('/qr/{uuid}/logo/status', 'QRController@logoStatus');

        // Download
        $r->get('/download/{uuid}', 'DownloadController@download');

        // Histórico
        $r->get('/history', 'HistoryController@index');

        // Analytics
        $r->get('/analytics',        'AnalyticsController@index');
        $r->get('/analytics/{uuid}', 'AnalyticsController@show');

        // Batch
        $r->get('/batch',               'BatchController@index');
        $r->post('/batch',              'BatchController@process');
        $r->get('/batch/{id}/download', 'BatchController@download');

        // Scanner
        $r->get('/scanner',  'ScannerController@index');
        $r->post('/scanner', 'ScannerController@scan');

        // Encurtador + Encapsulador — ATENÇÃO: /links/create DEVE vir antes de /links/{uuid}
        $r->get('/links',             'LinkController@index');
        $r->get('/links/create',      'LinkController@create');
        $r->post('/links',            'LinkController@store');
        $r->get('/links/{uuid}',      'LinkController@show');
        $r->get('/links/{uuid}/edit', 'LinkController@editForm');
        $r->put('/links/{uuid}',      'LinkController@update');
        $r->delete('/links/{uuid}',   'LinkController@delete');

        // Hub Digital — admin — ATENÇÃO: /hub/create DEVE vir antes de /hub/{uuid}
        $r->get('/hub',                   'HubController@index');
        $r->get('/hub/create',            'HubController@create');
        $r->post('/hub',                  'HubController@store');
        $r->get('/hub/{uuid}/edit',       'HubController@edit');
        $r->put('/hub/{uuid}',            'HubController@update');
        $r->delete('/hub/{uuid}',         'HubController@delete');
        $r->post('/hub/{uuid}/blocks',    'HubController@addBlock');
        $r->put('/hub/block/{id}',        'HubController@updateBlock');
        $r->delete('/hub/block/{id}',     'HubController@deleteBlock');
        $r->post('/hub/block/reorder',    'HubController@reorderBlocks');

        // Importador de Favoritos — ATENÇÃO: /bookmarks/import DEVE vir antes de /bookmarks/{id}
        $r->get('/bookmarks',                   'BookmarkController@index');
        $r->get('/bookmarks/import',            'BookmarkController@importForm');
        $r->post('/bookmarks/import',           'BookmarkController@import');
        $r->post('/bookmarks/export',           'BookmarkController@export');
        $r->post('/bookmarks/health-check',     'BookmarkController@healthCheck');
        $r->post('/bookmarks/bulk',             'BookmarkController@bulkAction');
        $r->delete('/bookmarks/{id}',           'BookmarkController@delete');
        $r->post('/bookmarks/{id}/launcher',    'BookmarkController@addToLauncher');

        // Perfil
        $r->get('/profile',           'ProfileController@index');
        $r->post('/profile',          'ProfileController@update');
        $r->post('/profile/api-key',  'ProfileController@regenerateApiKey');

        // Agenda (gestão de agendamentos do Hub)
        $r->get('/agenda',              'BookingController@index');
        $r->post('/agenda/{id}/confirm', 'BookingController@confirm');
        $r->post('/agenda/{id}/cancel',  'BookingController@cancel');

        // Admin
        $r->group(['prefix' => '/admin', 'middleware' => 'role:admin'], function (Router $r) {
            $r->get('/users',           'AdminController@users');
            $r->post('/users/{id}/toggle', 'AdminController@toggleUser');
            $r->get('/settings',        'AdminController@settings');
            $r->post('/settings',       'AdminController@saveSettings');
        });
    });

    // API REST v1 — pública (autentica via header X-API-Key, não sessão de navegador)
    // Fora do grupo 'auth' de propósito: uma chamada de API sem cookie de sessão
    // deve retornar 401 JSON, não redirecionar para /login.
    $r->group(['prefix' => '/api/v1', 'middleware' => 'api_key'], function (Router $r) {
        $r->post('/qr',          'ApiController@generateQR');
        $r->get('/qr',           'ApiController@listQR');
        $r->get('/qr/{uuid}',    'ApiController@getQR');
        $r->delete('/qr/{uuid}', 'ApiController@deleteQR');
        $r->post('/links',       'ApiController@createLink');
        $r->get('/links',        'ApiController@listLinks');
        $r->get('/links/{uuid}', 'ApiController@getLink');
        $r->get('/stats',        'ApiController@stats');
    });

    // Hub Digital público — DEVE vir APÓS as rotas autenticadas /hub/create, /hub/{uuid}/edit etc.
    // O Router usa first-match, então rotas específicas registradas antes ganham prioridade.
    $r->post('/hub/{slug}/pix/{blockId}', 'HubController@pixGenerate');
    $r->post('/hub/{slug}/contact', 'HubController@submitContact');
    $r->get('/hub/{slug}/agenda/{blockId}/slots', 'HubController@agendaSlots');
    $r->post('/hub/{slug}/agenda/{blockId}/book', 'HubController@agendaBook');
    $r->get('/hub/{slug}', 'HubController@public');
};
