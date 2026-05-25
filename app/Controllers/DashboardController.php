<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class DashboardController extends Controller
{
    public function index(): void
    {
        Middleware::auth();
        $this->view('dashboard/index', ['title' => 'Centro de Formação']);
    }


    public function profile(): void
    {
        Middleware::auth();
        $this->view('profile/index', ['title' => 'Meu Perfil']);
    }

    public function users(): void
    {
        Middleware::auth();
        $users = [
            ['name' => 'Ana Martins', 'email' => 'ana@empresa.pt', 'role' => 'Formadora', 'department' => 'Produção', 'status' => 'Ativo'],
            ['name' => 'Carlos Silva', 'email' => 'carlos@empresa.pt', 'role' => 'Operador', 'department' => 'Qualidade', 'status' => 'Ativo'],
            ['name' => 'Rita Costa', 'email' => 'rita@empresa.pt', 'role' => 'Gestora RH', 'department' => 'RH', 'status' => 'Pendente'],
        ];

        $this->view('admin/users/index', ['title' => 'Utilizadores', 'users' => $users]);
    }

    public function permissions(): void
    {
        Middleware::auth();
        $this->view('admin/permissions/index', ['title' => 'Permissões por Utilizador']);
    }

    public function contents(): void
    {
        Middleware::auth();
        $this->view('admin/contents/index', ['title' => 'Conteúdos de Formação']);
    }

    public function knowledge(): void
    {
        Middleware::auth();
        $this->view('admin/knowledge/index', ['title' => 'Departamentos e Pastas de Conhecimento']);
    }
}
