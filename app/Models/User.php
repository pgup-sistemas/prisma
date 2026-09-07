<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', $email);
    }

    public static function emailExists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }

    public static function findByApiKey(string $apiKey): ?array
    {
        return self::findBy('api_key', $apiKey);
    }

    /**
     * Debita créditos atomicamente. Retorna false se saldo insuficiente.
     */
    public static function deductCredit(int $userId, int $amount = 1): bool
    {
        $pdo  = \App\Core\Database::get();
        $stmt = $pdo->prepare(
            'UPDATE users SET credits = credits - ? WHERE id = ? AND credits >= ?'
        );
        $stmt->execute([$amount, $userId, $amount]);

        return $stmt->rowCount() > 0;
    }

    public static function addCredit(int $userId, int $amount): void
    {
        $pdo  = \App\Core\Database::get();
        $stmt = $pdo->prepare('UPDATE users SET credits = credits + ? WHERE id = ?');
        $stmt->execute([$amount, $userId]);
    }

    public static function getCredits(int $userId): int
    {
        $pdo  = \App\Core\Database::get();
        $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)($row['credits'] ?? 0);
    }
}
