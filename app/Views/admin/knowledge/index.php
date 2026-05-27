<div class="card shadow-sm"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Departamentos / Pastas de Conhecimento / Blocos de Formação</h1>
    </div>
    <form method="post" action="<?= e(url('/admin/knowledge/store')) ?>" class="row g-2 mb-3">
        <div class="col-md-9"><input name="path" class="form-control" placeholder="Ex: Software > Solune > RH" required></div>
        <div class="col-md-3"><button class="btn btn-dark w-100" type="submit">+ Criar Estrutura</button></div>
    </form>
    <p class="text-muted">Estrutura recomendada para retenção de know-how: Departamento → Assunto/Pasta → Formação (1+ vídeos) → Conteúdo + Quiz + checklist prática.</p>
    <div class="row g-3">
        <div class="col-12">
            <div class="border rounded p-3">
                <h2 class="h6">Estruturas criadas</h2>
                <ul class="mb-0">
                    <?php foreach (($knowledgeNodes ?? []) as $node): ?>
                        <li><?= e($node['path']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-12">
            <div class="border rounded p-3">
                <h2 class="h6">Estrutura em pastas/formações</h2>
                <?php
                $renderTree = function (array $tree) use (&$renderTree): void {
                    if (empty($tree)) {
                        return;
                    }
                    echo '<ul class="mb-0">';
                    foreach ($tree as $name => $children) {
                        echo '<li>' . e((string)$name);
                        if (!empty($children)) {
                            $renderTree($children);
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                };
                ?>
                <?php if (!empty($knowledgeTree ?? [])): ?>
                    <?php $renderTree($knowledgeTree); ?>
                <?php else: ?>
                    <p class="text-muted mb-0">Sem estruturas ainda.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 bg-light">
                <h2 class="h6">Produção</h2>
                <ul class="mb-0">
                    <li>Setup de Linha
                        <ul><li>Bloco: Arranque de turno</li><li>Bloco: Resolução de falhas comuns</li></ul>
                    </li>
                    <li>Qualidade em processo</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 bg-light">
                <h2 class="h6">RH & Onboarding</h2>
                <ul class="mb-0">
                    <li>Integração de novos colaboradores</li>
                    <li>Políticas internas (PDF assináveis)</li>
                    <li>Trilha por função (30-60-90 dias)</li>
                </ul>
            </div>
        </div>
    </div>
</div></div>
