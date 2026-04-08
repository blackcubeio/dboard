<?php

declare(strict_types=1);

/**
 * preview.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var bool $active
 * @var Blackcube\Dboard\Models\Forms\PreviewForm $formModel
 * @var string $toggleUrl
 * @var Yiisoft\Translator\TranslatorInterface|null $translator
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('div', [
    'dboard-preview' => '',
    'data-preview-active' => $active ? '1' : '0',
    'data-preview-url' => $toggleUrl,
    'class' => 'relative flex items-center',
]); ?>

    <?php echo Bleet::button()
            ->icon('eye')
            ->primary()
            ->outline()
            ->attribute('data-preview', 'toggle')
            ->attribute('aria-label', 'Preview')
            ->addClass($active ? '' : 'hidden'); ?>
    <?php echo Bleet::button()
            ->icon('eye-slash')
            ->primary()
            ->outline()
            ->attribute('data-preview', 'toggle')
            ->attribute('aria-label', 'Preview')
            ->addClass($active ? 'hidden' : ''); ?>

    <?php echo Html::openTag('div', [
        'data-preview' => 'panel',
        'class' => 'hidden absolute right-0 top-full mt-2 z-10 bg-white rounded-lg shadow-lg ring-1 ring-secondary-900/5 p-3 opacity-0 scale-95 transition-all duration-300',
    ]); ?>
        <?php echo Bleet::label()->active($formModel, 'simulateDate')->primary()->render(); ?>
        <?php echo Html::openTag('div', ['class' => 'mt-2 flex gap-2']); ?>
            <?php echo Html::openTag('div', ['class' => 'flex-1']); ?>
                <?php echo Bleet::input()
                        ->active($formModel, 'simulateDate')
                        ->date()
                        ->fieldData(['preview' => 'date'])
                        ->primary()
                        ->render(); ?>
            <?php echo Html::closeTag('div'); ?>
            <?php echo Bleet::button()
                    ->icon('x-mark')
                    ->outline()
                    ->primary()
                    ->attribute('data-preview', 'clear')
                    ->render(); ?>
        <?php echo Html::closeTag('div'); ?>
        <?php echo Html::openTag('div', ['class' => 'mt-2']); ?>
            <?php
                $activateLabel = $translator?->translate('Activate', category: 'dboard-common') ?? 'Activate';
                $deactivateLabel = $translator?->translate('Deactivate', category: 'dboard-common') ?? 'Deactivate';
            ?>
            <?php echo Bleet::button($active ? $deactivateLabel : $activateLabel)
                    ->primary()
                    ->sm()
                    ->addClass('w-full')
                    ->attribute('data-preview', 'apply')
                    ->attribute('data-preview-activate', $activateLabel)
                    ->attribute('data-preview-deactivate', $deactivateLabel); ?>
        <?php echo Html::closeTag('div'); ?>
    <?php echo Html::closeTag('div'); ?>

<?php echo Html::closeTag('div'); ?>
