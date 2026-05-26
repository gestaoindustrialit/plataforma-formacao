<div class="card shadow-sm"><div class="card-body">
    <a href="<?= e(url('/contents')) ?>" class="btn btn-link px-0">&larr; Voltar</a>
    <h1 class="h4"><?= e($content['title'] ?? '') ?></h1>
    <p class="text-muted"><?= e($content['description'] ?? '') ?></p>
    <?php if (($content['type'] ?? '') === 'Vídeo' && !empty($content['video_url'])): ?>
        <video controls playsinline preload="metadata" style="width:100%;max-height:420px;border-radius:8px;" src="<?= e($content['video_url']) ?>"></video>
    <?php elseif (($content['type'] ?? '') === 'PDF' && !empty($content['video_url'])): ?>
        <a href="<?= e($content['video_url']) ?>" target="_blank" class="btn btn-dark">Abrir PDF</a>
    <?php else: ?>
        <p class="mb-0">Sem ficheiro associado.</p>
    <?php endif; ?>
</div></div>
