<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentQuery;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagQuery;
use Blackcube\Dboard\Helpers\RelativeTimeHelper;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Widgets\Widgets;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var array{contentsActive: int, contentsOutOfDate: int, contentsInactive: int, tagsActive: int, tagsInactive: int} $stats
 * @var ContentQuery $latestContentsQuery
 * @var TagQuery $latestTagsQuery
 * @var string|null $csrf
 */

$outOfDateClasses = [
    'mt-2',
    'tabular-nums',
    'text-3xl',
    'font-semibold',
    'tracking-tight',
    $stats['contentsOutOfDate'] > 0 ? 'text-warning-600' : 'text-secondary-950',
    $stats['contentsOutOfDate'] > 0 ? 'dark:text-warning-400' : 'dark:text-white',
];
$inactiveClasses = [
    'mt-2',
    'tabular-nums',
    'text-3xl',
    'font-semibold',
    'tracking-tight',
    $stats['contentsInactive'] > 0 ? 'text-danger-600' : 'text-secondary-950',
    $stats['contentsInactive'] > 0 ? 'dark:text-danger-400' : 'dark:text-white',
];
$rowLinkClasses = [
    'min-w-0',
    'truncate',
    'text-sm',
    'font-medium',
    'text-secondary-900',
    'underline-offset-4',
    'transition-colors',
    'hover:text-accent-600',
    'hover:underline',
    'dark:text-secondary-100',
    'dark:hover:text-accent-300',
];

?>

        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('Dashboard', category: 'dboard-modules'))
                ->subtitle($translator->translate('Your contents and tags at a glance, and what changed last.', category: 'dboard-modules'))
                ->render(); ?>

            <dl class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-1 rounded-xl border border-secondary-200 bg-white px-5 py-4 shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Active contents', category: 'dboard-modules')); ?></dt>
                    <dd class="mt-2 tabular-nums text-3xl font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo $stats['contentsActive']; ?></dd>
                </div>
                <div class="flex-1 rounded-xl border border-secondary-200 bg-white px-5 py-4 shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Out of date contents', category: 'dboard-modules')); ?></dt>
                    <?php echo Html::tag('dd', (string) $stats['contentsOutOfDate'], [
                        'class' => $outOfDateClasses,
                    ]); ?>
                </div>
                <div class="flex-1 rounded-xl border border-secondary-200 bg-white px-5 py-4 shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Inactive contents', category: 'dboard-modules')); ?></dt>
                    <?php echo Html::tag('dd', (string) $stats['contentsInactive'], [
                        'class' => $inactiveClasses,
                    ]); ?>
                </div>
            </dl>

            <h2 class="mt-10 text-lg font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Overview', category: 'dboard-modules')); ?></h2>

            <div class="mt-4 flex flex-col gap-6 lg:flex-row">
                <article class="min-w-0 flex-1 overflow-hidden rounded-xl border border-secondary-200 bg-white shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <div class="h-0.5 bg-linear-to-r from-accent-500 to-accent-300"></div>
                    <div class="flex items-start justify-between gap-4 px-5 pt-5">
                        <div class="min-w-0">
                            <p class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Contents', category: 'dboard-modules')); ?></p>
                            <h3 class="mt-1 text-base font-semibold text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Latest updated contents', category: 'dboard-modules')); ?></h3>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <?php echo Bleet::badge((string) $stats['contentsActive'])->dot()->success(); ?>
                            <?php echo Bleet::badge((string) $stats['contentsOutOfDate'])->dot()->warning(); ?>
                            <?php echo Bleet::badge((string) $stats['contentsInactive'])->dot()->danger(); ?>
                        </div>
                    </div>
                    <?php $hasContents = false; ?>
                    <ul class="mt-3 divide-y divide-secondary-100 px-5 pb-2 dark:divide-white/5">
                        <?php /** @var Content $content */ ?>
                        <?php foreach ($latestContentsQuery->each() as $content): ?>
                            <?php $hasContents = true; ?>
                            <li class="flex items-baseline justify-between gap-4 py-3">
                                <?php echo Html::a(
                                    Html::encode($content->getName() ?? $translator->translate('Unnamed', category: 'dboard-modules')),
                                    $urlGenerator->generate('dboard.contents.edit', [
                                        'id' => $content->getId(),
                                    ]),
                                    [
                                        'class' => $rowLinkClasses,
                                    ]
                                ); ?>
                                <span class="shrink-0 text-xs text-secondary-500 dark:text-secondary-400"><?php echo Html::encode(RelativeTimeHelper::format($content->getDateUpdate(), $translator)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($hasContents === false): ?>
                        <p class="px-5 pb-5 text-sm text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('No results', category: 'dboard-common')); ?></p>
                    <?php endif; ?>
                </article>

                <article class="min-w-0 flex-1 overflow-hidden rounded-xl border border-secondary-200 bg-white shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <div class="h-0.5 bg-linear-to-r from-accent-500 to-accent-300"></div>
                    <div class="flex items-start justify-between gap-4 px-5 pt-5">
                        <div class="min-w-0">
                            <p class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Tags', category: 'dboard-modules')); ?></p>
                            <h3 class="mt-1 text-base font-semibold text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Latest updated tags', category: 'dboard-modules')); ?></h3>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <?php echo Bleet::badge((string) $stats['tagsActive'])->dot()->success(); ?>
                            <?php echo Bleet::badge((string) $stats['tagsInactive'])->dot()->danger(); ?>
                        </div>
                    </div>
                    <?php $hasTags = false; ?>
                    <ul class="mt-3 divide-y divide-secondary-100 px-5 pb-2 dark:divide-white/5">
                        <?php /** @var Tag $tag */ ?>
                        <?php foreach ($latestTagsQuery->each() as $tag): ?>
                            <?php $hasTags = true; ?>
                            <li class="flex items-baseline justify-between gap-4 py-3">
                                <?php echo Html::a(
                                    Html::encode($tag->getName()),
                                    $urlGenerator->generate('dboard.tags.edit', [
                                        'id' => $tag->getId(),
                                    ]),
                                    [
                                        'class' => $rowLinkClasses,
                                    ]
                                ); ?>
                                <span class="shrink-0 text-xs text-secondary-500 dark:text-secondary-400"><?php echo Html::encode(RelativeTimeHelper::format($tag->getDateUpdate(), $translator)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($hasTags === false): ?>
                        <p class="px-5 pb-5 text-sm text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('No results', category: 'dboard-common')); ?></p>
                    <?php endif; ?>
                </article>
            </div>
        </main>
