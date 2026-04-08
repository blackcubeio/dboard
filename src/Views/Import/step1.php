<?php

declare(strict_types=1);

/**
 * step1.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array $fileEndpoints
 * @var string|null $error
 * @var string|null $csrf
 */

?>
<main class="flex-1 p-4 sm:p-6 lg:p-8">
    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(0)
            ->addStep('Upload', $urlGenerator->generate('dboard.import.step1'))
            ->addStep('Existence')
            ->addStep($translator->translate('References', category: 'dboard-modules'))
            ->addStep($translator->translate('Import', category: 'dboard-modules'))
            ->render();
        ?>
    </div>

    <?php echo Bleet::cardHeader()
        ->icon('arrow-up-tray')
        ->title($translator->translate('Import an element', category: 'dboard-modules'))
        ->primary();
    ?>

    <div class="bg-white rounded-b-lg shadow-lg p-8">
        <p class="text-sm text-gray-600 mb-6"><?php echo $translator->translate('Select a JSON or ZIP file exported from the dashboard.', category: 'dboard-modules'); ?></p>

        <?php if ($error !== null): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()->content($error)->danger()->render(); ?>
            </div>
        <?php endif; ?>

        <?php echo Html::form()
            ->post($urlGenerator->generate('dboard.import.step1'))
            ->csrf($csrf)
            ->open();
        ?>
            <div>
                <?php echo Bleet::upload()
                    ->name('importFile')
                    ->endpoint($fileEndpoints['upload'])
                    ->previewEndpoint($fileEndpoints['preview'])
                    ->deleteEndpoint($fileEndpoints['delete'])
                    ->accept(['json', 'zip'])
                    ->label($translator->translate('Import file', category: 'dboard-modules'))
                    ->render();
                ?>
            </div>

            <div class="flex justify-end gap-4 mt-6">
                <?php echo Bleet::button($translator->translate('Analyze', category: 'dboard-modules'))
                    ->icon('magnifying-glass')
                    ->submit()
                    ->primary()
                    ->render();
                ?>
            </div>
        <?php echo Html::form()->close(); ?>
    </div>
</main>
