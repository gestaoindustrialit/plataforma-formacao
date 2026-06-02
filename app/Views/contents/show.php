<div class="card shadow-sm"><div class="card-body">
    <a href="<?= e(url('/contents')) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3">&larr; Voltar aos conteúdos</a>
    <h1 class="h4"><?= e($content['title'] ?? '') ?></h1>
    <p class="text-muted"><?= e($content['description'] ?? '') ?></p>
    <?php if (($content['type'] ?? '') === 'Vídeo' && !empty($content['video_url'])): ?>
        <?php
            $videoUrl = (string)$content['video_url'];
            $playerUrl = (string)($mediaUrl ?? $videoUrl);
            $videoDownloadUrl = (string)($downloadUrl ?? $videoUrl);
            $videoMimeType = media_mime_type($videoUrl) ?: 'video/mp4';
        ?>
        <link href="https://cdn.jsdelivr.net/npm/video.js@8.21.1/dist/video-js.min.css" rel="stylesheet">
        <div class="video-player-shell rounded overflow-hidden shadow-sm bg-dark">
            <video
                id="content-video-player"
                class="video-js vjs-big-play-centered vjs-fluid"
                controls
                playsinline
                preload="auto"
                data-setup='{"fluid":true,"responsive":true,"playbackRates":[0.5,1,1.25,1.5,2]}'
            >
                <source src="<?= e($playerUrl) ?>" type="<?= e($videoMimeType) ?>">
                <p class="vjs-no-js">Ative JavaScript para usar o player ou descarregue o vídeo.</p>
            </video>
        </div>
        <div id="video-player-error" class="alert alert-warning d-none mt-3 mb-0" role="alert">
            Não foi possível carregar o vídeo no player. Use o botão de download abaixo e confirme que o ficheiro é MP4 H.264/AAC ou WebM VP8/VP9/Opus.
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
            <a href="<?= e($videoDownloadUrl) ?>" class="btn btn-outline-secondary btn-sm">Descarregar vídeo</a>
            <a href="<?= e($playerUrl) ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm">Abrir vídeo em nova aba</a>
            <small class="text-muted">Player Video.js com endpoint de streaming autenticado e suporte a Range/HEAD para MP4 e WebM.</small>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/video.js@8.21.1/dist/video.min.js"></script>
        <script>
            (function () {
                var errorBox = document.getElementById('video-player-error');
                if (typeof videojs === 'undefined') {
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                    }
                    return;
                }

                var player = videojs('content-video-player');
                player.on('error', function () {
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                    }
                });
            })();
        </script>
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
