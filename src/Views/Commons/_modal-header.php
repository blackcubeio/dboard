<?php

declare(strict_types=1);

/**
 * _modal-header.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Enums\UiIcon;
use Yiisoft\Html\Html;

/**
 * @var string $title
 * @var UiColor $uiColor
 */

$icon = match ($uiColor) {
    UiColor::Success => UiIcon::Success,
    UiColor::Warning => UiIcon::Warning,
    UiColor::Danger => UiIcon::Danger,
    default => UiIcon::Info,
};

$iconSvg = match ($icon) {
    UiIcon::Info => Bleet::svg()->solid('information-circle')->addClass('size-6')->render(),
    UiIcon::Success => Bleet::svg()->solid('check-circle')->addClass('size-6')->render(),
    UiIcon::Warning => Bleet::svg()->solid('exclamation-triangle')->addClass('size-6')->render(),
    UiIcon::Danger => Bleet::svg()->solid('x-circle')->addClass('size-6')->render(),
};

$closeSvg = Bleet::svg()->solid('x-mark')->addClass('size-6')->render();

$headerBgClass = match ($uiColor) {
    UiColor::Primary => 'bg-primary-600',
    UiColor::Secondary => 'bg-secondary-600',
    UiColor::Success => 'bg-success-600',
    UiColor::Warning => 'bg-warning-600',
    UiColor::Danger => 'bg-danger-600',
    UiColor::Info => 'bg-info-600',
    UiColor::Accent => 'bg-accent-600',
};

$iconBgClass = match ($uiColor) {
    UiColor::Primary => 'bg-primary-100',
    UiColor::Secondary => 'bg-secondary-100',
    UiColor::Success => 'bg-success-100',
    UiColor::Warning => 'bg-warning-100',
    UiColor::Danger => 'bg-danger-100',
    UiColor::Info => 'bg-info-100',
    UiColor::Accent => 'bg-accent-100',
};

$iconTextClass = match ($uiColor) {
    UiColor::Primary => 'text-primary-600',
    UiColor::Secondary => 'text-secondary-600',
    UiColor::Success => 'text-success-600',
    UiColor::Warning => 'text-warning-600',
    UiColor::Danger => 'text-danger-600',
    UiColor::Info => 'text-info-600',
    UiColor::Accent => 'text-accent-600',
};

$closeTextClass = match ($uiColor) {
    UiColor::Primary => 'text-primary-200',
    UiColor::Secondary => 'text-secondary-200',
    UiColor::Success => 'text-success-200',
    UiColor::Warning => 'text-warning-200',
    UiColor::Danger => 'text-danger-200',
    UiColor::Info => 'text-info-200',
    UiColor::Accent => 'text-accent-200',
};
?>
<?php echo Html::openTag('div', ['class' => 'px-4 py-4 sm:px-6 ' . $headerBgClass]); ?>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <?php echo Html::openTag('div', ['class' => $iconBgClass . ' shrink-0 flex size-10 items-center justify-center rounded-full']); ?>
                <?php echo Html::tag('div', $iconSvg, ['class' => $iconTextClass])->encode(false); ?>
            <?php echo Html::closeTag('div'); ?>
            <?php echo Html::tag('h3', Html::encode($title), ['class' => 'text-lg font-medium text-white']); ?>
        </div>
        <div class="ml-3 flex h-7 items-center">
            <?php echo Html::button($closeSvg, [
                'type' => 'button',
                'class' => 'relative rounded-md ' . $closeTextClass . ' hover:text-white focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white cursor-pointer',
                'data-modal' => 'close',
            ])->encode(false); ?>
        </div>
    </div>
<?php echo Html::closeTag('div'); ?>
