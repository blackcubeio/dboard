<?php

declare(strict_types=1);

/**
 * edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AdministratorForm;
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
 * @var AdministratorForm $formModel
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

// Toolbar buttons
$barButtonClasses = ['!rounded-none', '!shadow-none', 'bg-white'];

$permissionsUrl = $urlGenerator->generate('dboard.administrators.permissions', ['id' => $administrator->getId()]);
$permissionsButton = Bleet::button()
    ->icon('shield-check')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'admin-popover-permissions')
    ->addAttributes(Bleet::drawer()->trigger($permissionsUrl));

$buttonsBar = Bleet::buttonsBar()
    ->addButton($permissionsButton);

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.administrators.edit', ['id' => $administrator->getId()]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.administrators'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('Administrator', category: 'dboard-modules'))
                    ->button($buttonsBar)
                    ->primary();
                ?>

                <?php echo $this->render('Administrators/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'localeOptions' => $localeOptions,
                ]); ?>

                <div class="flex justify-end gap-4 mt-6">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.administrators'))
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
            <?php echo Widgets::popover($translator->translate('Permissions', category: 'dboard-common'))->id('admin-popover-permissions'); ?>
            <?php echo Html::closeTag('form'); ?>
        </main>
