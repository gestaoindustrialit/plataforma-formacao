<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/', function () { header('Location: ' . url('/login')); });
