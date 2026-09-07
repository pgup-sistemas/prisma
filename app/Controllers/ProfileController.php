<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = User::find($this->user()['id']);
        $this->render('profile/index', ['title' => 'Meu Perfil', 'profile' => $user]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $name  = trim($this->request->input('name', ''));
        $email = trim($this->request->input('email', ''));

        $errors = [];
        if (strlen($name) < 2)  $errors[] = 'Nome deve ter ao menos 2 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';

        $uid = $this->user()['id'];

        if ($email !== $this->user()['email']) {
            $existing = User::findBy('email', $email);
            if ($existing && (int)$existing['id'] !== $uid) {
                $errors[] = 'Este e-mail já está em uso.';
            }
        }

        if ($errors) {
            $this->flash('danger', implode(' ', $errors));
            $this->redirect(url('/profile'));
            return;
        }

        $data = ['name' => $name, 'email' => $email];

        $password = $this->request->input('password', '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                $this->flash('danger', 'A senha deve ter ao menos 8 caracteres.');
                $this->redirect(url('/profile'));
                return;
            }
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        User::update($uid, $data);
        $_SESSION['user'] = array_merge($_SESSION['user'] ?? [], ['name' => $name, 'email' => $email]);

        $this->flash('success', 'Perfil atualizado com sucesso.');
        $this->redirect(url('/profile'));
    }

    public function regenerateApiKey(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $key = bin2hex(random_bytes(32));
        User::update($this->user()['id'], ['api_key' => $key]);

        $this->flash('success', 'Chave de API gerada: ' . $key);
        $this->redirect(url('/profile'));
    }
}
