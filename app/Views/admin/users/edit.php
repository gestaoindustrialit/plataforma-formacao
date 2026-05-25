<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3">Editar Utilizador</h1>
        <form method="post" action="<?= url('/admin/users/update') ?>" class="row g-3">
            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required></div>
            <div class="col-md-4"><label class="form-label">Função</label><input name="role" class="form-control" value="<?= e($user['role']) ?>" required></div>
            <div class="col-md-4"><label class="form-label">Departamento</label><input name="department" class="form-control" value="<?= e($user['department']) ?>" required></div>
            <div class="col-md-4"><label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <?php foreach (['Ativo', 'Pendente', 'Inativo'] as $status): ?>
                        <option <?= $user['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
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
