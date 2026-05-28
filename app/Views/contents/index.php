<?php
$contentTrainingTree = $contentTrainingTree ?? [];

$countContents = function (array $node) use (&$countContents): int {
    $total = count($node['_contents'] ?? []);

    foreach ($node as $key => $child) {
        if ($key === '_contents' || !is_array($child)) {
            continue;
        }

        $total += $countContents($child);
    }

    return $total;
};

$renderContentLink = function (array $content): void {
    ?>
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-3 content-item" href="<?= e(url('/contents/show?id=' . (int)$content['id'])) ?>">
        <span>
            <strong><?= e($content['title']) ?></strong><br>
            <small class="text-muted"><?= e($content['description'] ?? '') ?></small>
        </span>
        <span class="badge text-bg-secondary rounded-pill"><?= e($content['type'] ?? '-') ?></span>
    </a>
    <?php
};

$renderTrainingGroup = function (array $node, string $name, string $id, int $level = 0) use (&$renderTrainingGroup, $countContents, $renderContentLink): void {
    $childGroups = array_filter(array_keys($node), fn ($key) => $key !== '_contents');
    $contents = $node['_contents'] ?? [];
    $contentCount = $countContents($node);
    $headingId = 'heading-' . $id;
    $collapseId = 'collapse-' . $id;
    $isOpen = $level < 2;
    ?>
    <div class="accordion-item training-node training-node-level-<?= (int)$level ?>">
        <h2 class="accordion-header" id="<?= e($headingId) ?>">
            <button class="accordion-button training-toggle training-toggle-level-<?= (int)$level ?><?= $isOpen ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($collapseId) ?>" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>" aria-controls="<?= e($collapseId) ?>">
                <span class="fw-semibold"><?= e($name) ?></span>
                <span class="badge text-bg-primary rounded-pill ms-2"><?= (int)$contentCount ?></span>
            </button>
        </h2>
        <div id="<?= e($collapseId) ?>" class="accordion-collapse collapse<?= $isOpen ? ' show' : '' ?>" aria-labelledby="<?= e($headingId) ?>">
            <div class="accordion-body training-node-body training-node-body-level-<?= (int)$level ?>">
                <?php if (!empty($contents)): ?>
                    <div class="list-group content-items mb-3">
                        <?php foreach ($contents as $content): ?>
                            <?php $renderContentLink($content); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($childGroups)): ?>
                    <div class="accordion accordion-flush training-children">
                        <?php foreach ($childGroups as $index => $childName): ?>
                            <?php $renderTrainingGroup($node[$childName], $childName, $id . '-' . $index, $level + 1); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
};
?>

<style>
    .content-library-card {
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(20, 32, 48, 0.08) !important;
    }

    .content-total-badge {
        background: #f8fafc;
        border: 1px solid #d8dee8;
        border-radius: 8px;
        color: #111827;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 0.7rem;
    }

    .training-accordion {
        background: #ffffff;
        border: 1px solid #d6dde7;
        border-radius: 8px;
        overflow: hidden;
    }

    .training-node {
        background: transparent;
        border: 0;
    }

    .training-node:not(:last-child) {
        border-bottom: 1px solid #edf0f4;
    }

    .training-toggle {
        align-items: center;
        box-shadow: none !important;
        color: #1f2937;
        font-size: 1.03rem;
        gap: 0.2rem;
        min-height: 4rem;
        padding: 1.15rem 1.5rem;
    }

    .training-toggle:not(.collapsed) {
        background: #cfe0fa;
        color: #0b3772;
    }

    .training-toggle::after {
        background-size: 1.05rem;
        height: 1.05rem;
        width: 1.05rem;
    }

    .training-toggle .badge {
        background: #0d6efd !important;
        color: #ffffff;
        font-size: 0.78rem;
        min-width: 1.65rem;
        padding: 0.32rem 0.48rem;
    }

    .training-node-body {
        padding: 0.9rem 1.5rem 1.35rem;
    }

    .training-node-body-level-0 {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    .training-node-level-1 {
        border: 4px solid #c4d9ff;
        border-radius: 0;
        margin-bottom: 1.1rem;
    }

    .training-toggle-level-1 {
        padding-left: 1.45rem;
    }

    .training-node-level-1 > .accordion-collapse > .training-node-body {
        padding-bottom: 0.55rem;
    }

    .training-node-level-2 {
        margin-left: 1.35rem;
        margin-right: 1.35rem;
    }

    .training-node-level-2:not(:last-child) {
        border-bottom: 1px solid #dce3ec;
    }

    .training-toggle-level-2,
    .training-toggle-level-2:not(.collapsed) {
        background: #ffffff;
        color: #212529;
        min-height: 3.8rem;
        padding-left: 0;
        padding-right: 0.2rem;
    }

    .training-toggle-level-2:not(.collapsed) {
        font-weight: 700;
    }

    .training-node-body-level-2 {
        padding: 0 0 1rem;
    }

    .content-items {
        border-radius: 12px;
        overflow: hidden;
    }

    .content-item {
        border-color: #e7ebf0;
        padding: 0.95rem 1rem;
    }

    .content-item:hover {
        background: #f8fbff;
    }

    @media (max-width: 767.98px) {
        .training-toggle {
            min-height: 3.4rem;
            padding: 0.95rem 1rem;
        }

        .training-node-level-2 {
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }
    }
</style>

<div class="card shadow-sm content-library-card"><div class="card-body p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Conteúdos Disponíveis</h1>
            <p class="text-muted mb-0">Explore os conteúdos agrupados por tipo de formação.</p>
        </div>
        <span class="badge content-total-badge align-self-start align-self-md-center"><?= count($contents ?? []) ?> conteúdos</span>
    </div>

    <?php if (empty($contentTrainingTree)): ?>
        <div class="alert alert-info mb-0">Ainda não existem conteúdos disponíveis.</div>
    <?php else: ?>
        <div class="accordion training-accordion" id="contentTrainingAccordion">
            <?php $rootIndex = 0; ?>
            <?php foreach ($contentTrainingTree as $groupName => $groupNode): ?>
                <?php $renderTrainingGroup($groupNode, $groupName, 'training-' . $rootIndex); ?>
                <?php $rootIndex++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div></div>
