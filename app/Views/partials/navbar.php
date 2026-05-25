<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(url('/dashboard')) ?>">Centro de Formação Operacional</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/dashboard')) ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/users')) ?>">Utilizadores</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/permissions')) ?>">Permissões</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/contents')) ?>">Conteúdos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/admin/knowledge')) ?>">Know-how</a></li>
                <li class="nav-item"><a class="btn btn-outline-light btn-sm mt-1 mt-lg-0" href="<?= e(url('/logout')) ?>">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>
