<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ParameterForm;
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
 * @var ParameterForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Parameter $parameter
 * @var string|null $csrf
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('New parameter', category: 'dboard-modules'))
                ->backTo($urlGenerator->generate('dboard.parameters'), $translator->translate('Parameters', category: 'dboard-modules'))
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.parameters.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <?php echo $this->render('Parameters/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'isNew' => true,
                    'currentRoute' => $currentRoute,
                ]); ?>

                <div class="mt-6 flex justify-end gap-3">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.parameters'))
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
