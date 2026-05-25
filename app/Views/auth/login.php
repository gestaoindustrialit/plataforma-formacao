<div class="row justify-content-center align-items-center" style="min-height: 82vh;">
    <div class="col-lg-9">
        <div class="card shadow-lg overflow-hidden">
            <div class="row g-0">
                <div class="col-md-6 bg-dark text-white p-5 d-flex flex-column justify-content-center">
                    <h1 class="h3 fw-bold">Formação interna com foco em retenção de know-how</h1>
                    <p class="mb-4 text-white-50">Centralize SOPs, vídeos e conteúdos críticos por departamento para reduzir tempo de onboarding e evitar perda de conhecimento tácito.</p>
                    <ul class="small">
                        <li class="mb-2">Trilhas de formação por função</li>
                        <li class="mb-2">Permissões granulares por utilizador</li>
                        <li>Biblioteca de conhecimento versionada</li>
                    </ul>
                </div>
                <div class="col-md-6 p-5">
                    <h2 class="h4 fw-semibold mb-4">Entrar na plataforma</h2>
                    <form method="post" action="<?= e(url('/login')) ?>">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                        <div class="mb-3">
                            <label class="form-label">Email ou Username</label>
                            <input class="form-control form-control-lg" name="login" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input class="form-control form-control-lg" type="password" name="password" required>
                        </div>
                        <button class="btn btn-dark btn-lg w-100">Aceder ao Centro de Formação</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
