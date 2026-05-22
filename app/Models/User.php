<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    /** @return array|false */
    public function findByLogin(string $login)
    {
        return $this->db->fetch('SELECT u.*, r.is_admin FROM users u JOIN roles r ON r.id = u.role_id WHERE (u.email = :login OR u.username = :login) AND u.status = "active" LIMIT 1', ['login' => $login]);
    }
}
