<?php

declare(strict_types=1);

/**
 * _xeo-suggest-content.php
 * Partial view for XEO metadata fields (title → nofollow).
 * Rendered inside a bleet-ajaxify zone for partial refresh via suggest.
 *
 * @copyright 2010-2026 Blackcube
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
        <?php echo Bleet::label()->active($formModel, 'title')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::input()
                ->active($formModel, 'title')
                ->text()
                ->secondary()
                ->render(); ?>
        </div>
    </div>
    <div>
        <?php echo Bleet::label()->active($formModel, 'jsonldType')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::select()
                ->active($formModel, 'jsonldType')
                ->options(XeoForm::getJsonldTypeOptions())
                ->secondary()
                ->render(); ?>
        </div>
    </div>
    <div>
        <?php echo Bleet::label()->active($formModel, 'description')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::textarea()
                ->active($formModel, 'description')
                ->rows(3)
                ->secondary()
                ->render(); ?>
        </div>
    </div>
    <div>
        <?php echo Bleet::label()->active($formModel, 'keywords')->secondary()->render(); ?>
        <div class="mt-2">
            <?php echo Bleet::textarea()
                ->active($formModel, 'keywords')
                ->rows(3)
                ->secondary()
                ->render(); ?>
        </div>
    </div>

    <div>
        <?php echo Bleet::upload()
            ->active($formModel, 'image')
            ->endpoint($fileEndpoints['upload'])
            ->previewEndpoint($fileEndpoints['preview'])
            ->deleteEndpoint($fileEndpoints['delete'])
            ->accept(['png', 'jpg', 'jpeg', 'webp'])
            ->render(); ?>
    </div>

    <div class="flex gap-6">
        <?php echo Bleet::checkbox()
            ->active($formModel, 'speakable')
            ->secondary()
            ->render(); ?>

        <?php echo Bleet::checkbox()
            ->active($formModel, 'accessibleForFree')
            ->secondary()
            ->render(); ?>
    </div>

    <div class="flex gap-6">
        <?php echo Bleet::checkbox()
            ->active($formModel, 'noindex')
            ->secondary()
            ->render(); ?>

        <?php echo Bleet::checkbox()
            ->active($formModel, 'nofollow')
            ->secondary()
            ->render(); ?>
    </div>
</div>
