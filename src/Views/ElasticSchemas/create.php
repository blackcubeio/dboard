<?php

declare(strict_types=1);

/**
 * create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ElasticSchemaForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var ElasticSchemaForm $formModel
 * @var CurrentRoute $currentRoute
 * @var ElasticSchema $elasticSchema
 * @var \Blackcube\Dboard\Models\Forms\ElasticSchemaTypeForm[] $allowedTypes
 * @var string|null $csrf
 */

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.elasticschemas.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.elasticschemas'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('New elastic schema', category: 'dboard-modules'))
                    ->primary();
                ?>

                <?php echo $this->render('ElasticSchemas/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'allowedTypes' => $allowedTypes,
                    'elasticSchema' => $elasticSchema ?? null,
                ]); ?>

                <div class="flex justify-end gap-4 mt-6">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.elasticschemas'))
                        ->icon('x-mark')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))
                        ->icon('check')
                        ->submit()
                        ->primary()
                        ->render();
                    ?>
                </div>
            <?php echo Html::closeTag('form'); ?>
        </main>
