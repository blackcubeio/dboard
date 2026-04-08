<?php

declare(strict_types=1);

/**
 * _drawer-header.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\UiColor;
use Yiisoft\Html\Html;

/**
 * @var Yiisoft\View\WebView $this
 * @var string $title
 * @var UiColor $uiColor
 */

$closeSvg = Bleet::svg()->solid('x-mark')->addClass('size-6')->render();

$headerBgClass = match ($uiColor) {
    UiColor::Primary => 'bg-primary-700',
    UiColor::Secondary => 'bg-secondary-700',
    UiColor::Success => 'bg-success-700',
    UiColor::Warning => 'bg-warning-700',
    UiColor::Danger => 'bg-danger-700',
    UiColor::Info => 'bg-info-700',
    UiColor::Accent => 'bg-accent-700',
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
<?php echo Html::openTag('div', ['class' => 'px-4 py-6 sm:px-6 ' . $headerBgClass]); ?>
    <div class="flex items-center justify-between">
        <?php echo Html::tag('h3', Html::encode($title), ['class' => 'text-lg font-medium text-white']); ?>
        <div class="ml-3 flex h-7 items-center">
            <?php echo Html::button($closeSvg, [
                'type' => 'button',
                'class' => 'relative rounded-md ' . $closeTextClass . ' hover:text-white focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white cursor-pointer',
                'data-drawer' => 'close',
            ])->encode(false); ?>
        </div>
    </div>
<?php echo Html::closeTag('div'); ?>
