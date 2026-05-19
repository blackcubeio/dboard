<?php

declare(strict_types=1);

/**
 * _md-import-form-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var array|null $importResult
 * @var array $fileEndpoints
 * @var string $importFormAction
 */

?>
<?php echo Html::form()
        ->method('POST')
        ->action($importFormAction)
        ->csrf($csrf)
        ->noValidate()
        ->open(); ?>
<div class="space-y-4">

    <?php if ($importResult !== null && $importResult['success']): ?>
        <div class="rounded-md bg-success-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <?php echo Bleet::svg()->outline('check-circle')->addClass('size-5', 'text-success-400'); ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-success-800">
                        <?php echo $translator->translate('{count, plural, one{Import successful: # block created.} other{Import successful: # blocks created.}}', ['count' => (int) $importResult['blocsCreated']], 'dboard-common'); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($importResult !== null && !empty($importResult['errors'])): ?>
        <div class="rounded-md bg-danger-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <?php echo Bleet::svg()->outline('x-circle')->addClass('size-5', 'text-danger-400'); ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-danger-800"><?php echo $translator->translate('Import failed:', category: 'dboard-common'); ?></p>
                    <ul class="mt-1 text-sm text-danger-700 list-disc list-inside">
                        <?php foreach ($importResult['errors'] as $error): ?>
                            <li><?php echo Html::encode($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($importResult !== null && !empty($importResult['warnings'])): ?>
        <div class="rounded-md bg-warning-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <?php echo Bleet::svg()->outline('exclamation-triangle')->addClass('size-5', 'text-warning-400'); ?>
                </div>
                <div class="ml-3">
                    <ul class="text-sm text-warning-700 list-disc list-inside">
                        <?php foreach ($importResult['warnings'] as $warning): ?>
                            <li><?php echo Html::encode($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div>
        <?php echo Bleet::upload()
            ->name('mdFile')
            ->endpoint($fileEndpoints['upload'])
            ->previewEndpoint($fileEndpoints['preview'])
            ->deleteEndpoint($fileEndpoints['delete'])
            ->accept(['md'])
            ->label($translator->translate('Markdown file', category: 'dboard-common'))
            ->render(); ?>
    </div>
    <div>
        <?php echo Bleet::button($translator->translate('Import', category: 'dboard-common'))
            ->submit()
            ->icon('arrow-up-tray')
            ->primary()
            ->render();
        ?>
    </div>
</div>
<?php echo Html::closeTag('form'); ?>
