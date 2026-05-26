<div class="card shadow-sm"><div class="card-body">
    <div class="d-flex justify-content-between mb-3 align-items-center">
        <h1 class="h4 mb-0">Conteúdos de Formação (Vídeo/PDF)</h1>
        <span class="badge text-bg-dark">Player de vídeo com controlos nativos</span>
    </div>
    <form method="post" action="<?= e(url('/admin/contents/store')) ?>" class="row g-3 mb-3">
        <div class="col-md-3"><input name="title" class="form-control" placeholder="Título do conteúdo" required></div>
        <div class="col-md-2"><select name="type" class="form-select"><option>Vídeo</option><option>PDF</option></select></div>
        <div class="col-md-2"><input name="department" class="form-control" placeholder="Departamento" required></div>
        <div class="col-md-2"><input name="visible_for" class="form-control" placeholder="Visível para" required></div>
        <div class="col-md-3"><input name="editable_by" class="form-control" placeholder="Editável por" required></div>
        <div class="col-md-9"><input name="video_url" class="form-control" placeholder="URL vídeo (opcional)"></div>
        <div class="col-md-3"><button class="btn btn-dark w-100" type="submit">+ Adicionar Conteúdo</button></div>
    </form>
    <div class="alert alert-secondary">Defina permissões por conteúdo: <strong>quem pode ver</strong> e <strong>quem pode editar</strong> (por utilizador, papel ou departamento).</div>
    <table class="table">
        <thead><tr><th>Conteúdo</th><th>Tipo</th><th>Visível para</th><th>Editável por</th><th></th></tr></thead>
        <tbody>
            <?php foreach (($contents ?? []) as $content): ?>
                <tr>
                    <td><?= e($content['title']) ?></td><td><?= e($content['type']) ?></td><td><?= e($content['visible_for']) ?></td><td><?= e($content['editable_by']) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('/admin/contents/delete')) ?>">
                            <input type="hidden" name="id" value="<?= (int)$content['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-4">
        <h2 class="h5">Pré-visualização do vídeo</h2>
        <?php $video = ''; foreach (($contents ?? []) as $item) { if (($item['type'] ?? '') === 'Vídeo' && !empty($item['video_url'])) { $video = $item['video_url']; break; }} ?>
        <?php if ($video): ?>
            <video controls playsinline preload="metadata" style="width:100%;max-height:420px;border-radius:8px;" src="<?= e($video) ?>"></video>
        <?php else: ?>
            <p class="text-muted mb-0">Adicione um conteúdo do tipo vídeo com URL para ativar o player.</p>
        <?php endif; ?>
    </div>
</div></div>
