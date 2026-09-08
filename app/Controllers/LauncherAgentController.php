<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Middleware;
use App\Core\Validator;
use App\Models\Bookmark;
use App\Models\Link;
use App\Models\User;
use App\Services\QRGenerator;
use App\Services\ShortURLService;

class LauncherAgentController extends Controller
{
    /** Versão mais recente publicada do agente de desktop (mantida em sincronia com desktop-agent/package.json). */
    private const LATEST_VERSION = '0.2.0';

    /** Limite de itens aceitos por chamada de sincronização — evita payloads abusivos. */
    private const SYNC_MAX_ITEMS = 500;

    /**
     * URLs "latest" do GitHub Releases — sempre resolvem pra versão mais recente
     * publicada, desde que o nome do arquivo (definido no build do electron-builder,
     * artifactName) não mude entre versões. Assim esta página nunca precisa ser
     * editada de novo a cada release nova do Launcher.
     */
    private const RELEASES_BASE = 'https://github.com/pgup-sistemas/prisma-launcher/releases/latest/download/';

    /**
     * @return array<string, array{available: bool, variants: array<array{url: string, label: string}>}>
     */
    private static function downloads(): array
    {
        return [
            'linux' => [
                'available' => true,
                'variants'  => [
                    ['url' => self::RELEASES_BASE . 'PRISMA-Launcher.AppImage', 'label' => '.AppImage'],
                    ['url' => self::RELEASES_BASE . 'PRISMA-Launcher.deb', 'label' => '.deb'],
                ],
            ],
            'windows' => [
                'available' => true,
                'variants'  => [
                    ['url' => self::RELEASES_BASE . 'PRISMA-Launcher-Setup.exe', 'label' => '.exe'],
                ],
            ],
            'mac' => [
                'available' => true,
                'variants'  => [
                    ['url' => self::RELEASES_BASE . 'PRISMA-Launcher-arm64.dmg', 'label' => 'Apple Silicon (M1/M2/M3)'],
                    ['url' => self::RELEASES_BASE . 'PRISMA-Launcher-x64.dmg', 'label' => 'Intel'],
                ],
            ],
        ];
    }

    public function index(): void
    {
        $this->render('download/index', [
            'title'            => 'Baixar o PRISMA Launcher — busca ultrarrápida no seu desktop',
            'meta_description' => 'Instale o PRISMA Launcher e acesse seus favoritos, links e QR Codes com um atalho global, direto do seu computador.',
            'downloads'        => self::downloads(),
            'version'          => self::LATEST_VERSION,
            'loggedIn'         => Auth::check(),
        ], 'public');
    }

    /**
     * GET /download/version.json — consumido pelo próprio agente para checar se há
     * uma versão mais nova disponível (comparado com app.getVersion()).
     */
    public function version(): void
    {
        header('Access-Control-Allow-Origin: *');
        $this->json([
            'version'      => self::LATEST_VERSION,
            'download_url' => url('/download'),
        ]);
    }

    /**
     * POST /agent/qr — gera um QR Code de URL a partir da busca rápida do agente de desktop.
     * Autenticação via uid+key (mesmo padrão do widget Launcher) — evita preflight CORS,
     * já que o corpo é enviado como application/x-www-form-urlencoded (requisição "simples").
     * Não persiste no banco: é um utilitário rápido, igual ao /qr/quick da Home pública.
     */
    /** Tipos aceitos por essa rota — subconjunto do QRGenerator, o que a busca do agente oferece hoje. */
    private const QUICK_QR_TYPES = ['url', 'whatsapp', 'pix', 'vcard'];

