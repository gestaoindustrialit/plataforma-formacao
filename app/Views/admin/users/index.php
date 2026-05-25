<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Gestão de Utilizadores</h1>
            <button class="btn btn-dark">+ Novo Utilizador</button>
        </div>
        <table class="table align-middle">
            <thead><tr><th>Nome</th><th>Email</th><th>Função</th><th>Departamento</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach (($users ?? []) as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['department']) ?></td><td><?= e($u['status']) ?></td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-secondary">Editar</button> <button class="btn btn-sm btn-outline-danger">Eliminar</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
