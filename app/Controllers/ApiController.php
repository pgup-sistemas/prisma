<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Link;
use App\Models\QRCode;
use App\Models\Scan;
use App\Models\User;
use App\Services\QRGenerator;
use App\Services\ShortURLService;

class ApiController extends Controller
{
    private function apiUser(): array
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? '';

        // Middleware 'api_key' já validou a chave antes de chegar aqui.
        return User::findByApiKey($key);
    }

    private function ok(mixed $data, array $meta = [], int $status = 200): void
    {
        $payload = ['success' => true, 'data' => $data];
        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }
        $this->json($payload, $status);
    }

    private function fail(int $code, string $message, array $details = []): void
    {
        $error = ['code' => $code, 'message' => $message];
        if (!empty($details)) {
            $error['details'] = $details;
        }
        $this->json(['success' => false, 'error' => $error], $code);
    }

    // ── QR Codes ────────────────────────────────────────────────────────

    public function generateQR(): void
    {
        $user = $this->apiUser();
        $data = $this->request->all();
        $type = (string) ($data['type'] ?? 'url');

        if (!isset(QRGenerator::TYPE_FIELDS[$type])) {
            $this->fail(422, 'Tipo de QR Code inválido.', ['type' => $type]);
            return;
        }

        $fields = array_intersect_key($data, array_flip(QRGenerator::TYPE_FIELDS[$type]));
        $validator = Validator::make($fields, QRGenerator::TYPE_RULES[$type]);

        if ($validator->fails()) {
            $this->fail(422, 'Dados inválidos.', $validator->errors());
            return;
        }

        $generator = new QRGenerator();
        $content = $generator->buildContent($type, $fields);

        $uuid = uuid4();
        $style = [
            'size'        => max(128, min(2048, (int) ($data['size'] ?? 512))),
            'ecc_level'   => in_array($data['ecc_level'] ?? null, ['L', 'M', 'Q', 'H'], true) ? $data['ecc_level'] : 'M',
            'fg_color'    => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($data['fg_color'] ?? '')) ? $data['fg_color'] : '#000000',
            'bg_color'    => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($data['bg_color'] ?? '')) ? $data['bg_color'] : '#FFFFFF',
            'transparent' => !empty($data['transparent']),
        ];

        $files = $generator->render($content, $style, $uuid);
        $shortCode = ShortURLService::generateCode();

        $qrId = QRCode::create([
            'uuid'          => $uuid,
            'user_id'       => $user['id'],
            'type'          => $type,
            'content'       => $content,
            'label'         => (string) ($data['label'] ?? ''),
            'fg_color'      => $style['fg_color'],
            'bg_color'      => $style['bg_color'],
            'transparent'   => $style['transparent'] ? 1 : 0,
            'ecc_level'     => $style['ecc_level'],
            'size'          => $style['size'],
            'file_png'      => $files['png'],
            'file_svg'      => $files['svg'],
            'short_code'    => $shortCode,
            'short_url'     => ShortURLService::buildUrl($shortCode),
            'render_status' => 'done',
        ]);

        $this->ok($this->formatQR(QRCode::find($qrId)), [], 201);
    }

    public function listQR(): void
    {
        $user = $this->apiUser();
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = min(100, max(1, (int) $this->request->input('per_page', 20)));

        $result = QRCode::paginate($page, $perPage, 'user_id = ?', [$user['id']]);

        $this->ok(
            array_map([$this, 'formatQR'], $result['data']),
            ['page' => $result['page'], 'pages' => $result['pages'], 'total' => $result['total']]
        );
    }

    public function getQR(string $uuid): void
    {
        $user = $this->apiUser();
        $qr = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== (int) $user['id']) {
            $this->fail(404, 'QR Code não encontrado.');
            return;
        }

        $this->ok($this->formatQR($qr));
    }

    public function deleteQR(string $uuid): void
    {
        $user = $this->apiUser();
        $qr = QRCode::findByUuid($uuid);

        if ($qr === null || (int) $qr['user_id'] !== (int) $user['id']) {
            $this->fail(404, 'QR Code não encontrado.');
            return;
        }

        foreach (['file_png', 'file_svg', 'file_logo_png'] as $field) {
            if (!empty($qr[$field]) && is_file(ROOT . '/' . $qr[$field])) {
                @unlink(ROOT . '/' . $qr[$field]);
            }
        }

        QRCode::delete((int) $qr['id']);
        $this->ok(['deleted' => true]);
    }

    private function formatQR(array $qr): array
    {
        return [
            'uuid'       => $qr['uuid'],
            'type'       => $qr['type'],
            'content'    => $qr['content'],
            'label'      => $qr['label'],
            'size'       => (int) $qr['size'],
            'scan_count' => (int) $qr['scan_count'],
            'short_url'  => $qr['short_url'],
            'png_url'    => storageUrl($qr['file_png']),
            'svg_url'    => storageUrl($qr['file_svg']),
            'created_at' => $qr['created_at'],
        ];
    }

    // ── Links (Encurtador) ──────────────────────────────────────────────

    public function createLink(): void
    {
        $user = $this->apiUser();
        $data = $this->request->all();

        $validator = Validator::make($data, ['destination' => 'required|url']);
        if ($validator->fails()) {
            $this->fail(422, 'Dados inválidos.', $validator->errors());
            return;
        }

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug !== '') {
            $slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $slug);
            if (Link::slugExists($slug)) {
                $this->fail(422, 'Esse slug já está em uso.', ['slug' => $slug]);
                return;
            }
        } else {
            $slug = $this->generateUniqueSlug();
        }

        $linkId = Link::create([
            'uuid'         => uuid4(),
            'user_id'      => $user['id'],
            'slug'         => $slug,
            'destination'  => (string) $data['destination'],
            'title'        => (string) ($data['title'] ?? ''),
            'wrapper_type' => 'none',
            'active'       => 1,
        ]);

        $this->ok($this->formatLink(Link::find($linkId)), [], 201);
    }

    public function listLinks(): void
    {
        $user = $this->apiUser();
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = min(100, max(1, (int) $this->request->input('per_page', 20)));

        $result = Link::forUser((int) $user['id'], $page, $perPage);

        $this->ok(
            array_map([$this, 'formatLink'], $result['data']),
            ['page' => $result['page'], 'pages' => $result['pages'], 'total' => $result['total']]
        );
    }

    public function getLink(string $uuid): void
    {
        $user = $this->apiUser();
        $link = Link::findByUuidForUser($uuid, (int) $user['id']);

        if ($link === null) {
            $this->fail(404, 'Link não encontrado.');
            return;
        }

        $this->ok($this->formatLink($link));
    }

    private function formatLink(array $link): array
    {
        return [
            'uuid'         => $link['uuid'],
            'slug'         => $link['slug'],
            'destination'  => $link['destination'],
            'title'        => $link['title'],
            'wrapper_type' => $link['wrapper_type'],
            'click_count'  => (int) $link['click_count'],
            'short_url'    => url('/r/' . $link['slug']),
            'created_at'   => $link['created_at'],
        ];
    }

    private function generateUniqueSlug(int $len = 7): string
    {
        for ($i = 0; $i < 10; $i++) {
            $slug = shortCode($len);
            if (!Link::slugExists($slug)) {
                return $slug;
            }
        }
        return shortCode($len + 3);
    }

    // ── Estatísticas ────────────────────────────────────────────────────

    public function stats(): void
    {
        $user = $this->apiUser();
        $userId = (int) $user['id'];

        $this->ok([
            'qr_total'    => QRCode::totalForUser($userId),
            'scan_total'  => Scan::totalForUser($userId),
            'link_total'  => Link::totalForUser($userId),
            'click_total' => Link::totalClicksForUser($userId),
            'credits'     => (int) $user['credits'],
        ]);
    }
}
