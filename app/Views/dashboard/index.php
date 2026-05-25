<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Dashboard de Formação</h1>
        <p class="text-muted mb-0">Visão geral para acelerar onboarding e retenção de conhecimento.</p>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><p class="text-muted mb-1">Utilizadores ativos</p><h3 class="mb-0">128</h3></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><p class="text-muted mb-1">Conteúdos publicados</p><h3 class="mb-0">342</h3></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><p class="text-muted mb-1">Taxa conclusão</p><h3 class="mb-0">84%</h3></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><p class="text-muted mb-1">Formações críticas em atraso</p><h3 class="mb-0 text-danger">7</h3></div></div></div>
</div>
<div class="row g-3">
    <div class="col-lg-6"><a href="<?= e(url('/admin/users')) ?>" class="card shadow-sm text-decoration-none text-dark"><div class="card-body"><h2 class="h5">Gestão de Utilizadores</h2><p class="text-muted mb-0">Criar, editar e eliminar contas com perfis por função e departamento.</p></div></a></div>
    <div class="col-lg-6"><a href="<?= e(url('/admin/permissions')) ?>" class="card shadow-sm text-decoration-none text-dark"><div class="card-body"><h2 class="h5">Permissões por Utilizador</h2><p class="text-muted mb-0">Defina acesso de leitura, edição e aprovação por pessoa e equipa.</p></div></a></div>
    <div class="col-lg-6"><a href="<?= e(url('/admin/contents')) ?>" class="card shadow-sm text-decoration-none text-dark"><div class="card-body"><h2 class="h5">Biblioteca de Conteúdos</h2><p class="text-muted mb-0">Carregue vídeos e PDFs e atribua visibilidade por público interno.</p></div></a></div>
    <div class="col-lg-6"><a href="<?= e(url('/admin/knowledge')) ?>" class="card shadow-sm text-decoration-none text-dark"><div class="card-body"><h2 class="h5">Departamentos e Pastas</h2><p class="text-muted mb-0">Estruture trilhas por processos, áreas e blocos de formação.</p></div></a></div>
</div>
