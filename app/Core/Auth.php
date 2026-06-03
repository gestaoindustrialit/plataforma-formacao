<?php
namespace App\Core;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();

        if (!is_array($user)) {
            return false;
        }

        if (isset($user['is_admin']) && (int)$user['is_admin'] === 1) {
            return true;
        }

        foreach (['role', 'role_name', 'profile', 'name', 'username'] as $field) {
            if (self::isAdminLabel((string)($user[$field] ?? ''))) {
                return true;
            }
        }

        return false;
    }

    private static function isAdminLabel(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return in_array($normalized, ['super admin', 'admin', 'administrador'], true);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
