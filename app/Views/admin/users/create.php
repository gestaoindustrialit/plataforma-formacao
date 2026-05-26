<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3">Novo Utilizador</h1>
        <form method="post" action="<?= url('/admin/users') ?>" class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Password de acesso</label><input type="text" name="password" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Função</label><input name="role" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Departamento</label><input name="department" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Estado</label><select name="status" class="form-select"><option>Ativo</option><option>Pendente</option><option>Inativo</option></select></div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark"><i class="bi bi-check2-circle"></i> Guardar</button>
                <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
