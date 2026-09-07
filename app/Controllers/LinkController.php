<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\Link;
use App\Models\LinkClick;

class LinkController extends Controller
{
    private const WRAPPER_TYPES = ['none', 'intersticial', 'utm', 'conditional', 'ab', 'cloaking'];

    public function index(): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];
        $page   = max(1, (int) $this->request->input('page', 1));
        $search = trim((string) $this->request->input('q', ''));
        $type   = (string) $this->request->input('type', '');

        $result = Link::forUser($userId, $page, 15, $search, $type);

        $this->render('links/index', [
            'title'   => 'Links',
            'links'   => $result['data'],
            'total'   => $result['total'],
            'pages'   => $result['pages'],
            'page'    => $result['page'],
            'search'  => $search,
            'type'    => $type,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('links/create', [
            'title' => 'Novo Link',
            'link'  => null,
        ]);
    }

    public function editForm(string $uuid): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];
        $link   = Link::findByUuidForUser($uuid, $userId);

        if ($link === null) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        $this->render('links/create', [
            'title' => 'Editar Link',
            'link'  => $link,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $this->user()['id'];

        $destination  = trim((string) $this->request->input('destination', ''));
        $title        = trim((string) $this->request->input('title', ''));
        $slug         = trim((string) $this->request->input('slug', ''));
        $wrapperType  = (string) $this->request->input('wrapper_type', 'none');
        $expiresAt    = (string) $this->request->input('expires_at', '');

        // Validação básica
        $v = Validator::make(
            ['destination' => $destination, 'wrapper_type' => $wrapperType],
            ['destination' => 'required|url', 'wrapper_type' => 'required']
        );

        if ($v->fails()) {
            $this->flash('danger', implode(' ', array_merge(...array_values($v->errors()))));
            $this->redirect(url('/links/create'));
        }

        if (!in_array($wrapperType, self::WRAPPER_TYPES, true)) {
            $this->flash('danger', 'Tipo de encapsulador inválido.');
            $this->redirect(url('/links/create'));
        }

        // Slug: usa o fornecido ou gera um aleatório
        if ($slug === '') {
            $slug = $this->generateUniqueSlug();
        } else {
            $slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $slug);
            if (Link::slugExists($slug)) {
                $this->flash('danger', 'Esse slug já está em uso. Escolha outro.');
                $this->redirect(url('/links/create'));
            }
        }

        $data = [
            'uuid'         => uuid4(),
            'user_id'      => $userId,
            'slug'         => $slug,
            'destination'  => $destination,
            'title'        => $title,
            'wrapper_type' => $wrapperType,
            'active'       => 1,
        ];

        if ($expiresAt !== '') {
            $data['expires_at'] = $expiresAt;
        }

        $this->fillWrapperData($data, $wrapperType);

        Link::create($data);

        $this->flash('success', 'Link criado com sucesso!');
        $this->redirect(url('/links'));
    }

    public function show(string $uuid): void
    {
        $this->requireAuth();

        $userId = (int) $this->user()['id'];
        $link   = Link::findByUuidForUser($uuid, $userId);

        if ($link === null) {
            http_response_code(404);
            require ROOT . '/app/Views/errors/404.php';
            return;
        }

        $linkId = (int) $link['id'];

        $byDay     = LinkClick::countByDay($linkId, 30);
        $byDevice  = LinkClick::countByDevice($linkId);
        $byCountry = LinkClick::countByCountry($linkId);
        $byVariant = $link['wrapper_type'] === 'ab' ? LinkClick::countByVariant($linkId) : [];
        $recent    = LinkClick::recent($linkId, 20);

        $this->render('links/show', [
            'title'     => 'Link — ' . ($link['title'] ?: $link['slug']),
            'link'      => $link,
            'byDay'     => $byDay,
            'byDevice'  => $byDevice,
            'byCountry' => $byCountry,
            'byVariant' => $byVariant,
            'recent'    => $recent,
        ]);
    }

    public function update(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $this->user()['id'];
        $link   = Link::findByUuidForUser($uuid, $userId);

        if ($link === null) {
            $this->json(['error' => 'Não encontrado'], 404);
            return;
        }

        $destination = trim((string) $this->request->input('destination', $link['destination']));
        $title       = trim((string) $this->request->input('title', $link['title'] ?? ''));
        $wrapperType = (string) $this->request->input('wrapper_type', $link['wrapper_type']);
        $active      = (bool) $this->request->input('active', $link['active']);
        $expiresAt   = (string) $this->request->input('expires_at', '');

        if (!filter_var($destination, FILTER_VALIDATE_URL)) {
            $this->json(['error' => 'URL de destino inválida.'], 422);
            return;
        }

        if (!in_array($wrapperType, self::WRAPPER_TYPES, true)) {
            $this->json(['error' => 'Tipo de encapsulador inválido.'], 422);
            return;
        }

        $data = [
            'destination'  => $destination,
            'title'        => $title,
            'wrapper_type' => $wrapperType,
            'active'       => $active ? 1 : 0,
            'expires_at'   => $expiresAt !== '' ? $expiresAt : null,
            // Reset wrapper JSON fields on type change
            'utm_json'        => null,
            'conditions_json' => null,
            'ab_variants'     => null,
        ];

        $this->fillWrapperData($data, $wrapperType);

        Link::update((int) $link['id'], $data);

        // Invalida cache
        \App\Core\Cache::del('link:' . $link['slug']);

        $this->json(['success' => true, 'redirect' => url('/links/' . $uuid)]);
    }

    public function delete(string $uuid): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) $this->user()['id'];
        $link   = Link::findByUuidForUser($uuid, $userId);

        if ($link === null) {
            $this->json(['error' => 'Não encontrado'], 404);
            return;
        }

        Link::delete((int) $link['id']);
        \App\Core\Cache::del('link:' . $link['slug']);

        $this->json(['success' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function fillWrapperData(array &$data, string $wrapperType): void
    {
        switch ($wrapperType) {
            case 'utm':
                $data['utm_json'] = json_encode([
                    'source'   => (string) $this->request->input('utm_source', ''),
                    'medium'   => (string) $this->request->input('utm_medium', ''),
                    'campaign' => (string) $this->request->input('utm_campaign', ''),
                    'term'     => (string) $this->request->input('utm_term', ''),
                    'content'  => (string) $this->request->input('utm_content', ''),
                ]);
                break;

            case 'conditional':
                $raw = (string) $this->request->input('conditions_json', '[]');
                $conds = json_decode($raw, true);
                $data['conditions_json'] = json_encode(is_array($conds) ? $conds : []);
                break;

            case 'ab':
                $raw = (string) $this->request->input('ab_variants', '[]');
                $variants = json_decode($raw, true);
                $data['ab_variants'] = json_encode(is_array($variants) ? $variants : []);
                break;
        }
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
}
