<div class="card shadow-sm admin-contents-view"><div class="card-body p-3">
    <style>
        .admin-contents-view { font-size: .9rem; }
        .admin-contents-view h1 { font-size: 1.25rem; }
        .admin-contents-view h2 { font-size: 1.05rem; }
        .admin-contents-view .form-control,
        .admin-contents-view .form-select,
        .admin-contents-view .btn,
        .admin-contents-view .alert,
        .admin-contents-view .table { font-size: .85rem; }
        .admin-contents-view .form-text,
        .admin-contents-view small { font-size: .78rem; }
        .admin-contents-view .table > :not(caption) > * > * { padding: .45rem .5rem; }
        .admin-contents-view .btn-icon { width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
        .admin-contents-view .video-player-shell .video-js { width: 100%; }
    </style>
    <div class="d-flex justify-content-between mb-3 align-items-center gap-2">
        <h1 class="mb-0">Conteúdos de Formação (Vídeo/PDF)</h1>
        <span class="badge text-bg-dark">Player Video.js</span>
    </div>
    <form method="post" action="<?= e(url('/admin/contents/store')) ?>" enctype="multipart/form-data" class="row g-2 mb-3">
        <div class="col-md-3"><input name="title" class="form-control form-control-sm" placeholder="Título do conteúdo" required></div>
        <div class="col-md-3"><input name="description" class="form-control form-control-sm" placeholder="Descrição" required></div>
        <div class="col-md-2"><select name="type" class="form-select form-select-sm"><option>Vídeo</option><option>PDF</option></select></div>
        <div class="col-md-2">
            <select name="department" class="form-select form-select-sm" required>
                <option value="">Departamento</option>
                <?php foreach (($departments ?? []) as $department): ?>
                    <option value="<?= e($department) ?>"><?= e($department) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="training_path" class="form-select form-select-sm" required>
                <option value="">Formação (Know-how)</option>
                <?php foreach (($knowledgePathOptions ?? []) as $path): ?>
                    <option value="<?= e($path) ?>"><?= e($path) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="visible_for" class="form-select form-select-sm" required>
                <option value="">Visível para</option>
                <?php if (!empty($visibleDepartmentOptions ?? [])): ?>
                    <optgroup label="Departamentos">
                        <?php foreach ($visibleDepartmentOptions as $option): ?>
                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php if (!empty($visibleUserOptions ?? [])): ?>
                    <optgroup label="Utilizadores">
                        <?php foreach ($visibleUserOptions as $option): ?>
                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php foreach (($visibleExtraOptions ?? []) as $option): ?>
                    <option value="<?= e($option) ?>"><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="editable_by" class="form-select form-select-sm" required>
                <option value="">Editável por</option>
                <?php if (!empty($editableDepartmentOptions ?? [])): ?>
                    <optgroup label="Departamentos">
                        <?php foreach ($editableDepartmentOptions as $option): ?>
                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php if (!empty($editableUserOptions ?? [])): ?>
                    <optgroup label="Utilizadores">
                        <?php foreach ($editableUserOptions as $option): ?>
                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php foreach (($editableExtraOptions ?? []) as $option): ?>
                    <option value="<?= e($option) ?>"><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5"><input type="file" name="content_file" class="form-control form-control-sm" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v,application/pdf,.pdf"><div class="form-text">Pode carregar um ficheiro diretamente; com FFmpeg ativo, o vídeo será convertido para MP4 compatível. A URL é opcional.</div></div>
        <div class="col-md-4"><input name="video_url" class="form-control form-control-sm" placeholder="URL vídeo (opcional)"></div>
        <div class="col-md-3"><button class="btn btn-dark btn-sm w-100" type="submit"><i class="bi bi-plus-lg me-1"></i>Adicionar Conteúdo</button></div>
    </form>
    <div class="alert alert-secondary py-2 mb-3">Defina permissões por conteúdo: <strong>quem pode ver</strong> e <strong>quem pode editar</strong> (por utilizador ou departamento).</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-3">
            <thead><tr><th>Conteúdo</th><th>Descrição</th><th>Tipo</th><th>Formação</th><th>Visível para</th><th>Editável por</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
                <?php foreach (($contents ?? []) as $content): ?>
                    <tr>
                        <td><?= e($content['title']) ?></td>
                        <td><?= e($content['description'] ?? '') ?></td>
                        <td><?= e($content['type']) ?></td>
                        <td><?= e($content['training_path'] ?? '—') ?></td>
                        <td><?= e($content['visible_for']) ?></td>
                        <td><?= e($content['editable_by']) ?></td>
                        <td class="text-end text-nowrap">
                            <form method="post" action="<?= e(url('/admin/contents/delete')) ?>" class="d-inline-flex gap-1">
                                <input type="hidden" name="id" value="<?= (int)$content['id'] ?>">
                                <a class="btn btn-sm btn-outline-secondary btn-icon" href="<?= e(url('/admin/contents/edit?id=' . (int)$content['id'])) ?>" title="Editar" aria-label="Editar <?= e($content['title']) ?>"><i class="bi bi-pencil-square"></i></a>
                                <button class="btn btn-sm btn-outline-danger btn-icon" type="submit" title="Remover" aria-label="Remover <?= e($content['title']) ?>" onclick="return confirm('Remover este conteúdo?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="border-top pt-3">
        <h2 class="mb-2">Pré-visualização do vídeo</h2>
        <?php $previewContent = null; foreach (($contents ?? []) as $item) { if (($item['type'] ?? '') === 'Vídeo' && !empty($item['video_url'])) { $previewContent = $item; break; }} ?>
        <?php if ($previewContent): ?>
            <?php
                $videoUrl = (string)$previewContent['video_url'];
                $playerUrl = url('/contents/media?id=' . (int)$previewContent['id']);
                $videoDownloadUrl = url('/contents/download?id=' . (int)$previewContent['id']);
                $videoMimeType = media_mime_type($videoUrl) ?: 'video/mp4';
            ?>
            <link href="https://cdn.jsdelivr.net/npm/video.js@8.21.1/dist/video-js.min.css" rel="stylesheet">
            <div class="video-player-shell rounded overflow-hidden shadow-sm bg-dark">
                <video
                    id="admin-content-video-player"
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
            <div id="admin-video-player-error" class="alert alert-warning d-none mt-3 mb-0" role="alert">
                Não foi possível carregar o vídeo no player. Use o botão de download abaixo e confirme que o ficheiro é MP4 H.264/AAC ou WebM VP8/VP9/Opus.
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                <a href="<?= e($videoDownloadUrl) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Descarregar vídeo</a>
                <a href="<?= e($playerUrl) ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i>Abrir vídeo</a>
                <small class="text-muted">Player Video.js com endpoint de streaming autenticado e suporte a Range/HEAD para MP4 e WebM.</small>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/video.js@8.21.1/dist/video.min.js"></script>
            <script>
                (function () {
                    var errorBox = document.getElementById('admin-video-player-error');
                    if (typeof videojs === 'undefined') {
                        if (errorBox) {
                            errorBox.classList.remove('d-none');
                        }
                        return;
                    }

                    var player = videojs('admin-content-video-player');
                    player.on('error', function () {
                        if (errorBox) {
                            errorBox.classList.remove('d-none');
                        }
                    });
                })();
            </script>
        <?php else: ?>
            <p class="text-muted mb-0">Adicione um conteúdo do tipo vídeo com ficheiro carregado para ativar o player. A URL é opcional.</p>
        <?php endif; ?>
    </div>
</div></div>
