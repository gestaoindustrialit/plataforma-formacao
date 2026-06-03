<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/profile', [DashboardController::class, 'profile']);
$router->get('/admin/users', [DashboardController::class, 'users']);
$router->get('/admin/users/create', [DashboardController::class, 'createUserForm']);
$router->post('/admin/users', [DashboardController::class, 'storeUser']);
$router->get('/admin/users/edit', [DashboardController::class, 'editUserForm']);
$router->post('/admin/users/update', [DashboardController::class, 'updateUser']);
$router->post('/admin/users/delete', [DashboardController::class, 'deleteUser']);
$router->get('/admin/permissions', [DashboardController::class, 'permissions']);
$router->post('/admin/permissions/save', [DashboardController::class, 'savePermissions']);
$router->get('/admin/contents', [DashboardController::class, 'contents']);
$router->post('/admin/contents/store', [DashboardController::class, 'storeContent']);
$router->get('/admin/contents/edit', [DashboardController::class, 'editContent']);
$router->post('/admin/contents/update', [DashboardController::class, 'updateContent']);
$router->post('/admin/contents/delete', [DashboardController::class, 'deleteContent']);
$router->get('/contents', [DashboardController::class, 'listContents']);
$router->get('/contents/show', [DashboardController::class, 'showContent']);
$router->post('/contents/complete', [DashboardController::class, 'completeContent']);
$router->get('/contents/media', [DashboardController::class, 'streamContentMedia']);
$router->get('/contents/download', [DashboardController::class, 'downloadContent']);
$router->head('/contents/media', [DashboardController::class, 'streamContentMedia']);
$router->head('/contents/download', [DashboardController::class, 'downloadContent']);
$router->get('/admin/knowledge', [DashboardController::class, 'knowledge']);
$router->post('/admin/knowledge/store', [DashboardController::class, 'storeKnowledgeNode']);

$router->get('/', function () { header('Location: ' . url('/login')); });
