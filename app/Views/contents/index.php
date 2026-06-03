<?php
$contentTrainingTree = $contentTrainingTree ?? [];
$contents = $contents ?? [];
$viewedContentIds = array_map('intval', $viewedContentIds ?? []);
$recentContentIds = array_map('intval', $recentContentIds ?? []);

$contentTypeIcon = function (string $type): string {
    return $type === 'PDF' ? 'bi-file-earmark-pdf' : 'bi-play-circle-fill';
};

$contentTypeLabel = function (string $type): string {
    return $type === 'PDF' ? 'Ler PDF' : 'Assistir vídeo';
};

$flattenTrainingSections = function (array $node, array $path = []) use (&$flattenTrainingSections): array {
    $sections = [];

    if (!empty($node['_contents'])) {
        $sections[] = [
            'path' => $path ?: ['Sem formação'],
            'contents' => $node['_contents'],
        ];
    }

    foreach ($node as $key => $child) {
        if ($key === '_contents' || !is_array($child)) {
            continue;
        }

        $sections = array_merge($sections, $flattenTrainingSections($child, array_merge($path, [$key])));
    }

    return $sections;
};

$trainingSections = [];
foreach ($contentTrainingTree as $groupName => $groupNode) {
    $trainingSections = array_merge($trainingSections, $flattenTrainingSections($groupNode, [$groupName]));
}

$totalContents = count($contents);
$videoCount = count(array_filter($contents, fn ($content) => ($content['type'] ?? '') === 'Vídeo'));
$pdfCount = count(array_filter($contents, fn ($content) => ($content['type'] ?? '') === 'PDF'));
$totalTrainingAreas = count($trainingSections);
$completedCount = count(array_filter($contents, fn ($content) => in_array((int)($content['id'] ?? 0), $viewedContentIds, true)));
?>

