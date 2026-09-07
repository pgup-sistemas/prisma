<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Settings;
use App\Models\User;

class AdminController extends Controller
{
    public function users(): void
    {
        $page   = max(1, (int) $this->request->input('page', 1));
        $search = trim((string) $this->request->input('q', ''));

        $where  = '1';
        $params = [];
        if ($search !== '') {
            $where = '(name LIKE ? OR email LIKE ?)';
            $like = '%' . $search . '%';
            $params = [$like, $like];
        }

        $result = User::paginate($page, 20, $where, $params);

        $this->render('admin/users', [
            'title'  => 'Usuários',
            'users'  => $result['data'],
            'total'  => $result['total'],
            'pages'  => $result['pages'],
            'page'   => $result['page'],
            'search' => $search,
        ]);
    }

    public function toggleUser(string $id): void
    {
        $this->verifyCsrf();

        $id = (int) $id;
        $target = User::find($id);

        if ($target === null) {
            $this->json(['success' => false, 'error' => 'Usuário não encontrado.'], 404);
            return;
        }

        if ($id === (int) $this->user()['id']) {
            $this->json(['success' => false, 'error' => 'Você não pode desativar sua própria conta.'], 422);
            return;
        }

        $newState = (int) $target['active'] === 1 ? 0 : 1;
        User::update($id, ['active' => $newState]);

        $this->json(['success' => true, 'active' => (bool) $newState]);
    }

    public function settings(): void
    {
        $this->render('admin/settings', [
            'title'    => 'Configurações',
            'settings' => Settings::all(),
        ]);
    }

    public function saveSettings(): void
    {
        $this->verifyCsrf();

        Settings::set([
            'site_name'          => trim((string) $this->request->input('site_name', 'PRISMA')),
            'maintenance_mode'   => (bool) $this->request->input('maintenance_mode', false),
            'allow_registration' => (bool) $this->request->input('allow_registration', false),
            'default_credits'    => max(0, (int) $this->request->input('default_credits', 0)),
        ]);

        $this->flash('success', 'Configurações salvas com sucesso.');
        $this->redirect(url('/admin/settings'));
    }
}
