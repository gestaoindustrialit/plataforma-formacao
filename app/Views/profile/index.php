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

        <hr>
        <h2 class="h5 mb-3">Histórico de conteúdos vistos</h2>
        <?php if (!empty($contentHistory ?? [])): ?>
            <ul class="list-group">
                <?php foreach ($contentHistory as $entry): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong><?= e($entry['title'] ?? '') ?></strong> <span class="text-muted">(<?= e($entry['type'] ?? '-') ?>)</span></span>
                        <small class="text-muted"><?= e($entry['viewed_at'] ?? '') ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted mb-0">Ainda não visualizou conteúdos.</p>
        <?php endif; ?>

</div>
    </div>
</div>
