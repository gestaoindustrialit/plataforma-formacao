<div class="card shadow-sm"><div class="card-body">
    <a href="<?= e(url('/contents')) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3">&larr; Voltar aos conteúdos</a>
    <h1 class="h4"><?= e($content['title'] ?? '') ?></h1>
    <p class="text-muted"><?= e($content['description'] ?? '') ?></p>
    <?php if (($content['type'] ?? '') === 'Vídeo' && !empty($content['video_url'])): ?>
        <?php $videoUrl = (string)$content['video_url']; ?>
        <video controls playsinline preload="metadata" style="width:100%;max-height:420px;border-radius:8px;" controlsList="nodownload">
            <source src="<?= e($videoUrl) ?>" type="<?= e(media_mime_type($videoUrl)) ?>">
            O seu browser não consegue reproduzir este vídeo. Descarregue o ficheiro para o visualizar.
        </video>
        <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
            <a href="<?= e($videoUrl) ?>" class="btn btn-outline-secondary btn-sm" download>Descarregar vídeo</a>
            <small class="text-muted">Se o player ficar em 0:00, envie um MP4 com codec H.264/AAC ou converta o upload no servidor.</small>
        </div>
    <?php elseif (($content['type'] ?? '') === 'PDF' && !empty($content['video_url'])): ?>
        <div class="d-flex gap-2 mb-3"><a href="<?= e($content['video_url']) ?>" target="_blank" class="btn btn-dark">Abrir PDF em nova aba</a></div><iframe src="<?= e($content['video_url']) ?>" style="width:100%;height:70vh;border:1px solid #dee2e6;border-radius:8px;"></iframe>
    <?php else: ?>
        <p class="mb-0">Sem ficheiro associado.</p>
    <?php endif; ?>
</div></div>
