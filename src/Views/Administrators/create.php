<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AdministratorForm;
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
 * @var AdministratorForm $formModel
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

$permissionsButton = Bleet::button($translator->translate('Permissions', category: 'dboard-common'))
    ->icon('shield-check')
    ->ghost()
    ->secondary()
    ->disabled();

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('New administrator', category: 'dboard-modules'))
                ->backTo($urlGenerator->generate('dboard.administrators'), $translator->translate('Administrators', category: 'dboard-modules'))
                ->addAction($permissionsButton->render())
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.administrators.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <?php echo $this->render('Administrators/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'localeOptions' => $localeOptions,
                ]); ?>

                <div class="dboard-actions">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.administrators'))
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
