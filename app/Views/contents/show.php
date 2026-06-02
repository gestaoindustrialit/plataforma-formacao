<div class="card shadow-sm"><div class="card-body">
    <a href="<?= e(url('/contents')) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3">&larr; Voltar aos conteúdos</a>
    <h1 class="h4"><?= e($content['title'] ?? '') ?></h1>
    <p class="text-muted"><?= e($content['description'] ?? '') ?></p>
    <?php if (($content['type'] ?? '') === 'Vídeo' && !empty($content['video_url'])): ?>
        <?php
            $videoUrl = (string)$content['video_url'];
            $playerUrl = (string)($mediaUrl ?? $videoUrl);
            $videoDownloadUrl = (string)($downloadUrl ?? $videoUrl);
        ?>
        <div class="ratio ratio-16x9 bg-dark rounded overflow-hidden shadow-sm">
            <video class="w-100 h-100" controls playsinline preload="metadata" controlsList="nodownload">
                <source src="<?= e($playerUrl) ?>" type="<?= e(media_mime_type($videoUrl)) ?>">
                O seu browser não consegue reproduzir este vídeo. Descarregue o ficheiro para o visualizar.
            </video>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
            <a href="<?= e($videoDownloadUrl) ?>" class="btn btn-outline-secondary btn-sm">Descarregar vídeo</a>
            <small class="text-muted">O vídeo é servido pelo endpoint da aplicação com suporte de streaming/range para MP4 e WebM.</small>
        </div>
    <?php elseif (($content['type'] ?? '') === 'PDF' && !empty($content['video_url'])): ?>
        <?php
            $pdfUrl = (string)($mediaUrl ?? $content['video_url']);
            $pdfDownloadUrl = (string)($downloadUrl ?? $content['video_url']);
        ?>
        <div class="d-flex gap-2 mb-3">
            <a href="<?= e($pdfUrl) ?>" target="_blank" class="btn btn-dark">Abrir PDF em nova aba</a>
            <a href="<?= e($pdfDownloadUrl) ?>" class="btn btn-outline-secondary">Descarregar PDF</a>
        </div>
        <iframe src="<?= e($pdfUrl) ?>" style="width:100%;height:70vh;border:1px solid #dee2e6;border-radius:8px;"></iframe>
    <?php else: ?>
        <p class="mb-0">Sem ficheiro associado.</p>
    <?php endif; ?>
</div></div>
