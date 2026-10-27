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

$permissionsUrl = $urlGenerator->generate('dboard.administrators.permissions', [
    'id' => $administrator->getId(),
]);
$permissionsButton = Bleet::button($translator->translate('Permissions', category: 'dboard-common'))
    ->icon('shield-check')
    ->ghost()
    ->secondary()
    ->addAttributes(Bleet::drawer()->trigger($permissionsUrl));

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($administrator->getName())
                ->subtitle($translator->translate('Administrator', category: 'dboard-modules') . ' #' . $administrator->getId())
                ->backTo($urlGenerator->generate('dboard.administrators'), $translator->translate('Administrators', category: 'dboard-modules'))
                ->addAction($permissionsButton->render())
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.administrators.edit', [
                    'id' => $administrator->getId(),
                ]))
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

                <div class="mt-6 flex justify-end gap-3">
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
