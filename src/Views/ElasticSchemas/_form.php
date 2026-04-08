<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\ElasticSchemaTypeForm;
use Blackcube\Dboard\Widgets\SchemaEditor;
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

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="basis-full mb-4">
                        <?php
                        $toggle = Bleet::toggle()
                            ->active($formModel, 'active')
                            ->primary();
                        if ($isBuiltin) {
                            $toggle = $toggle->disabled();
                        }
                        echo $toggle->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'name')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php
                                $nameInput = Bleet::input()
                                    ->active($formModel, 'name')
                                    ->primary();
                                if ($isBuiltin) {
                                    $nameInput = $nameInput->disabled();
                                }
                                echo $nameInput->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'kind')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php
                                $kindSelect = Bleet::select()
                                    ->active($formModel, 'kind')
                                    ->options($kindOptions)
                                    ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                                    ->primary();
                                if ($isBuiltin) {
                                    $kindSelect = $kindSelect->disabled();
                                }
                                echo $kindSelect->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="basis-full">
                            <?php echo Bleet::label()
                                ->active($formModel, 'view')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php
                                $viewInput = Bleet::input()
                                    ->active($formModel, 'view')
                                    ->primary();
                                if ($isBuiltin) {
                                    $viewInput = $viewInput->disabled();
                                }
                                echo $viewInput->render();
                                ?>
                            </div>
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
                                    <?php echo $hasAdminView
                                        ? Bleet::badge($translator->translate('active', category: 'dboard-modules'))->success()->render()
                                        : Bleet::badge($translator->translate('auto', category: 'dboard-modules'))->secondary()->render(); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('JSON Schema', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php echo Bleet::label()
                                ->active($formModel, 'schema')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php
                                $schemaEditor = (new SchemaEditor())
                                    ->active($formModel, 'schema')
                                    ->language('fr')
                                    ->primary();
                                echo $schemaEditor->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Markdown Mapping', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php echo Bleet::label()
                                ->active($formModel, 'mdMapping')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php
                                $mdMappingTextarea = Bleet::textarea()
                                    ->active($formModel, 'mdMapping')
                                    ->rows(8)
                                    ->primary();
                                if ($isBuiltin) {
                                    $mdMappingTextarea = $mdMappingTextarea->disabled();
                                }
                                echo $mdMappingTextarea->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($allowedTypes)): ?>
                    <?php echo Bleet::hr($translator->translate('Associated types', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex flex-wrap gap-4">
                        <?php foreach ($allowedTypes as $i => $allowedType): ?>
                        <div class="basis-auto">
                            <?php
                            echo Bleet::input()
                                ->type('hidden')
                                ->active($allowedType, '[' . $i . ']typeId')
                                ->render();
                            echo Bleet::input()
                                ->type('hidden')
                                ->active($allowedType, '[' . $i . ']elasticSchemaId')
                                ->render();
                            $checkbox = Bleet::checkbox()
                                ->active($allowedType, '[' . $i . ']allowed')
                                ->label($allowedType->getTypeName())
                                ->primary();
                            if ($isBuiltin) {
                                $checkbox = $checkbox->disabled();
                            }
                            echo $checkbox->render();
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
