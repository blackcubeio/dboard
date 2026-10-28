<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

/**
 * The bar of ink of the panel, dark in both modes: the brand on top, the
 * navigation, and at the foot what is one's own, the account, the preview,
 * the theme switch and the installed version.
 *
 * The kit drives it: bleet-menu slides the bar on a phone (data-menu-show /
 * data-menu-hide) and folds the groups ([data-menu=toggle-button-ID] shows
 * [data-menu=toggle-list-ID], the chevron turns by data-menu-show on its
 * svg[data-menu=icon]); bleet-profile opens the account menu, upwards, since
 * nothing sits below it.
 *
 * @var Blackcube\Bleet\Widgets\SidebarItem[] $items
 * @var string $brandUrl
 * @var string $name
 * @var string $initials
 * @var string $email
 * @var string $accountUrl
 * @var string $logoutUrl
 * @var string|null $version
 * @var string $preview
 * @var string $ruleClass
 * @var string $activeIconClass
 * @var Yiisoft\Translator\TranslatorInterface|null $translator
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

$closeMenuLabel = $translator?->translate('Close menu', category: 'dboard-common') ?? 'Close menu';
$navigationLabel = $translator?->translate('Main navigation', category: 'dboard-common') ?? 'Main navigation';
$accountMenuLabel = $translator?->translate('Open the account menu', category: 'dboard-common') ?? 'Open the account menu';
$themeLabel = $translator?->translate('Switch between light and dark', category: 'dboard-common') ?? 'Switch between light and dark';
$accountLabel = $translator?->translate('My account', category: 'dboard-modules') ?? 'My account';
$logoutLabel = $translator?->translate('Log out', category: 'dboard-modules') ?? 'Log out';

$linkClasses = [
    'group',
    'flex',
    'items-center',
    'gap-3',
    'rounded-md',
    'border-l-2',
    'px-3',
    'py-2',
    'text-sm',
    'font-medium',
    'transition-colors',
];
$linkActiveClasses = [
    $ruleClass,
    'bg-white/10',
    'text-white',
];
$linkInactiveClasses = [
    'border-transparent',
    'text-secondary-400',
    'hover:bg-white/5',
    'hover:text-white',
];
$iconClasses = [
    'block',
    'size-5',
    'shrink-0',
    'transition-colors',
];
$iconActiveClasses = [
    $activeIconClass,
];
$iconInactiveClasses = [
    'text-secondary-500',
    'group-hover:text-secondary-300',
];
$sublinkClasses = [
    'block',
    'rounded-md',
    'px-3',
    'py-1.5',
    'text-sm',
    'transition-colors',
];
$sublinkActiveClasses = [
    'bg-white/5',
    'text-white',
];
$sublinkInactiveClasses = [
    'text-secondary-400',
    'hover:bg-white/5',
    'hover:text-white',
];
$menuItemClasses = [
    'flex',
    'items-center',
    'gap-2.5',
    'rounded-md',
    'px-3',
    'py-2',
    'text-sm',
    'text-secondary-200',
    'transition-colors',
    'hover:bg-white/5',
    'hover:text-white',
];

?>
<aside bleet-menu data-menu-show="translate-x-0" data-menu-hide="-translate-x-full" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-white/5 bg-primary-950 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

    <div class="flex h-16 shrink-0 items-center justify-between gap-3 px-5">
        <?php echo Html::openTag('a', [
            'href' => $brandUrl,
            'class' => 'flex items-center gap-2.5 rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-400',
        ]); ?>
            <span class="flex size-7 items-center justify-center rounded-md bg-white/10 text-white">
                <?php echo Bleet::svg()->logo('blackcube')->addClass('size-4'); ?>
            </span>
            <span class="text-base font-semibold tracking-tight text-white">Dboard</span>
        <?php echo Html::closeTag('a'); ?>
        <button type="button" data-menu="close" class="-m-2 cursor-pointer rounded-md p-2 text-secondary-400 transition-colors hover:bg-white/5 hover:text-white focus-visible:outline-2 focus-visible:outline-accent-400 lg:hidden">
            <span class="sr-only"><?php echo Html::encode($closeMenuLabel); ?></span>
            <?php echo Bleet::svg()->outline('x-mark')->addClass('size-5'); ?>
        </button>
    </div>

    <?php echo Html::openTag('nav', [
        'aria-label' => $navigationLabel,
        'class' => 'flex-1 overflow-y-auto px-3 pb-4',
    ]); ?>
        <ul class="space-y-0.5">
            <?php foreach ($items as $item): ?>
                <?php if ($item->hasChildren() === true): ?>
                    <?php $toggleId = $item->getToggleId() ?? uniqid('sidebar-'); ?>
                    <?php
                    $hasActiveChild = false;
                    foreach ($item->getChildren() as $child):
                        if ($child->isActive() === true):
                            $hasActiveChild = true;
                        endif;
                    endforeach;
                    $groupClasses = [
                        ...$linkClasses,
                        ...($hasActiveChild === true ? $linkActiveClasses : $linkInactiveClasses),
                        'w-full',
                        'cursor-pointer',
                        'justify-between',
                    ];
                    $groupIconClasses = [
                        ...$iconClasses,
                        ...($hasActiveChild === true ? $iconActiveClasses : $iconInactiveClasses),
                    ];
                    ?>
                    <li>
                        <?php echo Html::openTag('button', [
                            'type' => 'button',
                            'data-menu' => 'toggle-button-' . $toggleId,
                            'aria-expanded' => 'false',
                            'class' => $groupClasses,
                        ]); ?>
                            <span class="flex items-center gap-3">
                                <?php if ($item->getIconName() !== null): ?>
                                    <?php if ($item->getIconType() === 'solid'): ?>
                                        <?php echo Bleet::svg()->solid($item->getIconName())->addClass(...$groupIconClasses); ?>
                                    <?php else: ?>
                                        <?php echo Bleet::svg()->outline($item->getIconName())->addClass(...$groupIconClasses); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php echo Html::encode($item->getLabel()); ?>
                            </span>
                            <?php echo Bleet::svg()
                                ->solid('chevron-down')
                                ->addClass('size-4', 'text-secondary-500', 'transition-transform', 'duration-200')
                                ->attribute('data-menu', 'icon')
                                ->attribute('data-menu-show', 'rotate-180'); ?>
                        <?php echo Html::closeTag('button'); ?>
                        <?php echo Html::openTag('ul', [
                            'data-menu' => 'toggle-list-' . $toggleId,
                            'hidden' => true,
                            'class' => 'mt-0.5 mb-2 ml-5 space-y-0.5 border-l border-white/10 pl-3',
                        ]); ?>
                            <?php foreach ($item->getChildren() as $child): ?>
                                <li>
                                    <?php echo Html::openTag('a', [
                                        'href' => $child->getUrl() ?? '#',
                                        'aria-current' => $child->isActive() === true ? 'page' : null,
                                        'class' => [
                                            ...$sublinkClasses,
                                            ...($child->isActive() === true ? $sublinkActiveClasses : $sublinkInactiveClasses),
                                        ],
                                    ]); ?>
                                        <?php echo Html::encode($child->getLabel()); ?>
                                    <?php echo Html::closeTag('a'); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php echo Html::closeTag('ul'); ?>
                    </li>
                <?php else: ?>
                    <li>
                        <?php echo Html::openTag('a', [
                            'href' => $item->getUrl() ?? '#',
                            'aria-current' => $item->isActive() === true ? 'page' : null,
                            'class' => [
                                ...$linkClasses,
                                ...($item->isActive() === true ? $linkActiveClasses : $linkInactiveClasses),
                            ],
                        ]); ?>
                            <?php if ($item->getIconName() !== null): ?>
                                <?php $itemIconClasses = [
                                    ...$iconClasses,
                                    ...($item->isActive() === true ? $iconActiveClasses : $iconInactiveClasses),
                                ]; ?>
                                <?php if ($item->getIconType() === 'solid'): ?>
                                    <?php echo Bleet::svg()->solid($item->getIconName())->addClass(...$itemIconClasses); ?>
                                <?php else: ?>
                                    <?php echo Bleet::svg()->outline($item->getIconName())->addClass(...$itemIconClasses); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php echo Html::encode($item->getLabel()); ?>
                        <?php echo Html::closeTag('a'); ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php echo Html::closeTag('nav'); ?>

    <div class="shrink-0 border-t border-white/5 p-3">
        <div class="flex items-center justify-end gap-1">
            <?php echo $preview; ?>
            <?php echo Html::openTag('button', [
                'type' => 'button',
                'dboard-dark-toggle' => '',
                'title' => $themeLabel,
                'aria-label' => $themeLabel,
                'class' => 'inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-md text-secondary-400 transition-colors hover:bg-white/5 hover:text-white focus-visible:outline-2 focus-visible:outline-accent-400',
            ]); ?>
                <?php echo Bleet::svg()->outline('moon')->addClass('size-4', 'dark:hidden'); ?>
                <?php echo Bleet::svg()->outline('sun')->addClass('hidden', 'size-4', 'dark:block'); ?>
            <?php echo Html::closeTag('button'); ?>
        </div>
            <div class="relative mt-1" bleet-profile>
                <button type="button" data-profile="toggle" aria-haspopup="menu" aria-expanded="false" class="flex w-full cursor-pointer items-center gap-3 rounded-md p-2 text-left transition-colors hover:bg-white/5 focus-visible:outline-2 focus-visible:outline-accent-400">
                    <span class="sr-only"><?php echo Html::encode($accountMenuLabel); ?></span>
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-accent-400 to-accent-700 text-xs font-semibold text-white ring-1 ring-white/20"><?php echo Html::encode($initials); ?></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-white"><?php echo Html::encode($name); ?></span>
                        <span class="block truncate text-xs text-secondary-500"><?php echo Html::encode($email); ?></span>
                    </span>
                    <?php echo Bleet::svg()->outline('chevron-up-down')->addClass('size-4', 'shrink-0', 'text-secondary-500'); ?>
                </button>
                <div data-profile="panel" hidden data-profile-show="opacity-100 scale-100" data-profile-hide="opacity-0 scale-95 pointer-events-none" class="absolute inset-x-0 bottom-full z-10 mb-2 origin-bottom rounded-lg bg-primary-900 p-1 shadow-xl ring-1 ring-white/10 transition transform opacity-0 scale-95 pointer-events-none focus:outline-hidden">
                    <?php echo Html::openTag('a', [
                        'href' => $accountUrl,
                        'class' => $menuItemClasses,
                    ]); ?>
                        <?php echo Bleet::svg()->outline('user')->addClass('size-4', 'text-secondary-400'); ?>
                        <?php echo Html::encode($accountLabel); ?>
                    <?php echo Html::closeTag('a'); ?>
                    <?php echo Html::openTag('a', [
                        'href' => $logoutUrl,
                        'class' => $menuItemClasses,
                    ]); ?>
                        <?php echo Bleet::svg()->outline('arrow-right-start-on-rectangle')->addClass('size-4', 'text-secondary-400'); ?>
                        <?php echo Html::encode($logoutLabel); ?>
                    <?php echo Html::closeTag('a'); ?>
                </div>
            </div>
        <p class="mt-2 px-2 text-xs text-secondary-500">&copy; <?php echo date('Y'); ?> Blackcube<?php if ($version !== null): ?> &middot; <span class="font-mono"><?php echo Html::encode($version); ?></span><?php endif; ?></p>
    </div>

</aside>
