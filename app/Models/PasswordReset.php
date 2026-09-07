<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    protected static string $table = 'password_resets';

    public static function findValidToken(string $token): ?array
    {
        return self::row(
            'SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()',
            [$token]
        );
    }

    public static function markUsed(int $id): bool
    {
        return self::update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }

    protected static function timestamps(array $data, bool $update = false): array
    {
        if ($update) {
            return $data;
        }

        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        return $data;
    }
}
