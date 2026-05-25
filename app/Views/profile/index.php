<?php
$user = \App\Core\Auth::user() ?? [];
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3">Meu Perfil</h1>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted">Nome</label>
                <div class="form-control bg-light"><?= e($user['name'] ?? '-') ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Email</label>
                <div class="form-control bg-light"><?= e($user['email'] ?? '-') ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Username</label>
                <div class="form-control bg-light"><?= e($user['username'] ?? '-') ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Perfil</label>
                <div class="form-control bg-light"><?= e($user['role_name'] ?? 'Utilizador') ?></div>
            </div>
        </div>
    </div>
</div>
