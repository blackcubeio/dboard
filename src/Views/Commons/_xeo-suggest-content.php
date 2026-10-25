<?php

declare(strict_types=1);

/**
 * _xeo-suggest-content.php
 * Partial view for XEO metadata fields (title → nofollow).
 * Rendered inside a bleet-ajaxify zone for partial refresh via suggest.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\XeoForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var XeoForm $formModel
 * @var array $fileEndpoints
 */

?>
<div class="space-y-4">
    <div>
        <?php echo Bleet::label('', $formModel, 'title')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::input($formModel, 'title')
                ->hideLabel()
                ->text()
                ->secondary()
                ->render(); ?>
        </div>
    </div>
    <div>
        <?php echo Bleet::label('', $formModel, 'jsonldType')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::select($formModel, 'jsonldType')
                ->hideLabel()
                ->optionsData(XeoForm::getJsonldTypeOptions())
                ->secondary()
                ->render(); ?>
        </div>
    </div>
    <div>
        <?php echo Bleet::label('', $formModel, 'description')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::textarea($formModel, 'description')
                ->hideLabel()
                ->rows(3)
                ->secondary()
                ->render(); ?>
        </div>
    </div>
    <div>
        <?php echo Bleet::label('', $formModel, 'keywords')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::textarea($formModel, 'keywords')
                ->hideLabel()
                ->rows(3)
                ->secondary()
                ->render(); ?>
        </div>
    </div>

    <div>
        <?php echo Bleet::upload($formModel, 'image')
            ->endpoint($fileEndpoints['upload'])
            ->previewEndpoint($fileEndpoints['preview'])
            ->deleteEndpoint($fileEndpoints['delete'])
            ->accept([
                'png',
                'jpg',
                'jpeg',
                'webp',
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
            ->render(); ?>
    </div>

    <div class="flex gap-6">
        <?php echo Bleet::checkbox($formModel, 'speakable')
            ->secondary()
            ->render(); ?>

        <?php echo Bleet::checkbox($formModel, 'accessibleForFree')
            ->secondary()
            ->render(); ?>
    </div>

    <div class="flex gap-6">
        <?php echo Bleet::checkbox($formModel, 'noindex')
            ->secondary()
            ->render(); ?>

        <?php echo Bleet::checkbox($formModel, 'nofollow')
            ->secondary()
            ->render(); ?>
    </div>
</div>
