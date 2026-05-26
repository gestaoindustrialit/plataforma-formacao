<div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-3">Editar Conteúdo</h1>
    <form method="post" action="<?= e(url('/admin/contents/update')) ?>" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="id" value="<?= (int)$content['id'] ?>">
        <div class="col-md-3"><input name="title" class="form-control" value="<?= e($content['title'] ?? '') ?>" required></div>
        <div class="col-md-3"><input name="description" class="form-control" value="<?= e($content['description'] ?? '') ?>" required></div>
        <div class="col-md-2"><select name="type" class="form-select"><option<?= ($content['type'] ?? '')==='Vídeo'?' selected':'' ?>>Vídeo</option><option<?= ($content['type'] ?? '')==='PDF'?' selected':'' ?>>PDF</option></select></div>
        <div class="col-md-2"><select name="department" class="form-select" required><?php foreach (($departments ?? []) as $department): ?><option value="<?= e($department) ?>"<?= ($content['department'] ?? '')===$department?' selected':'' ?>><?= e($department) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select name="visible_for" class="form-select" required><?php foreach (array_merge($visibleDepartmentOptions ?? [], $visibleUserOptions ?? [], $visibleRoleOptions ?? [], $visibleExtraOptions ?? []) as $option): ?><option value="<?= e($option) ?>"<?= ($content['visible_for'] ?? '')===$option?' selected':'' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select name="editable_by" class="form-select" required><?php foreach (array_merge($editableDepartmentOptions ?? [], $editableUserOptions ?? [], $editableRoleOptions ?? [], $editableExtraOptions ?? []) as $option): ?><option value="<?= e($option) ?>"<?= ($content['editable_by'] ?? '')===$option?' selected':'' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-5"><input type="file" name="content_file" class="form-control" accept="video/mp4,video/webm,video/quicktime,.m4v,application/pdf,.pdf"></div>
        <div class="col-md-4"><input name="video_url" class="form-control" value="<?= e($content['video_url'] ?? '') ?>" placeholder="URL vídeo"></div>
        <div class="col-md-3"><button class="btn btn-dark w-100" type="submit">Guardar Alterações</button></div>
    </form>
</div></div>
