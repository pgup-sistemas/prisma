<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bookmark;
use App\Models\BookmarkFolder;

class BookmarkImporterService
{
    private const MAX_BOOKMARKS = 5000;

    /**
     * Parser NETSCAPE Bookmark File Format.
     * Constrói árvore de pastas via <H3>/<DL> e importa bookmarks via <A HREF>.
     * Deduplica comparando a URL contra os favoritos já existentes do usuário.
     */
    public static function import(string $htmlContent, int $userId): array
    {
        $imported   = 0;
        $duplicates = 0;
        $foldersNew = 0;
        $errors     = [];

        $html = preg_replace('/<p>/i', '', $htmlContent) ?? $htmlContent;

        preg_match_all(
            '/<DL>|<\/DL>|<H3([^>]*)>(.*?)<\/H3>|<A([^>]*)>(.*?)<\/A>/is',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            return ['imported' => 0, 'duplicates' => 0, 'folders' => 0, 'errors' => ['Nenhum favorito encontrado — arquivo não é um export NETSCAPE válido.']];
        }

        $existingUrls = array_flip(array_column(Bookmark::forUser($userId), 'url'));

        $stack = [null];
        $pendingFolderName = null;
        $position = 0;

        foreach ($matches as $m) {
            $full = $m[0];

            if (stripos($full, '</dl') === 0) {
                if (count($stack) > 1) {
                    array_pop($stack);
                }
                continue;
            }

            if (stripos($full, '<dl') === 0) {
                if ($pendingFolderName !== null) {
                    $parentId = end($stack);
                    $folderId = BookmarkFolder::findOrCreate($userId, $pendingFolderName, $parentId === false ? null : $parentId);
                    $stack[] = $folderId;
                    $foldersNew++;
                    $pendingFolderName = null;
                } else {
                    $stack[] = end($stack);
                }
                continue;
            }

            // <H3> — nome de pasta
            if (stripos($full, '<h3') === 0) {
                $pendingFolderName = mb_substr(html_entity_decode(strip_tags($m[2] ?? ''), ENT_QUOTES, 'UTF-8'), 0, 200);
                continue;
            }

            // <A HREF="..."> — bookmark
            if (stripos($full, '<a') === 0) {
                if ($imported + $duplicates >= self::MAX_BOOKMARKS) {
                    $errors[] = 'Limite de ' . self::MAX_BOOKMARKS . ' favoritos por importação atingido.';
                    break;
                }

                $attrs = $m[3] ?? '';
                $title = mb_substr(trim(html_entity_decode(strip_tags($m[4] ?? ''), ENT_QUOTES, 'UTF-8')), 0, 500);

                if (!preg_match('/HREF="([^"]*)"/i', $attrs, $hrefMatch)) {
                    continue;
                }
                $url = html_entity_decode($hrefMatch[1], ENT_QUOTES, 'UTF-8');

                if ($url === '' || !preg_match('#^https?://#i', $url)) {
                    continue;
                }

                if (isset($existingUrls[$url])) {
                    $duplicates++;
                    continue;
                }

                $favicon = null;
                if (preg_match('/ICON="([^"]*)"/i', $attrs, $iconMatch)) {
                    $favicon = $iconMatch[1];
                }
                // Chrome/Edge embutem o favicon como data:image/...;base64,... (frequentemente
                // muito maior que os 512 chars da coluna) — descarta em vez de truncar e quebrar o INSERT.
                if ($favicon !== null && mb_strlen($favicon) > 512) {
                    $favicon = null;
                }

                $folderId = end($stack);

                Bookmark::create([
                    'user_id'   => $userId,
                    'folder_id' => $folderId === false ? null : $folderId,
                    'title'     => $title !== '' ? $title : mb_substr($url, 0, 500),
                    'url'       => $url,
                    'favicon'   => $favicon,
                    'health'    => 'unknown',
                    'position'  => $position++,
                ]);

                $existingUrls[$url] = true;
                $imported++;
            }
        }

        return [
            'imported'   => $imported,
            'duplicates' => $duplicates,
            'folders'    => $foldersNew,
            'errors'     => $errors,
        ];
    }

    /**
     * Gera export NETSCAPE Bookmark File Format preservando a hierarquia de pastas.
     */
    public static function export(int $userId): string
    {
        $tree      = BookmarkFolder::tree($userId);
        $bookmarks = Bookmark::forUser($userId);

        $byFolder = [];
        foreach ($bookmarks as $b) {
            $key = $b['folder_id'] !== null ? (int) $b['folder_id'] : 0;
            $byFolder[$key][] = $b;
        }

        $html  = "<!DOCTYPE NETSCAPE-Bookmark-file-1>\n";
        $html .= "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">\n";
        $html .= "<TITLE>Bookmarks</TITLE>\n<H1>Bookmarks</H1>\n<DL><p>\n";
        $html .= self::renderFolderLinks($byFolder[0] ?? []);
        $html .= self::renderTree($tree, $byFolder, 1);
        $html .= "</DL><p>\n";

        return $html;
    }

    private static function renderTree(array $folders, array $byFolder, int $depth): string
    {
        $indent = str_repeat('    ', $depth);
        $html = '';

        foreach ($folders as $folder) {
            $html .= $indent . '<DT><H3>' . htmlspecialchars($folder['name'], ENT_QUOTES, 'UTF-8') . "</H3>\n";
            $html .= $indent . "<DL><p>\n";
            $html .= self::renderFolderLinks($byFolder[(int) $folder['id']] ?? [], $depth + 1);
            if (!empty($folder['children'])) {
                $html .= self::renderTree($folder['children'], $byFolder, $depth + 1);
            }
            $html .= $indent . "</DL><p>\n";
        }

        return $html;
    }

    private static function renderFolderLinks(array $bookmarks, int $depth = 1): string
    {
        $indent = str_repeat('    ', $depth);
        $html = '';

        foreach ($bookmarks as $b) {
            $html .= $indent . '<DT><A HREF="' . htmlspecialchars($b['url'], ENT_QUOTES, 'UTF-8') . '"';
            if (!empty($b['favicon'])) {
                $html .= ' ICON="' . htmlspecialchars($b['favicon'], ENT_QUOTES, 'UTF-8') . '"';
            }
            $html .= '>' . htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8') . "</A>\n";
        }

        return $html;
    }

    /**
     * Health check assíncrono — chamado pelo worker (queue: bookmark_health).
     * HEAD request com timeout 5s. HTTP 200-399 → 'ok', senão/timeout → 'broken'.
     */
    public static function checkHealth(array $payload): void
    {
        $id  = (int) ($payload['bookmark_id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $bookmark = Bookmark::find($id);
        if ($bookmark === null) {
            return;
        }

        Bookmark::markHealth($id, self::probe($bookmark['url']));
    }

    private static function probe(string $url): string
    {
        if (!function_exists('curl_init')) {
            return 'unknown';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'PRISMA-BookmarkHealthCheck/1.0',
        ]);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_errno($ch);
        curl_close($ch);

        if ($error !== 0) {
            return 'broken';
        }

        return ($status >= 200 && $status < 400) ? 'ok' : 'broken';
    }
}