    public function quickQr(): void
    {
        header('Access-Control-Allow-Origin: *');

        $user = $this->resolveUser();
        if ($user === null) {
            $this->json(['success' => false, 'error' => 'unauthorized'], 401);
            return;
        }

        Middleware::rateLimit('agent:qr:' . $user['uuid'], 30, 60);

        $type = (string) $this->request->input('type', 'url');
        if (!in_array($type, self::QUICK_QR_TYPES, true)) {
            $this->json(['success' => false, 'error' => 'Tipo de QR inválido.'], 422);
            return;
        }

        // Compatibilidade: chamadas antigas mandam só "url" (sem "type"), continuam funcionando.
        // PIX é um caso especial: o campo da chave PIX se chama "key" no QRGenerator, o que
        // colidiria com o parâmetro "key" usado pra autenticação (a Chave de API) — por isso
        // o cliente manda "pix_key" nessa rota, e a gente remapeia aqui antes de validar.
        if ($type === 'pix') {
            $fields = $this->request->only(['name', 'city', 'amount', 'txid', 'description']);
            $fields['key'] = trim((string) $this->request->input('pix_key', ''));
        } elseif ($type === 'url') {
            $fields = ['url' => trim((string) $this->request->input('url', ''))];
        } else {
            $fields = $this->request->only(QRGenerator::TYPE_FIELDS[$type]);
        }

        $validator = Validator::make($fields, QRGenerator::TYPE_RULES[$type]);
        if ($validator->fails()) {
            $this->json(['success' => false, 'error' => 'Dados inválidos para gerar o QR.', 'details' => $validator->errors()], 422);
            return;
        }

        $generator = new QRGenerator();
        $content = $generator->buildContent($type, $fields);

        $tmpBase = 'agent-' . uuid4();
        $files = $generator->render($content, [
            'size'        => 512,
            'ecc_level'   => 'M',
            'fg_color'    => '#000000',
            'bg_color'    => '#FFFFFF',
            'transparent' => false,
        ], $tmpBase);

        $pngData = @file_get_contents(ROOT . '/' . $files['png']);
        @unlink(ROOT . '/' . $files['png']);
        @unlink(ROOT . '/' . $files['svg']);

        if ($pngData === false) {
            $this->json(['success' => false, 'error' => 'Não foi possível gerar o QR Code.'], 500);
            return;
        }

        $this->json([
            'success'    => true,
            'png_base64' => 'data:image/png;base64,' . base64_encode($pngData),
        ]);
    }

    /**
     * POST /agent/link — encurta uma URL a partir da busca rápida do agente de desktop.
     * Mesmo padrão de autenticação (uid+key) do endpoint acima.
     */
    public function quickLink(): void
    {
        header('Access-Control-Allow-Origin: *');

        $user = $this->resolveUser();
        if ($user === null) {
            $this->json(['success' => false, 'error' => 'unauthorized'], 401);
            return;
        }

        Middleware::rateLimit('agent:link:' . $user['uuid'], 30, 60);

        $destination = trim((string) $this->request->input('destination', ''));
        $validator = Validator::make(['destination' => $destination], ['destination' => 'required|url']);
        if ($validator->fails()) {
            $this->json(['success' => false, 'error' => 'URL inválida.'], 422);
            return;
        }

        $slug = $this->generateUniqueSlug();

        Link::create([
            'uuid'         => uuid4(),
            'user_id'      => $user['id'],
            'slug'         => $slug,
            'destination'  => $destination,
            'title'        => '',
            'wrapper_type' => 'none',
            'active'       => 1,
        ]);

        $this->json([
            'success'   => true,
            'short_url' => url('/r/' . $slug),
        ]);
    }

    /**
     * POST /agent/bookmarks/sync — recebe favoritos lidos localmente pelo agente
     * (arquivo Bookmarks do Chrome/Edge/Brave, monitorado via fs.watch) e importa
     * só os que ainda não existem na conta do usuário. Mesmo padrão de auth uid+key,
     * corpo como application/x-www-form-urlencoded (sem preflight CORS).
     * Parâmetro `items`: JSON string, array de {title, url}.
     */
    public function syncBookmarks(): void
    {
        header('Access-Control-Allow-Origin: *');

        $user = $this->resolveUser();
        if ($user === null) {
            $this->json(['success' => false, 'error' => 'unauthorized'], 401);
            return;
        }

        Middleware::rateLimit('agent:bookmarks-sync:' . $user['uuid'], 20, 60);

        $raw = (string) $this->request->input('items', '');
        $items = json_decode($raw, true);

        if (!is_array($items)) {
            $this->json(['success' => false, 'error' => 'Payload inválido.'], 422);
            return;
        }

        $items = array_slice($items, 0, self::SYNC_MAX_ITEMS);

        $imported = 0;
        $duplicates = 0;
        $position = Bookmark::countForUser((int) $user['id']);

        foreach ($items as $item) {
            $url = trim((string) ($item['url'] ?? ''));
            $title = trim((string) ($item['title'] ?? ''));

            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            if (Bookmark::urlExists((int) $user['id'], $url)) {
                $duplicates++;
                continue;
            }

            Bookmark::create([
                'user_id'  => $user['id'],
                'title'    => mb_substr($title !== '' ? $title : $url, 0, 500),
                'url'      => $url,
                'health'   => 'unknown',
                'position' => $position++,
            ]);
            $imported++;
        }

        $this->json(['success' => true, 'imported' => $imported, 'duplicates' => $duplicates]);
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
