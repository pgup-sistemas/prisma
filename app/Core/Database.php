<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $cfg = require ROOT . '/config/database.php';
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
            self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        }

        return self::$instance;
    }
}
