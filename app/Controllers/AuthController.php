<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['error'] = 'Token CSRF inválido.';
            $this->redirect('/login');
        }

        $db = new Database(require __DIR__ . '/../../config/database.php');
        $userModel = new User($db);
        $user = $userModel->findByLogin(trim($_POST['login'] ?? ''));

        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
            $_SESSION['error'] = 'Credenciais inválidas.';
            $this->redirect('/login');
        }

        unset($user['password']);
        Auth::login($user);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
