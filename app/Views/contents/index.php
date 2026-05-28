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
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-3" href="<?= e(url('/contents/show?id=' . (int)$content['id'])) ?>">
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
    $isRootLevel = $level === 0;
    ?>
    <div class="accordion-item">
        <h2 class="accordion-header" id="<?= e($headingId) ?>">
            <button class="accordion-button<?= $isRootLevel ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($collapseId) ?>" aria-expanded="<?= $isRootLevel ? 'true' : 'false' ?>" aria-controls="<?= e($collapseId) ?>">
                <span class="fw-semibold"><?= e($name) ?></span>
                <span class="badge text-bg-primary rounded-pill ms-2"><?= (int)$contentCount ?></span>
            </button>
        </h2>
        <div id="<?= e($collapseId) ?>" class="accordion-collapse collapse<?= $isRootLevel ? ' show' : '' ?>" aria-labelledby="<?= e($headingId) ?>">
            <div class="accordion-body">
                <?php if (!empty($contents)): ?>
                    <div class="list-group mb-3">
                        <?php foreach ($contents as $content): ?>
                            <?php $renderContentLink($content); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($childGroups)): ?>
                    <div class="accordion accordion-flush">
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

<div class="card shadow-sm"><div class="card-body">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Conteúdos Disponíveis</h1>
            <p class="text-muted mb-0">Explore os conteúdos agrupados por tipo de formação.</p>
        </div>
        <span class="badge text-bg-light align-self-start align-self-md-center border"><?= count($contents ?? []) ?> conteúdos</span>
    </div>

    <?php if (empty($contentTrainingTree)): ?>
        <div class="alert alert-info mb-0">Ainda não existem conteúdos disponíveis.</div>
    <?php else: ?>
        <div class="accordion" id="contentTrainingAccordion">
            <?php $rootIndex = 0; ?>
            <?php foreach ($contentTrainingTree as $groupName => $groupNode): ?>
                <?php $renderTrainingGroup($groupNode, $groupName, 'training-' . $rootIndex); ?>
                <?php $rootIndex++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div></div>
