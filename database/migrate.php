<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/app/Core/helpers.php';

Dotenv\Dotenv::createImmutable(ROOT)->safeLoad();

$cfg = require ROOT . '/config/database.php';

$dsn = "mysql:host={$cfg['host']};port={$cfg['port']};charset={$cfg['charset']}";

try {
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$cfg['database']}`");
} catch (PDOException $e) {
    fwrite(STDERR, "Falha ao conectar no MySQL: {$e->getMessage()}" . PHP_EOL);
    exit(1);
}

$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

if (empty($files)) {
    fwrite(STDOUT, "Nenhuma migration encontrada em {$migrationsDir}" . PHP_EOL);
    exit(0);
}

foreach ($files as $file) {
    $name = basename($file);
    $sql = file_get_contents($file);

    try {
        $pdo->exec($sql);
        fwrite(STDOUT, "[OK] {$name}" . PHP_EOL);
    } catch (PDOException $e) {
        fwrite(STDERR, "[FALHOU] {$name}: {$e->getMessage()}" . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, PHP_EOL . "Migrations concluídas (" . count($files) . " arquivos)." . PHP_EOL);
