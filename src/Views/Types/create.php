<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\TypeForm;
use Blackcube\Dboard\Widgets\Widgets;
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
 * @var TypeForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Type $type
 * @var \Blackcube\Dboard\Models\Forms\TypeElasticSchemaForm[] $allowedElasticSchemas
 * @var string|null $csrf
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('New type', category: 'dboard-modules'))
                ->backTo($urlGenerator->generate('dboard.types'), $translator->translate('Types', category: 'dboard-modules'))
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.types.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <?php echo $this->render('Types/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'allowedElasticSchemas' => $allowedElasticSchemas,
                ]); ?>

                <div class="mt-6 flex justify-end gap-3">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.types'))
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
