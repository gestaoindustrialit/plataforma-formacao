<?php
use App\Core\Auth;

$authUser = Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$mainLinks = [
    ['label' => 'Dashboard', 'path' => '/dashboard'],
    ['label' => 'Utilizadores', 'path' => '/admin/users'],
    ['label' => 'Permissões', 'path' => '/admin/permissions'],
    ['label' => 'Conteúdos', 'path' => '/admin/contents'],
    ['label' => 'Ver Conteúdos', 'path' => '/contents'],
    ['label' => 'Know-how', 'path' => '/admin/knowledge'],
];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(url('/dashboard')) ?>">Centro de Formação Operacional</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-lg-4 me-auto mb-2 mb-lg-0 gap-lg-1">
                <?php foreach ($mainLinks as $link): ?>
                    <?php $isActive = $currentPath === $link['path']; ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $isActive ? ' active fw-semibold' : '' ?>" <?= $isActive ? 'aria-current="page"' : '' ?> href="<?= e(url($link['path'])) ?>">
                            <?= e($link['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($authUser): ?>
                <div class="d-flex align-items-lg-center gap-2 flex-column flex-lg-row">
                    <a class="btn btn-outline-light btn-sm" href="<?= e(url('/profile')) ?>">Perfil</a>
                    <span class="text-white-50 small d-none d-lg-inline"><?= e($authUser['name'] ?? $authUser['username'] ?? 'Utilizador') ?></span>
                    <a class="btn btn-light btn-sm" href="<?= e(url('/logout')) ?>">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
