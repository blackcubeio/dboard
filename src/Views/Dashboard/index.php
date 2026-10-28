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
use Blackcube\Html\Aurelia;
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
 * @var array{contentsActive: int, contentsScheduled: int, contentsExpired: int, contentsInactive: int, tagsActive: int, tagsInactive: int, updatedThisWeek: int, unreachable: int, pagesWithoutXeo: int, pagesOutOfSitemap: int, redirections: int, inactiveSlugs: int, activeLanguages: int, missingTranslations: int} $stats
 * @var ContentQuery $latestContentsQuery
 * @var ContentQuery $draftsQuery
 * @var TagQuery $latestTagsQuery
 * @var array<int, array{name: string, total: int}> $countsByLanguage
 * @var array<int, array{name: string|null, total: int}> $countsByType
 * @var string|null $csrf
 */

$expiredClasses = [
    'mt-2',
    'tabular-nums',
    'text-3xl',
    'font-semibold',
    'tracking-tight',
    $stats['contentsExpired'] > 0 ? 'text-warning-600' : 'text-secondary-950',
    $stats['contentsExpired'] > 0 ? 'dark:text-warning-400' : 'dark:text-white',
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
$healthRows = [
    [
        'label' => $translator->translate('Unreachable contents', category: 'dboard-modules'),
        'count' => $stats['unreachable'],
        'url' => $urlGenerator->generate('dboard.dashboard.health', [
            'kind' => 'unreachable',
        ]),
        'warn' => true,
    ],
    [
        'label' => $translator->translate('Pages without *EO', category: 'dboard-modules'),
        'count' => $stats['pagesWithoutXeo'],
        'url' => $urlGenerator->generate('dboard.dashboard.health', [
            'kind' => 'without-xeo',
        ]),
        'warn' => true,
    ],
    [
        'label' => $translator->translate('Pages out of the sitemap', category: 'dboard-modules'),
        'count' => $stats['pagesOutOfSitemap'],
        'url' => $urlGenerator->generate('dboard.dashboard.health', [
            'kind' => 'out-of-sitemap',
        ]),
        'warn' => true,
    ],
    [
        'label' => $translator->translate('Redirections', category: 'dboard-modules'),
        'count' => $stats['redirections'],
        'url' => $urlGenerator->generate('dboard.dashboard.health', [
            'kind' => 'redirections',
        ]),
        'warn' => false,
    ],
    [
        'label' => $translator->translate('Inactive URLs', category: 'dboard-modules'),
        'count' => $stats['inactiveSlugs'],
        'url' => $urlGenerator->generate('dboard.dashboard.health', [
            'kind' => 'inactive-slugs',
        ]),
        'warn' => true,
    ],
];
if ($stats['activeLanguages'] >= 2):
    $healthRows[] = [
        'label' => $translator->translate('Missing translations', category: 'dboard-modules'),
        'count' => $stats['missingTranslations'],
        'url' => $urlGenerator->generate('dboard.dashboard.health', [
            'kind' => 'missing-translations',
        ]),
        'warn' => true,
    ];
endif;
$moreLabel = $translator->translate('And {count} more', [
    'count' => '{count}',
], 'dboard-modules');
$languageMax = max(array_merge([
    0,
], array_column($countsByLanguage, 'total')));
$typeMax = max(array_merge([
    0,
], array_column($countsByType, 'total')));

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
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Scheduled contents', category: 'dboard-modules')); ?></dt>
                    <dd class="mt-2 tabular-nums text-3xl font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo $stats['contentsScheduled']; ?></dd>
                </div>
                <div class="flex-1 rounded-xl border border-secondary-200 bg-white px-5 py-4 shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Expired contents', category: 'dboard-modules')); ?></dt>
                    <?php echo Html::tag('dd', (string) $stats['contentsExpired'], [
                        'class' => $expiredClasses,
                    ]); ?>
                </div>
                <div class="flex-1 rounded-xl border border-secondary-200 bg-white px-5 py-4 shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Inactive contents', category: 'dboard-modules')); ?></dt>
                    <?php echo Html::tag('dd', (string) $stats['contentsInactive'], [
                        'class' => $inactiveClasses,
                    ]); ?>
                </div>
                <div class="flex-1 rounded-xl border border-secondary-200 bg-white px-5 py-4 shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <dt class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Updated this week', category: 'dboard-modules')); ?></dt>
                    <dd class="mt-2 tabular-nums text-3xl font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo $stats['updatedThisWeek']; ?></dd>
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
                            <?php echo Bleet::badge((string) $stats['contentsExpired'])->dot()->warning(); ?>
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
                            <p class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Contents', category: 'dboard-modules')); ?></p>
                            <h3 class="mt-1 text-base font-semibold text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Drafts in progress', category: 'dboard-modules')); ?></h3>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <?php echo Bleet::badge((string) $stats['contentsInactive'])->dot()->danger(); ?>
                        </div>
                    </div>
                    <?php $hasDrafts = false; ?>
                    <ul class="mt-3 divide-y divide-secondary-100 px-5 pb-2 dark:divide-white/5">
                        <?php /** @var Content $draft */ ?>
                        <?php foreach ($draftsQuery->each() as $draft): ?>
                            <?php $hasDrafts = true; ?>
                            <li class="flex items-baseline justify-between gap-4 py-3">
                                <?php echo Html::a(
                                    Html::encode($draft->getName() ?? $translator->translate('Unnamed', category: 'dboard-modules')),
                                    $urlGenerator->generate('dboard.contents.edit', [
                                        'id' => $draft->getId(),
                                    ]),
                                    [
                                        'class' => $rowLinkClasses,
                                    ]
                                ); ?>
                                <span class="shrink-0 text-xs text-secondary-500 dark:text-secondary-400"><?php echo Html::encode(RelativeTimeHelper::format($draft->getDateUpdate(), $translator)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($hasDrafts === false): ?>
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

            <div class="mt-6 flex flex-col gap-6 lg:flex-row">
                <article class="min-w-0 flex-1 overflow-hidden rounded-xl border border-secondary-200 bg-white shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <div class="h-0.5 bg-linear-to-r from-accent-500 to-accent-300"></div>
                    <div class="px-5 pt-5">
                        <p class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Site', category: 'dboard-modules')); ?></p>
                        <h3 class="mt-1 text-base font-semibold text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Site health', category: 'dboard-modules')); ?></h3>
                    </div>
                    <ul class="mt-3 divide-y divide-secondary-100 px-5 pb-2 dark:divide-white/5">
                        <?php foreach ($healthRows as $healthRow): ?>
                            <?php
                            $healthCountClasses = [
                                'tabular-nums',
                                'text-sm',
                                'font-semibold',
                                $healthRow['warn'] === true && $healthRow['count'] > 0 ? 'text-warning-600' : 'text-secondary-950',
                                $healthRow['warn'] === true && $healthRow['count'] > 0 ? 'dark:text-warning-400' : 'dark:text-white',
                            ];
                            ?>
                            <li>
                                <?php echo Html::openTag('dboard-unfold', Aurelia::attributesCustomElement([
                                    'url' => $healthRow['url'],
                                    'moreLabel' => $moreLabel,
                                ])); ?>
                                    <button type="button" class="group flex w-full cursor-pointer items-baseline justify-between gap-4 py-3 text-left" data-unfold="trigger" aria-expanded="false">
                                        <span class="min-w-0 truncate text-sm font-medium text-secondary-900 transition-colors group-hover:text-accent-600 dark:text-secondary-100 dark:group-hover:text-accent-300"><?php echo Html::encode($healthRow['label']); ?></span>
                                        <span class="flex shrink-0 items-center gap-2">
                                            <?php echo Html::tag('span', (string) $healthRow['count'], [
                                                'class' => $healthCountClasses,
                                            ]); ?>
                                            <?php echo Bleet::svg()->outline('chevron-down')->addClass('size-4', 'text-secondary-400', 'transition-transform', 'group-aria-expanded:rotate-180')->render(); ?>
                                        </span>
                                    </button>
                                <?php echo Html::closeTag('dboard-unfold'); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>

                <article class="min-w-0 flex-1 overflow-hidden rounded-xl border border-secondary-200 bg-white shadow-xs dark:border-white/10 dark:bg-primary-900">
                    <div class="h-0.5 bg-linear-to-r from-accent-500 to-accent-300"></div>
                    <div class="px-5 pt-5">
                        <p class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Site', category: 'dboard-modules')); ?></p>
                        <h3 class="mt-1 text-base font-semibold text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Shape of the site', category: 'dboard-modules')); ?></h3>
                    </div>
                    <div class="mt-3 flex flex-col gap-6 px-5 pb-5 sm:flex-row">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Active contents by language', category: 'dboard-modules')); ?></h4>
                            <dl class="mt-2 flex flex-col">
                                <?php foreach ($countsByLanguage as $languageCount): ?>
                                    <?php $languageWidth = $languageMax > 0 ? (int) round($languageCount['total'] * 100 / $languageMax) : 0; ?>
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-4 py-2">
                                        <dt class="min-w-0 truncate text-sm text-secondary-700 dark:text-secondary-300"><?php echo Html::encode($languageCount['name']); ?></dt>
                                        <dd class="tabular-nums text-sm font-semibold text-secondary-950 dark:text-white"><?php echo $languageCount['total']; ?></dd>
                                        <dd class="mt-1.5 h-1.5 basis-full overflow-hidden rounded-full bg-secondary-100 dark:bg-white/10"><?php echo Html::tag('div', '', [
                                            'class' => 'h-full rounded-full bg-accent-500',
                                            'style' => 'width: ' . $languageWidth . '%',
                                        ]); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                            <?php if (empty($countsByLanguage) === true): ?>
                                <p class="mt-2 text-sm text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('No results', category: 'dboard-common')); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-mono text-3xs font-medium uppercase tracking-label text-secondary-400"><?php echo Html::encode($translator->translate('Active contents by type', category: 'dboard-modules')); ?></h4>
                            <dl class="mt-2 flex flex-col">
                                <?php foreach ($countsByType as $typeCount): ?>
                                    <?php $typeWidth = $typeMax > 0 ? (int) round($typeCount['total'] * 100 / $typeMax) : 0; ?>
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-4 py-2">
                                        <dt class="min-w-0 truncate text-sm text-secondary-700 dark:text-secondary-300"><?php echo Html::encode($typeCount['name'] ?? $translator->translate('Without type', category: 'dboard-modules')); ?></dt>
                                        <dd class="tabular-nums text-sm font-semibold text-secondary-950 dark:text-white"><?php echo $typeCount['total']; ?></dd>
                                        <dd class="mt-1.5 h-1.5 basis-full overflow-hidden rounded-full bg-secondary-100 dark:bg-white/10"><?php echo Html::tag('div', '', [
                                            'class' => 'h-full rounded-full bg-accent-500',
                                            'style' => 'width: ' . $typeWidth . '%',
                                        ]); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                            <?php if (empty($countsByType) === true): ?>
                                <p class="mt-2 text-sm text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('No results', category: 'dboard-common')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
        </main>
