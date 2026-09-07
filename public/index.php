<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(ROOT)->safeLoad();

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=()');

if (filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Sessão segura
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => env('APP_ENV') === 'production',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Roteador
$router = new App\Core\Router();
$routes = require ROOT . '/config/routes.php';
$routes($router);

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath));
}

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $requestPath === '' ? '/' : $requestPath
);
