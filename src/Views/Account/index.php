<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AccountForm;
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
 * @var AccountForm $formModel
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

$passkeysUrl = $urlGenerator->generate('dboard.account.passkeys.init', [
    'id' => $administrator->getId(),
]);
$passkeysButton = Bleet::button($translator->translate('Passkeys', category: 'dboard-common'))
    ->icon('finger-print')
    ->ghost()
    ->secondary()
    ->addAttributes(Bleet::drawer()->trigger($passkeysUrl));

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('My account', category: 'dboard-modules'))
                ->subtitle($administrator->getName())
                ->backTo($urlGenerator->generate('dboard.dashboard'), $translator->translate('Dashboard', category: 'dboard-common'))
                ->addAction($passkeysButton->render())
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.account', [
                    'id' => $administrator->getId(),
                ]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <?php echo $this->render('Account/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'localeOptions' => $localeOptions,
                ]); ?>

                <div class="mt-6 flex justify-end gap-3">
                    <?php echo Bleet::a($translator->translate('Dashboard', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.dashboard'))
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
