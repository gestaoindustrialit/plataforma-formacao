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

        return $this->db->fetch(
            'SELECT u.*, COALESCE(r.is_admin, 0) AS is_admin
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE (LOWER(TRIM(u.email)) = LOWER(:login) OR LOWER(TRIM(u.username)) = LOWER(:login))
               AND LOWER(TRIM(u.status)) IN ("active", "ativo")
             LIMIT 1',
            ['login' => $login]
        );
    }

    public function updatePasswordHash(int $id, string $passwordHash): bool
    {
        return $this->db->update(
            'users',
            ['password' => $passwordHash, 'updated_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $id]
        ) > 0;
    }
}
