<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $hash = env('ADMIN_PASSWORD_HASH');
        $plain = env('ADMIN_PASSWORD', 'ChangeThisPassword123');
        $valid = $hash ? password_verify($password, $hash) : hash_equals($plain, $password);

        if (hash_equals($adminEmail, $email) && $valid) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['name' => env('ADMIN_NAME', 'Admin'), 'email' => $adminEmail];
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
