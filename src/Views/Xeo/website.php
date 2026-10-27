<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Dboard\Widgets\Widgets;
use Blackcube\Bleet\Bleet;
use Blackcube\BridgeFormModel\BridgeFormModel;
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
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('Website — {host}', [
                    'host' => $host->getName(),
                ], 'dboard-content'))
                ->backTo($urlGenerator->generate('dboard.xeo.website'), $translator->translate('Website', category: 'dboard-common'))
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.xeo.website.edit', [
                    'hostId' => $host->getId(),
                ]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <div class="dboard-surface p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle($formModel, 'active')
                            ->secondary()
                            ->render();
                        ?>
                    </div>

                    <div class="space-y-4">
                        <?php
                        $adminTemplate = ElasticFieldRenderer::getAdminView($globalXeo->getElasticSchemaId());
                        if ($adminTemplate !== false):
                            $formClass = get_class($formModel);
                            $elasticAttributes = [];
                            foreach ($formModel->getProperties() as $propertyName => $property):
                                if ($property->isElastic($formClass) === true):
                                    $elasticAttributes[] = $propertyName;
                                endif;
                            endforeach;
                            echo $this->render($adminTemplate, [
                                'blocId' => null,
                                'blocForm' => $formModel,
                                'attributes' => $elasticAttributes,
                                'fileEndpoints' => $fileEndpoints,
                            ]);
                        else:
                            echo ElasticFieldRenderer::renderAll($formModel, '', $fileEndpoints);
                        endif;
                        ?>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.xeo.website'))
                        ->icon('x-mark')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::submitButton($translator->translate('Save', category: 'dboard-common'))
                        ->icon('check')
                        ->accent()
                        ->render();
                    ?>
                </div>
            <?php echo Html::closeTag('form'); ?>
        </main>
