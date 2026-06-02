<?php
namespace App\Core;

class Middleware
{
    public static function auth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public static function admin(): void
    {
        self::auth();

        if (!Auth::isAdmin()) {
            header('Location: ' . url('/contents'));
            exit;
        }
    }

    public static function permission(string $permission): void
    {
        $userPermissions = $_SESSION['permissions'] ?? [];
        $isAdmin = Auth::isAdmin();
        if (!$isAdmin && !in_array($permission, $userPermissions, true)) {
            http_response_code(403);
            include __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }
}
