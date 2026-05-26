<div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-3">Conteúdos Disponíveis</h1>
    <div class="list-group">
        <?php foreach (($contents ?? []) as $content): ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(url('/contents/show?id=' . (int)$content['id'])) ?>">
                <span><strong><?= e($content['title']) ?></strong><br><small class="text-muted"><?= e($content['description'] ?? '') ?></small></span>
                <span class="badge text-bg-secondary"><?= e($content['type'] ?? '-') ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div></div>
