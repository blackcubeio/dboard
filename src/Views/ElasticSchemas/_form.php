<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\ElasticSchemaTypeForm;
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
 * @var ElasticSchemaTypeForm[] $allowedTypes
 * @var ElasticSchema|null $elasticSchema
 */

$kindOptions = ElasticSchemaForm::getKindOptions();
$isBuiltin = $formModel->isBuiltin();

?>

                <div class="dboard-surface p-6">
                    <div class="basis-full mb-4">
                        <?php
                        $toggle = Bleet::toggle($formModel, 'active')
                            ->secondary();
                        if ($isBuiltin === true):
                            $toggle = $toggle->disabled();
                        endif;
                        echo $toggle->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php
                            $nameInput = Bleet::input($formModel, 'name')
                                ->secondary();
                            if ($isBuiltin === true):
                                $nameInput = $nameInput->disabled();
                            endif;
                            echo $nameInput->render();
                            ?>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php
                            $kindSelect = Bleet::select($formModel, 'kind')
                                ->optionsData($kindOptions)
                                ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                                ->secondary();
                            if ($isBuiltin === true):
                                $kindSelect = $kindSelect->disabled();
                            endif;
                            echo $kindSelect->render();
                            ?>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="basis-full">
                            <?php
                            $viewInput = Bleet::input($formModel, 'view')
                                ->secondary();
                            if ($isBuiltin === true):
                                $viewInput = $viewInput->disabled();
                            endif;
                            echo $viewInput->render();
                            ?>
                            <?php
                            $elasticSchema = $elasticSchema ?? null;
                            if ($elasticSchema !== null && $elasticSchema->getId() !== null):
                                $aliasPath = ElasticFieldRenderer::getAdminViewAlias($elasticSchema);
                                $hasAdminView = ElasticFieldRenderer::getAdminView($elasticSchema) !== false;
                            ?>
                                <p class="text-xs text-secondary-500 mt-1">
                                    <?php if ($aliasPath !== null): ?>
                                        <code><?php echo Html::encode($aliasPath); ?></code>
                                    <?php endif; ?>
                                    <?php echo $hasAdminView === true
                                        ? Bleet::badge($translator->translate('active', category: 'dboard-modules'))->success()->render()
                                        : Bleet::badge($translator->translate('auto', category: 'dboard-modules'))->secondary()->render(); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('JSON Schema', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php
                            $schemaEditor = Bleet::jsonEditor($formModel, 'schema')
                                ->language('fr')
                                ->addClass('dboard-schema-editor');
                            echo $schemaEditor->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Markdown Mapping', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php
                            $mdMappingTextarea = Bleet::textarea($formModel, 'mdMapping')
                                ->rows(8)
                                ->secondary();
                            if ($isBuiltin === true):
                                $mdMappingTextarea = $mdMappingTextarea->disabled();
                            endif;
                            echo $mdMappingTextarea->render();
                            ?>
                        </div>
                    </div>

                    <?php if (empty($allowedTypes) === false): ?>
                    <?php echo Bleet::hr($translator->translate('Associated types', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex flex-wrap gap-4">
                        <?php foreach ($allowedTypes as $i => $allowedType): ?>
                        <div class="basis-auto">
                            <?php
                            echo Bleet::input($allowedType, '[' . $i . ']typeId')
                                ->hidden()
                                ->render();
                            echo Bleet::input($allowedType, '[' . $i . ']elasticSchemaId')
                                ->hidden()
                                ->render();
                            $checkbox = Bleet::checkbox($allowedType, '[' . $i . ']allowed')
                                ->label($allowedType->getTypeName())
                                ->secondary();
                            if ($isBuiltin === true):
                                $checkbox = $checkbox->disabled();
                            endif;
                            echo $checkbox->render();
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
