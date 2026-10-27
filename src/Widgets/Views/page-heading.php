<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

/**
 * The heading of a page: the title and its subtitle on the left, the actions
 * of the page on the right, under the title on a phone.
 *
 * @var string $title
 * @var string|null $subtitle
 * @var string[] $actions
 */

use Yiisoft\Html\Html;

?>
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-secondary-950 sm:text-3xl dark:text-white"><?php echo Html::encode($title); ?></h1>
        <?php if ($subtitle !== null && $subtitle !== ''): ?>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($subtitle); ?></p>
        <?php endif; ?>
    </div>
    <?php if (empty($actions) === false): ?>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <?php foreach ($actions as $action): ?>
                <?php echo $action; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
