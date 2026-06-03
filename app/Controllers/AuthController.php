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
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

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

        $password = $_POST['password'] ?? '';

        if (!$user || !$this->passwordMatches($password, (string)($user['password'] ?? ''), $userModel, (int)($user['id'] ?? 0))) {
            $_SESSION['error'] = 'Credenciais inválidas.';
            $this->redirect('/login');
        }

        unset($user['password']);
        Auth::login($user);
        $this->redirect(((int)($user['is_admin'] ?? 0) === 1) ? '/dashboard' : '/contents');
    }

    private function passwordMatches(string $password, string $storedPassword, User $userModel, int $userId): bool
    {
        if ($storedPassword === '') {
            return false;
        }

        if (password_verify($password, $storedPassword)) {
            if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                $userModel->updatePasswordHash($userId, password_hash($password, PASSWORD_DEFAULT));
            }

            return true;
        }

        if (password_get_info($storedPassword)['algoName'] === 'unknown' && hash_equals($storedPassword, $password)) {
            $userModel->updatePasswordHash($userId, password_hash($password, PASSWORD_DEFAULT));
            return true;
        }

        return false;
    }

    private function passwordMatches(string $password, string $storedPassword, User $userModel, int $userId): bool
    {
        if ($storedPassword === '') {
            return false;
        }

        if (password_verify($password, $storedPassword)) {
            if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                $userModel->updatePasswordHash($userId, password_hash($password, PASSWORD_DEFAULT));
            }

            return true;
        }

        if (password_get_info($storedPassword)['algoName'] === 'unknown' && hash_equals($storedPassword, $password)) {
            $userModel->updatePasswordHash($userId, password_hash($password, PASSWORD_DEFAULT));
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
