<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
$storagePath = $basePath . '/storage';
$dbFile = $storagePath . '/database.sqlite';
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (version_compare(PHP_VERSION, '8.0.0', '<')) $errors[] = 'PHP 8+ é obrigatório.';
    if (!extension_loaded('pdo_sqlite')) $errors[] = 'Extensão PDO SQLite não está ativa.';
    if (!is_writable($storagePath)) $errors[] = 'Pasta /storage sem permissão de escrita.';

    if (!$errors) {
        @touch($dbFile);
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec(file_get_contents($basePath . '/database/schema.sql'));
        $pdo->exec(file_get_contents($basePath . '/database/seeds.sql'));

        $permissions = ['dashboard.view','users.view','users.create','users.edit','users.delete','departments.manage','roles.manage','permissions.manage','videos.view','videos.create','videos.edit','videos.delete','categories.manage','programs.manage','reports.view','settings.manage'];
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO permissions (key, label, description) VALUES (:key, :label, :description)');
        foreach ($permissions as $key) {
            $stmt->execute(['key' => $key, 'label' => $key, 'description' => $key]);
        }

        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT OR IGNORE INTO users (id, name, email, username, password, role_id, status, must_change_password, created_at, updated_at) VALUES (1, :name, :email, :username, :password, 1, "active", 1, datetime("now"), datetime("now"))')
            ->execute(['name' => 'Super Admin', 'email' => 'admin@empresa.local', 'username' => 'admin', 'password' => $password]);

        $messages[] = 'Instalação concluída. Proteja a pasta /install.';
    }
}
?><!doctype html><html lang="pt"><head><meta charset="UTF-8"><title>Instalador</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><div class="container py-5"><div class="card"><div class="card-body"><h1 class="h4">Instalador - Centro de Formação Operacional</h1><?php foreach($errors as $error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endforeach; ?><?php foreach($messages as $message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endforeach; ?><form method="post"><button class="btn btn-primary">Instalar agora</button></form></div></div></div></body></html>
