<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
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

<div class="bg-white rounded-b-lg shadow-lg p-6">
    <div class="flex gap-4">
        <div class="basis-1/2 grow">
            <?php echo Bleet::label()
                    ->active($formModel, 'regularElasticSchemaId')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php
                $regularSelect = Bleet::select()
                        ->active($formModel, 'regularElasticSchemaId')
                        ->options($regularOptions)
                        ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                        ->primary();
                if ($isEdit) {
                    $regularSelect = $regularSelect->disabled();
                }
                echo $regularSelect->render();
                ?>
            </div>
        </div>
        <div class="basis-1/2 grow">
            <?php echo Bleet::label()
                    ->active($formModel, 'xeoElasticSchemaId')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php
                $xeoSelect = Bleet::select()
                        ->active($formModel, 'xeoElasticSchemaId')
                        ->options($xeoOptions)
                        ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                        ->primary();
                if ($isEdit) {
                    $xeoSelect = $xeoSelect->disabled();
                }
                echo $xeoSelect->render();
                ?>
            </div>
        </div>
    </div>

    <div class="flex gap-4 mt-4">
        <div class="basis-full">
            <?php echo Bleet::label()
                    ->active($formModel, 'mapping')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::textarea()
                        ->active($formModel, 'mapping')
                        ->rows(8)
                        ->primary()
                        ->render();
                ?>
            </div>
        </div>
    </div>
</div>
