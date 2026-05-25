<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/profile', [DashboardController::class, 'profile']);
$router->get('/admin/users', [DashboardController::class, 'users']);
$router->get('/admin/permissions', [DashboardController::class, 'permissions']);
$router->get('/admin/contents', [DashboardController::class, 'contents']);
$router->get('/admin/knowledge', [DashboardController::class, 'knowledge']);

$router->get('/', function () { header('Location: ' . url('/login')); });
