<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Middleware;
use App\Core\Session;
use App\Core\Settings;
use App\Core\Validator;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\MailerService;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/dashboard'));
        }

        $this->render('auth/login', ['title' => 'Entrar'], 'auth');
    }

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/dashboard'));
        }

        $this->verifyCsrf();

        Middleware::rateLimit('login:' . $this->request->ip(), (int) env('RATE_LIMIT_LOGIN', 5), (int) env('RATE_LIMIT_WINDOW', 900));

        $validator = Validator::make($this->request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->flash('error', 'Informe e-mail e senha válidos.');
            $this->redirect(url('/login'));
        }

        $email = (string) $this->request->input('email');
        $password = (string) $this->request->input('password');

        if (!Auth::attempt($email, $password)) {
            $this->flash('error', 'E-mail ou senha inválidos.');
            $this->redirect(url('/login'));
        }

        if (Settings::get('maintenance_mode') && !Auth::hasRole('admin') && !Auth::hasRole('superadmin')) {
            // Não usa Auth::logout() aqui: ele chama Session::destroy(), que zera $_SESSION
            // por inteiro e apagaria a flash message logo em seguida. Basta remover a chave
            // de login para desfazer o Auth::attempt() anterior.
            Session::remove('user_id');
            $this->flash('error', 'O sistema está em manutenção no momento. Tente novamente mais tarde.');
            $this->redirect(url('/login'));
        }

        $intended = $_SESSION['_intended'] ?? url('/dashboard');
        unset($_SESSION['_intended']);

        $this->redirect($intended);
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/dashboard'));
        }

        if (!Settings::get('allow_registration')) {
            $this->flash('error', 'Novos cadastros estão desabilitados no momento.');
            $this->redirect(url('/login'));
        }

        $this->render('auth/register', ['title' => 'Criar conta'], 'auth');
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/dashboard'));
        }

        if (!Settings::get('allow_registration')) {
            $this->flash('error', 'Novos cadastros estão desabilitados no momento.');
            $this->redirect(url('/login'));
        }

        $this->verifyCsrf();

        $data = $this->request->all();

        $validator = Validator::make($data, [
            'name'     => 'required|min:2|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors() as $fieldErrors) {
                foreach ($fieldErrors as $message) {
                    $this->flash('error', $message);
                }
            }
            $this->redirect(url('/register'));
        }

        $userId = User::create([
            'uuid'     => uuid4(),
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'     => 'user',
            'api_key'  => bin2hex(random_bytes(32)),
            'credits'  => max(0, (int) Settings::get('default_credits', 0)),
        ]);

        $user = User::find($userId);
        MailerService::sendWelcome($user);

        Auth::login($userId);

        $this->redirect(url('/dashboard'));
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(url('/login'));
    }

    public function showForgot(): void
    {
        $this->render('auth/forgot', ['title' => 'Recuperar senha'], 'auth');
    }

    public function sendReset(): void
    {
        $this->verifyCsrf();

        Middleware::rateLimit('forgot:' . $this->request->ip(), 5, 900);

        $email = (string) $this->request->input('email');

        $user = User::findByEmail($email);

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));

            PasswordReset::create([
                'email'      => $email,
                'token'      => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            ]);

            MailerService::sendPasswordReset($email, $token);
        }

        $this->flash('success', 'Se o e-mail existir, você receberá o link de recuperação.');
        $this->redirect(url('/forgot'));
    }

    public function showReset(string $token): void
    {
        $reset = PasswordReset::findValidToken($token);

        if ($reset === null) {
            $this->flash('error', 'Link de recuperação inválido ou expirado.');
            $this->redirect(url('/forgot'));
        }

        $this->render('auth/reset', ['title' => 'Redefinir senha', 'token' => $token], 'auth');
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();

        $token = (string) $this->request->input('token');
        $reset = PasswordReset::findValidToken($token);

        if ($reset === null) {
            $this->flash('error', 'Link de recuperação inválido ou expirado.');
            $this->redirect(url('/forgot'));
        }

        $validator = Validator::make($this->request->all(), [
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            $this->flash('error', 'A senha deve ter no mínimo 8 caracteres e confirmação igual.');
            $this->redirect(url('/reset/' . $token));
        }

        $user = User::findByEmail($reset['email']);

        if ($user === null) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect(url('/forgot'));
        }

        User::update((int) $user['id'], [
            'password' => password_hash((string) $this->request->input('password'), PASSWORD_BCRYPT),
        ]);

        PasswordReset::markUsed((int) $reset['id']);

        $this->flash('success', 'Senha redefinida com sucesso. Faça login.');
        $this->redirect(url('/login'));
    }
}
