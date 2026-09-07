<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\LauncherLink;
use App\Models\User;
use App\Services\LauncherIndexService;

class LauncherController extends Controller
{
    /**
     * GET /launcher/index — JSON index consumido pelo widget embutido no site do cliente.
     * Autenticação via uid (uuid do usuário) + key (api_key) em query string — não usa sessão,
     * pois a requisição parte de um domínio de terceiros (Shadow DOM widget).
     */
    public function getIndex(): void
    {
        header('Access-Control-Allow-Origin: *');

        $user = $this->resolveUser();
        if ($user === null) {
            $this->json(['error' => 'unauthorized'], 401);
            return;
        }

        Middleware::rateLimit('launcher:index:' . $user['uuid'], 120, 60);

        $index = LauncherIndexService::get((int) $user['id']);

        header('Cache-Control: private, max-age=' . (int) env('LAUNCHER_CACHE_TTL', 300));
        $this->json($index);
    }

    /**
     * POST /launcher/track — registra uso de um item (incrementa use_count / last_used_at).
     */
    public function track(): void
    {
        header('Access-Control-Allow-Origin: *');

        $user = $this->resolveUser();
        if ($user === null) {
            $this->json(['error' => 'unauthorized'], 401);
            return;
        }

        Middleware::rateLimit('launcher:track:' . $user['uuid'], 120, 60);

        $source   = (string) $this->request->input('source', 'manual');
        $sourceId = $this->request->input('source_id');
        $title    = trim((string) $this->request->input('title', ''));
        $url      = trim((string) $this->request->input('url', ''));
        $icon     = $this->request->input('icon');

        $allowedSources = ['qrcode', 'shortlink', 'bookmark', 'manual'];
        if (!in_array($source, $allowedSources, true) || $url === '') {
            $this->json(['error' => 'invalid_payload'], 422);
            return;
        }

        LauncherLink::touch(
            (int) $user['id'],
            $source,
            $sourceId !== null && $sourceId !== '' ? (int) $sourceId : null,
            $title !== '' ? $title : $url,
            $url,
            $icon !== null && $icon !== '' ? (string) $icon : null
        );

        LauncherIndexService::invalidate((int) $user['id']);

        $this->json(['success' => true]);
    }

    /**
     * GET /launcher/embed — serve o bundle JS (Shadow DOM) que o cliente inclui via <script src>.
     * O widget lê data-user-id / data-api-key do próprio <script> em runtime.
     */
    public function embedScript(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/javascript; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');

        $path = ROOT . '/public/assets/js/launcher-widget.js';

        if (!is_file($path)) {
            http_response_code(404);
            echo '// launcher-widget.js não encontrado';
            return;
        }

        readfile($path);
    }

    private function resolveUser(): ?array
    {
        $uid = (string) $this->request->input('uid', '');
        $key = (string) $this->request->input('key', '');

        if ($uid === '' || $key === '') {
            return null;
        }

        $user = User::findByUuid($uid);

        if ($user === null || empty($user['api_key']) || !hash_equals($user['api_key'], $key)) {
            return null;
        }

        if ((int) $user['active'] !== 1) {
            return null;
        }

        return $user;
    }
}
