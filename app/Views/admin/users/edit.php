<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Editar Utilizador</h1>
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
        </div>
        <form method="post" action="<?= url('/admin/users/update') ?>" class="row g-3">
            <input type="hidden" name="id" value="<?= (int)($user['id'] ?? 0) ?>">
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" value="<?= e($user['name'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Nova password de acesso</label><input type="password" name="password" class="form-control" value="" placeholder="Preencha apenas se quiser alterar" autocomplete="new-password"><div class="form-text">Deixe em branco para manter a password atual.</div></div>
            <div class="col-md-4"><label class="form-label">Função</label><input name="role" class="form-control" value="<?= e($user['role'] ?? '') ?>" required></div>
            <div class="col-md-4"><label class="form-label">Departamento</label><input name="department" class="form-control" value="<?= e($user['department'] ?? '') ?>" required></div>
            <div class="col-md-4"><label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <?php foreach (['Ativo', 'Pendente', 'Inativo'] as $status): ?>
                        <option <?= (($user['status'] ?? 'Ativo') === $status) ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark"><i class="bi bi-check2-circle"></i> Atualizar</button>
                <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
