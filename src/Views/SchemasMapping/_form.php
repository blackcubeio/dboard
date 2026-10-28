<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\SchemaSchemaForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var SchemaSchemaForm $formModel
 * @var CurrentRoute $currentRoute
 * @var array<int, string> $regularOptions
 * @var array<int, string> $xeoOptions
 * @var bool $isEdit
 */

?>

<div class="dboard-surface p-6">
    <div class="flex gap-4">
        <div class="basis-1/2 grow">
            <?php
            $regularSelect = Bleet::select($formModel, 'regularElasticSchemaId')
                    ->optionsData($regularOptions)
                    ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                    ->secondary();
            if ($isEdit === true):
                $regularSelect = $regularSelect->disabled();
            endif;
            echo $regularSelect->render();
            ?>
        </div>
        <div class="basis-1/2 grow">
            <?php
            $xeoSelect = Bleet::select($formModel, 'xeoElasticSchemaId')
                    ->optionsData($xeoOptions)
                    ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                    ->secondary();
            if ($isEdit === true):
                $xeoSelect = $xeoSelect->disabled();
            endif;
            echo $xeoSelect->render();
            ?>
        </div>
    </div>

    <div class="flex gap-4 mt-4">
        <div class="basis-full">
            <?php echo Bleet::textarea($formModel, 'mapping')
                    ->rows(8)
                    ->secondary()
                    ->render();
            ?>
        </div>
    </div>
</div>
