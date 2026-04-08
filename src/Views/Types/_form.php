<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
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
                            <?php echo Bleet::label()
                                ->active($formModel, 'name')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'name')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'handler')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                    ->active($formModel, 'handler')
                                    ->placeholder($translator->translate('-- Select a handler --', category: 'dboard-modules'))
                                    ->options($ssrRoutes)
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Options', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-auto">
                            <?php echo Bleet::checkbox()
                                ->active($formModel, 'contentAllowed')
                                ->primary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-auto">
                            <?php echo Bleet::checkbox()
                                ->active($formModel, 'tagAllowed')
                                ->primary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php if (!empty($allowedElasticSchemas)): ?>
                    <?php echo Bleet::hr($translator->translate('Associated block types', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex flex-wrap gap-4">
                        <?php foreach ($allowedElasticSchemas as $i => $typeElasticSchema): ?>
                        <div class="basis-auto">
                            <?php
                            echo Bleet::input()
                                ->type('hidden')
                                ->active($typeElasticSchema, '[' . $i . ']typeId')
                                ->render();
                            echo Bleet::input()
                                ->type('hidden')
                                ->active($typeElasticSchema, '[' . $i . ']elasticSchemaId')
                                ->render();
                            echo Bleet::checkbox()
                                ->active($typeElasticSchema, '[' . $i . ']allowed')
                                ->label($typeElasticSchema->getElasticSchemaName())
                                ->primary()
                                ->render();
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
