<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\TypeElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\TypeForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var TypeForm $formModel
 * @var TypeElasticSchemaForm[] $allowedElasticSchemas
 * @var CurrentRoute $currentRoute
 */

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::input($formModel, 'name')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::select($formModel, 'handler')
                                ->prompt($translator->translate('-- Select a handler --', category: 'dboard-modules'))
                                ->optionsData($ssrRoutes)
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Options', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-auto">
                            <?php echo Bleet::checkbox($formModel, 'contentAllowed')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-auto">
                            <?php echo Bleet::checkbox($formModel, 'tagAllowed')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php if (empty($allowedElasticSchemas) === false): ?>
                    <?php echo Bleet::hr($translator->translate('Associated block types', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex flex-wrap gap-4">
                        <?php foreach ($allowedElasticSchemas as $i => $typeElasticSchema): ?>
                        <div class="basis-auto">
                            <?php
                            echo Bleet::input($typeElasticSchema, '[' . $i . ']typeId')
                                ->hidden()
                                ->render();
                            echo Bleet::input($typeElasticSchema, '[' . $i . ']elasticSchemaId')
                                ->hidden()
                                ->render();
                            echo Bleet::checkbox($typeElasticSchema, '[' . $i . ']allowed')
                                ->label($typeElasticSchema->getElasticSchemaName())
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
