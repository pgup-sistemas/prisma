<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    private static ?array $userCache = null;
    private static bool $resolved = false;

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$userCache;
        }

        self::$resolved = true;

        $id = Session::get('user_id');

        if ($id === null) {
            return self::$userCache = null;
        }

        $user = User::find((int) $id);

        if ($user === null || (int) $user['active'] !== 1) {
            return self::$userCache = null;
        }

        return self::$userCache = $user;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user ? (int) $user['id'] : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findBy('email', $email);

        if ($user === null || (int) $user['active'] !== 1) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        self::login((int) $user['id']);

        return true;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set('user_id', $userId);
        self::$resolved = false;
        self::$userCache = null;

        User::update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$resolved = false;
        self::$userCache = null;
    }

    public static function hasRole(string $role): bool
    {
        $user = self::user();

        if ($user === null) {
            return false;
        }

        if ($user['role'] === 'superadmin') {
            return true;
        }

        return $user['role'] === $role;
    }

    public static function regenerate(): void
    {
        Session::regenerate();
    }
}
