<?php

declare(strict_types=1);

/**
 * robots.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Bleet\Bleet;
use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Host $host
 * @var GlobalXeo $globalXeo
 * @var BridgeFormModel $formModel
 * @var array $fileEndpoints
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.xeo.robots.edit', ['hostId' => $host->getId()]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.xeo.robots'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('robots.txt — {host}', ['host' => $host->getName()], 'dboard-content'))
                    ->primary();
                ?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle()
                            ->active($formModel, 'active')
                            ->primary()
                            ->render();
                        ?>
                    </div>

                    <div class="space-y-4">
                        <?php
                        $adminTemplate = ElasticFieldRenderer::getAdminView($globalXeo->getElasticSchemaId());
                        if ($adminTemplate !== false) {
                            $formClass = get_class($formModel);
                            $elasticProperties = array_filter($formModel->getProperties(), function ($property) use ($formClass) {
                                return $property->isElastic($formClass);
                            });
                            $elasticAttributes = array_keys($elasticProperties);
                            echo $this->render($adminTemplate, [
                                'blocId' => null,
                                'blocForm' => $formModel,
                                'attributes' => $elasticAttributes,
                                'fileEndpoints' => $fileEndpoints,
                            ]);
                        } else {
                            echo ElasticFieldRenderer::renderAll($formModel, '', $fileEndpoints);
                        }
                        ?>
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.xeo.robots'))
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
