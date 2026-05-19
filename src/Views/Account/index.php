<?php

declare(strict_types=1);

/**
 * index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AccountForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
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

// Toolbar buttons
$barButtonClasses = ['!rounded-none', '!shadow-none', 'bg-white'];

$passkeysUrl = $urlGenerator->generate('dboard.account.passkeys.init', ['id' => $administrator->getId()]);
$passkeysButton = Bleet::button()
    ->icon('finger-print')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'account-popover-passkeys')
    ->addAttributes(Bleet::drawer()->trigger($passkeysUrl));

$buttonsBar = Bleet::buttonsBar()
    ->addButton($passkeysButton);

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.account', ['id' => $administrator->getId()]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.dashboard'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('My account', category: 'dboard-modules'))
                    ->button($buttonsBar)
                    ->primary();
                ?>

                <?php echo $this->render('Account/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'localeOptions' => $localeOptions,
                ]); ?>

                <div class="flex justify-end gap-4 mt-6">
                    <?php echo Bleet::a($translator->translate('Dashboard', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.dashboard'))
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
            <?php echo Widgets::popover($translator->translate('Passkeys', category: 'dboard-common'))->id('account-popover-passkeys'); ?>
            <?php echo Html::closeTag('form'); ?>
        </main>
