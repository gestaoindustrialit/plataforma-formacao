<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    /** @return array|false */
    public function findByLogin(string $login)
    {
        $login = trim($login);

        if ($login === '') {
            return false;
        }

        $roleAdminSelect = $this->rolesTableHasColumn('is_admin') ? 'COALESCE(r.is_admin, 0)' : '0';
        $user = $this->db->fetch(
            'SELECT u.*, r.name AS role_name, ' . $roleAdminSelect . ' AS role_is_admin
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE (LOWER(TRIM(u.email)) = LOWER(:login) OR LOWER(TRIM(u.username)) = LOWER(:login))
               AND LOWER(TRIM(u.status)) IN ("active", "ativo")
             LIMIT 1',
            ['login' => $login]
        );

        if (!$user) {
            return false;
        }

        $user['is_admin'] = $this->resolveAdminFlag($user);

        return $user;
    }

    public function updatePasswordHash(int $id, string $passwordHash): bool
    {
        try {
            return $this->db->update(
                'users',
                ['password' => $passwordHash, 'updated_at' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $id]
            ) > 0;
        } catch (\Throwable $exception) {
            try {
                return $this->db->update(
                    'users',
                    ['password' => $passwordHash],
                    'id = :id',
                    ['id' => $id]
                ) > 0;
            } catch (\Throwable $fallbackException) {
                return false;
            }
        }
    }

    private function rolesTableHasColumn(string $column): bool
    {
        try {
            foreach ($this->db->fetchAll('PRAGMA table_info(roles)') as $field) {
                if (strcasecmp((string)($field['name'] ?? ''), $column) === 0) {
                    return true;
                }
            }
        } catch (\Throwable $exception) {
            return false;
        }

        return false;
    }

    private function resolveAdminFlag(array $user): int
    {
        if ((int)($user['role_is_admin'] ?? 0) === 1 || (int)($user['is_admin'] ?? 0) === 1) {
            return 1;
        }

        foreach (['role_name', 'role', 'profile', 'name', 'username'] as $field) {
            if ($this->isAdminLabel((string)($user[$field] ?? ''))) {
                return 1;
            }
        }

        return 0;
    }

    private function isAdminLabel(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return in_array($normalized, ['super admin', 'admin', 'administrador'], true);
    }
}
