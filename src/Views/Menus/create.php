<?php

declare(strict_types=1);

/**
 * create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\MenuForm;
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
 * @var MenuForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Menu $menu
 * @var string|null $csrf
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $languageQuery
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $hostQuery
 */

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.menus.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.menus'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('New menu', category: 'dboard-content'))
                    ->primary();
                ?>

                <?php echo $this->render('Menus/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'menu' => $menu,
                    'languageQuery' => $languageQuery,
                    'hostQuery' => $hostQuery,
                    'isEdit' => false,
                ]); ?>

                    <div class="flex justify-end gap-4 mt-6">
                        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                            ->url($urlGenerator->generate('dboard.menus'))
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
