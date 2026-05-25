<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Gestão de Utilizadores</h1>
            <a class="btn btn-dark" href="<?= url('/admin/users/create') ?>"><i class="bi bi-person-plus"></i> Novo Utilizador</a>
        </div>
        <table class="table align-middle">
            <thead><tr><th>Nome</th><th>Email</th><th>Função</th><th>Departamento</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach (($users ?? []) as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['department']) ?></td><td><?= e($u['status']) ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/users/edit?id=' . (int)$u['id']) ?>" title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form method="post" action="<?= url('/admin/users/delete') ?>" class="d-inline">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Eliminar" onclick="return confirm('Eliminar este utilizador?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
