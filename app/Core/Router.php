<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $path, string|\Closure $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string|\Closure $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, string|\Closure $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, string|\Closure $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function group(array $attributes, \Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, string|\Closure $handler): void
    {
        $prefix = '';
        $middleware = [];

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'] ?? '';
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array) $group['middleware']);
            }
        }

        $fullPath = rtrim($prefix . $path, '/');
        $fullPath = $fullPath === '' ? '/' : $fullPath;

        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'pattern'    => $this->toRegex($fullPath),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    private function toRegex(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);

        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = rtrim($uri, '/');
        $uri = $uri === '' ? '/' : $uri;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                fn (string|int $key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            foreach ($route['middleware'] as $middleware) {
                Middleware::handle($middleware);
            }

            $this->invoke($route['handler'], $params);

            return;
        }

        $this->notFound();
    }

    private function invoke(string|\Closure $handler, array $params): void
    {
        if ($handler instanceof \Closure) {
            $handler(...array_values($params));

            return;
        }

        [$controllerName, $action] = explode('@', $handler);
        $class = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($class)) {
            $this->serverError("Controller não encontrado: {$class}");

            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            $this->serverError("Ação não encontrada: {$class}@{$action}");

            return;
        }

        $controller->$action(...array_values($params));
    }

    private function notFound(): void
    {
        http_response_code(404);

        $view = ROOT . '/app/Views/errors/404.php';

        if (is_file($view)) {
            require $view;
        } else {
            echo '404 - Página não encontrada';
        }
    }

    private function serverError(string $message): void
    {
        http_response_code(500);

        if (filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
            echo '<pre>' . e($message) . '</pre>';

            return;
        }

        $view = ROOT . '/app/Views/errors/500.php';

        if (is_file($view)) {
            require $view;
        } else {
            echo '500 - Erro interno';
        }
    }
}
