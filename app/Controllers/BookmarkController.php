<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Bookmark;
use App\Models\BookmarkFolder;
use App\Services\BookmarkImporterService;
use App\Services\QueueService;

class BookmarkController extends Controller
{
    private const PER_PAGE = 30;

    public function index(): void
    {
        $this->requireAuth();
        $user = $this->user();

        $folderId = $this->request->input('folder');
        $folderId = $folderId !== null && $folderId !== '' ? (int) $folderId : null;
        $search   = trim((string) $this->request->input('q', ''));
        $page     = max(1, (int) $this->request->input('page', 1));

        $result = Bookmark::paginateForUser((int) $user['id'], $folderId, $search, $page, self::PER_PAGE);
        $tree   = BookmarkFolder::tree((int) $user['id']);

        $this->render('bookmarks/index', [
            'bookmarks'      => $result['data'],
            'tree'           => $tree,
            'currentFolder'  => $folderId,
            'search'         => $search,
            'page'           => $result['page'],
            'pages'          => $result['pages'],
            'totalFiltered'  => $result['total'],
            'total'          => Bookmark::countForUser((int) $user['id']),
        ], 'main');
    }

    public function importForm(): void
    {
        $this->requireAuth();
        $this->render('bookmarks/import', [], 'main');
    }

    public function import(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $user = $this->user();
        $file = $this->request->file('bookmarks_file');

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
            $this->flash('error', 'Selecione um arquivo .html válido para importar.');
            $this->redirect(url('/bookmarks'));
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $this->flash('error', 'Arquivo muito grande (máx. 10MB).');
            $this->redirect(url('/bookmarks'));
            return;
        }

        if (!validMime($file['tmp_name'], ['text/html', 'text/plain'])) {
            $this->flash('error', 'Formato inválido — envie um export NETSCAPE Bookmark File (.html).');
            $this->redirect(url('/bookmarks'));
            return;
        }

        $content = file_get_contents($file['tmp_name']);
        $result  = BookmarkImporterService::import($content, (int) $user['id']);

        if ($result['imported'] > 0) {
            $this->flash('success', "{$result['imported']} favoritos importados, {$result['duplicates']} duplicados ignorados, {$result['folders']} pastas criadas.");
        } else {
            $this->flash('warning', 'Nenhum favorito novo foi importado (' . $result['duplicates'] . ' duplicados ignorados).');
        }

        foreach ($result['errors'] as $err) {
            $this->flash('error', $err);
        }

        $this->redirect(url('/bookmarks'));
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $user = $this->user();
        $html = BookmarkImporterService::export((int) $user['id']);

        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="prisma-favoritos.html"');
        header('Content-Length: ' . strlen($html));
        echo $html;
    }

    public function healthCheck(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $user      = $this->user();
        $bookmarks = Bookmark::forUser((int) $user['id']);

        foreach ($bookmarks as $b) {
            QueueService::push('bookmark_health', ['bookmark_id' => (int) $b['id']]);
        }

        $this->json(['success' => true, 'queued' => count($bookmarks)]);
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $id = (int) $id;
        $this->ownedBookmark($id);

        Bookmark::delete($id);
        $this->json(['success' => true]);
    }

    public function addToLauncher(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $id = (int) $id;
        $bookmark = $this->ownedBookmark($id);

        $inLauncher = (int) $bookmark['in_launcher'] === 1 ? 0 : 1;
        Bookmark::update($id, ['in_launcher' => $inLauncher]);

        $this->json(['success' => true, 'in_launcher' => (bool) $inLauncher]);
    }

    /**
     * Ação em massa sobre favoritos selecionados: marcar/desmarcar no Launcher ou excluir.
     */
    public function bulkAction(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $this->user()['id'];
        $action = (string) $this->request->input('bulk_action', '');
        $ids    = $this->request->input('ids', []);

        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids), fn (string $v) => $v !== '');
        }

        if (!is_array($ids) || empty($ids)) {
            $this->json(['success' => false, 'error' => 'Nenhum favorito selecionado.'], 422);
            return;
        }

        $affected = match ($action) {
            'launcher_on'  => Bookmark::bulkSetLauncher($ids, $userId, true),
            'launcher_off' => Bookmark::bulkSetLauncher($ids, $userId, false),
            'delete'       => Bookmark::bulkDelete($ids, $userId),
            default        => null,
        };

        if ($affected === null) {
            $this->json(['success' => false, 'error' => 'Ação inválida.'], 422);
            return;
        }

        $this->json(['success' => true, 'affected' => $affected]);
    }

    private function ownedBookmark(int $id): array
    {
        $bookmark = Bookmark::find($id);
        if ($bookmark === null || (int) $bookmark['user_id'] !== (int) $this->user()['id']) {
            $this->json(['success' => false, 'error' => 'Favorito não encontrado.'], 404);
            exit;
        }
        return $bookmark;
    }
}
