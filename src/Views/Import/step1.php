<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
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
<main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
    <?php echo Widgets::pageHeading($translator->translate('Import an element', category: 'dboard-modules'))->render(); ?>

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

    <div class="dboard-surface p-6">
        <p class="mb-6 text-sm text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Select a JSON or ZIP file exported from the dashboard.', category: 'dboard-modules'); ?></p>

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
                    ->accept([
                        'json',
                        'zip',
                    ])
                    ->dropzoneText($translator->translate('Drop file here', category: 'dboard-common'))
                    ->browseSeparator($translator->translate('or', category: 'dboard-common'))
                    ->browseLabel($translator->translate('browse', category: 'dboard-common'))
                    ->typeErrorText($translator->translate('The file "{name}" is not an accepted type ({types}).', [
                        'name' => '{name}',
                        'types' => '{types}',
                    ], 'dboard-common'))
                    ->responseErrorText($translator->translate('Invalid server response.', category: 'dboard-common'))
                    ->uploadErrorText($translator->translate('Failed to upload "{name}".', [
                        'name' => '{name}',
                    ], 'dboard-common'))
                    ->errorTitle($translator->translate('Error', category: 'dboard-common'))
                    ->label($translator->translate('Import file', category: 'dboard-modules'))
                    ->render();
                ?>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <?php echo Bleet::submitButton($translator->translate('Analyze', category: 'dboard-modules'))
                    ->icon('magnifying-glass')
                    ->accent()
                    ->render();
                ?>
            </div>
        <?php echo Html::form()->close(); ?>
    </div>
</main>