<style>
    .learning-library {
        --learning-primary: #1f5eff;
        --learning-primary-dark: #123c98;
        --learning-soft: #eef4ff;
        --learning-border: #dce6f7;
        --learning-text: #172033;
        color: var(--learning-text);
    }

    .learning-hero {
        background: linear-gradient(135deg, #111827 0%, #1d4ed8 55%, #4f46e5 100%);
        border-radius: 24px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        padding: 2rem;
        position: relative;
    }

    .learning-hero::after {
        background: radial-gradient(circle at center, rgba(255, 255, 255, 0.2), transparent 65%);
        content: '';
        height: 18rem;
        position: absolute;
        right: -5rem;
        top: -6rem;
        width: 18rem;
    }

    .learning-hero-content {
        position: relative;
        z-index: 1;
    }

    .learning-eyebrow {
        align-items: center;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        color: #e0e7ff;
        display: inline-flex;
        font-size: 0.78rem;
        font-weight: 700;
        gap: 0.4rem;
        letter-spacing: 0.06em;
        padding: 0.38rem 0.75rem;
        text-transform: uppercase;
    }

    .learning-stat-card {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 18px;
        color: #ffffff;
        min-height: 6rem;
        padding: 1rem;
    }

    .learning-stat-card strong {
        display: block;
        font-size: 1.65rem;
        line-height: 1;
    }

    .learning-toolbar,
    .training-section {
        background: #ffffff;
        border: 1px solid var(--learning-border);
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(20, 32, 48, 0.07);
    }

    .learning-toolbar {
        padding: 1rem;
    }

    .learning-filter-chip {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        color: #334155;
        display: inline-flex;
        font-size: 0.86rem;
        font-weight: 700;
        gap: 0.35rem;
        padding: 0.45rem 0.75rem;
    }

    .training-section {
        padding: 1.25rem;
    }

    .training-section-heading {
        align-items: flex-start;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .training-path {
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        font-size: 0.84rem;
        gap: 0.35rem;
        margin-bottom: 0.35rem;
    }

    .training-path span:not(:last-child)::after {
        color: #94a3b8;
        content: '›';
        margin-left: 0.35rem;
    }

    .content-card {
        border: 1px solid #e3eaf5;
        border-radius: 20px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        color: inherit;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .content-card:hover,
    .content-card:focus {
        border-color: #8fb4ff;
        box-shadow: 0 16px 34px rgba(31, 94, 255, 0.15);
        color: inherit;
        transform: translateY(-3px);
    }

    .content-card-top {
        background: linear-gradient(135deg, #f8fbff 0%, #eaf2ff 100%);
        min-height: 8.5rem;
        padding: 1rem;
    }

    .content-card-icon {
        align-items: center;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 8px 18px rgba(31, 94, 255, 0.12);
        color: var(--learning-primary);
        display: inline-flex;
        font-size: 1.65rem;
        height: 3.25rem;
        justify-content: center;
        width: 3.25rem;
    }

    .content-card-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 1rem;
    }

    .content-card-description {
        color: #64748b;
        font-size: 0.9rem;
        min-height: 2.7rem;
    }

    .content-card-meta {
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        font-size: 0.78rem;
        gap: 0.45rem;
        margin-top: auto;
        padding-top: 1rem;
    }

    .content-card-meta span,
    .content-status-badge {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        display: inline-flex;
        gap: 0.3rem;
        padding: 0.32rem 0.55rem;
    }

    .content-status-badge {
        background: #e8fff3;
        border-color: #b8efd0;
        color: #147242;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .content-cta {
        align-items: center;
        color: var(--learning-primary-dark);
        display: inline-flex;
        font-weight: 800;
        gap: 0.4rem;
        margin-top: 1rem;
    }

    .empty-library {
        background: #ffffff;
        border: 1px dashed #b9c7dc;
        border-radius: 22px;
        padding: 2rem;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .learning-hero {
            padding: 1.35rem;
        }

        .training-section-heading {
            display: block;
        }
    }
</style>

<div class="learning-library">
    <section class="learning-hero mb-4 text-white">
        <div class="learning-hero-content">
            <div class="row g-4 align-items-end">
                <div class="col-lg-7">
                    <span class="learning-eyebrow mb-3"><i class="bi bi-mortarboard-fill"></i> App de formação</span>
                    <h1 class="display-6 fw-bold mb-2">Escolha o conteúdo que quer aprender agora</h1>
                    <p class="lead mb-0 text-white-50">Cursos organizados por área, com cartões claros para iniciar vídeos ou abrir documentos sem perder tempo.</p>
                </div>
                <div class="col-lg-5">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="learning-stat-card">
                                <strong><?= (int)$totalContents ?></strong>
                                <span>conteúdos</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="learning-stat-card">
                                <strong><?= (int)$totalTrainingAreas ?></strong>
                                <span>percursos</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="learning-stat-card">
                                <strong><?= (int)$videoCount ?></strong>
                                <span>vídeos</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="learning-stat-card">
                                <strong><?= (int)$completedCount ?></strong>
                                <span>já vistos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="learning-toolbar mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <h2 class="h5 mb-1">Biblioteca disponível</h2>
                <p class="text-muted mb-0">Selecione um cartão para começar. Os conteúdos já visualizados ficam assinalados.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="learning-filter-chip"><i class="bi bi-play-circle"></i><?= (int)$videoCount ?> vídeos</span>
                <span class="learning-filter-chip"><i class="bi bi-file-earmark-pdf"></i><?= (int)$pdfCount ?> PDFs</span>
                <span class="learning-filter-chip"><i class="bi bi-check2-circle"></i><?= (int)$completedCount ?> vistos</span>
            </div>
        </div>
    </div>

    <?php if (empty($trainingSections)): ?>
        <div class="empty-library">
            <i class="bi bi-collection-play fs-1 text-primary"></i>
            <h2 class="h5 mt-3">Ainda não existem conteúdos disponíveis</h2>
            <p class="text-muted mb-0">Quando forem publicados vídeos ou PDFs para o seu perfil, aparecerão aqui em formato de cartão.</p>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-4">
            <?php foreach ($trainingSections as $sectionIndex => $section): ?>
                <?php
                    $path = $section['path'];
                    $sectionContents = $section['contents'];
                    $sectionTitle = end($path) ?: 'Sem formação';
                    $sectionId = 'training-section-' . (int)$sectionIndex;
                ?>
                <section class="training-section" aria-labelledby="<?= e($sectionId) ?>">
                    <div class="training-section-heading">
                        <div>
                            <div class="training-path" aria-label="Percurso de formação">
                                <?php foreach ($path as $pathSegment): ?>
                                    <span><?= e($pathSegment) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <h2 class="h4 mb-0" id="<?= e($sectionId) ?>"><?= e($sectionTitle) ?></h2>
                        </div>
                        <span class="badge rounded-pill text-bg-primary"><?= count($sectionContents) ?> conteúdos</span>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($sectionContents as $content): ?>
                            <?php
                                $contentId = (int)($content['id'] ?? 0);
                                $contentType = (string)($content['type'] ?? '-');
                                $isViewed = in_array($contentId, $viewedContentIds, true);
                                $isRecent = in_array($contentId, $recentContentIds, true);
                            ?>
                            <div class="col-md-6 col-xl-4">
                                <a class="content-card" href="<?= e(url('/contents/show?id=' . $contentId)) ?>" aria-label="Abrir <?= e($content['title'] ?? 'conteúdo') ?>">
                                    <div class="content-card-top d-flex justify-content-between align-items-start gap-2">
                                        <span class="content-card-icon"><i class="bi <?= e($contentTypeIcon($contentType)) ?>"></i></span>
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <span class="badge rounded-pill <?= $contentType === 'PDF' ? 'text-bg-danger' : 'text-bg-primary' ?>"><?= e($contentType) ?></span>
                                            <?php if ($isRecent): ?>
                                                <span class="content-status-badge"><i class="bi bi-clock-history"></i> Recente</span>
                                            <?php elseif ($isViewed): ?>
                                                <span class="content-status-badge"><i class="bi bi-check-circle-fill"></i> Visto</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="content-card-body">
                                        <h3 class="h5 mb-2"><?= e($content['title'] ?? '') ?></h3>
                                        <p class="content-card-description mb-0"><?= e(($content['description'] ?? '') !== '' ? $content['description'] : 'Conteúdo de formação disponível para consulta.') ?></p>
                                        <div class="content-card-meta">
                                            <?php if (!empty($content['department'])): ?>
                                                <span><i class="bi bi-building"></i><?= e($content['department']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($content['visible_for'])): ?>
                                                <span><i class="bi bi-people"></i><?= e($content['visible_for']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="content-cta"><?= e($contentTypeLabel($contentType)) ?> <i class="bi bi-arrow-right"></i></span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
