<div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-3">Gestão de Permissões por Utilizador</h1>
    <p class="text-muted">Modelo inspirado em plataformas de formação corporativa: mínima permissão por defeito + acesso por responsabilidade.</p>
    <form method="post" action="<?= e(url('/admin/permissions/save')) ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Utilizador</label>
                <select name="user" class="form-select">
                    <?php foreach (($users ?? []) as $user): ?>
                        <option value="<?= e($user['name'] ?? '') ?>" <?= ($permissions['user'] ?? '') === ($user['name'] ?? '') ? 'selected' : '' ?>>
                            <?= e(($user['name'] ?? '') . ' · ' . ($user['department'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Perfil Base</label>
                <input name="profile" class="form-control" value="<?= e($permissions['profile'] ?? '') ?>" list="profiles-list" placeholder="Ex: Formador">
                <datalist id="profiles-list">
                    <?php foreach (($profiles ?? []) as $profile): ?>
                        <option value="<?= e($profile) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-4">
                <label class="form-label">Escopo</label>
                <input name="scope" class="form-control" value="<?= e($permissions['scope'] ?? '') ?>" list="scopes-list" placeholder="Ex: Produção">
                <datalist id="scopes-list">
                    <?php foreach (($scopes ?? []) as $scope): ?>
                        <option value="<?= e($scope) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>
        <div class="alert alert-secondary small mt-3 mb-0" role="alert">
            Os dados de utilizador, perfil base e escopo são carregados automaticamente a partir das entidades já existentes.
            Pode também introduzir novos perfis/escopos para os adicionar à base de seleção.
        </div>
        <hr>
        <div class="form-check mb-2"><input name="can_view" class="form-check-input" type="checkbox" <?= !empty($permissions['can_view']) ? 'checked' : '' ?>><label class="form-check-label">Visualizar conteúdos</label></div>
        <div class="form-check mb-2"><input name="can_edit" class="form-check-input" type="checkbox" <?= !empty($permissions['can_edit']) ? 'checked' : '' ?>><label class="form-check-label">Editar conteúdos da equipa</label></div>
        <div class="form-check mb-3"><input name="can_approve" class="form-check-input" type="checkbox" <?= !empty($permissions['can_approve']) ? 'checked' : '' ?>><label class="form-check-label">Aprovar publicação e versionamento</label></div>
        <button class="btn btn-dark" type="submit">Guardar Permissões</button>
    </form>
</div></div>
