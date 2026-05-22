<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class DashboardController extends Controller
{
    public function index(): void
    {
        Middleware::auth();
        $this->view('dashboard/index', ['title' => 'Dashboard']);
    }
}
