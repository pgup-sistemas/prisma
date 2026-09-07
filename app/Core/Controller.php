<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected View $view;
    protected Request $request;

    public function __construct()
    {
        $this->view = new View();
        $this->request = new Request();
    }

    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data['csrf_token'] ??= $this->csrfToken();
        $data['auth_user'] ??= $this->user();

        echo $this->view->render($view, $data, $layout);
    }

    protected function redirect(string $url, int $code = 302): never
    {
        header('Location: ' . $url, true, $code);
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect(url('/login'));
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!Auth::hasRole($role)) {
            http_response_code(403);

            $errorView = ROOT . '/app/Views/errors/403.php';
            if (is_file($errorView)) {
                require $errorView;
            } else {
                echo '403 - Acesso negado';
            }

            exit;
        }
    }

    protected function flash(string $type, string $msg): void
    {
        flash($type, $msg);
    }

    protected function user(): ?array
    {
        return Auth::user();
    }

    protected function csrfToken(): string
    {
        return Session::csrf();
    }

    protected function verifyCsrf(): void
    {
        $token = $this->request->input('_csrf');

        if (!Session::verifyCsrf($token)) {
            http_response_code(403);
            echo '403 - Token CSRF inválido.';
            exit;
        }
    }
}
