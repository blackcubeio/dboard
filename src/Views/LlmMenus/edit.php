<?php

declare(strict_types=1);

/**
 * edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;
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
 * @var LlmMenuForm $formModel
 * @var CurrentRoute $currentRoute
 * @var LlmMenu $llmMenu
 * @var string|null $csrf
 */

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.llmmenus.edit', ['id' => $llmMenu->getId()]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.llmmenus'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('LLM Menu', category: 'dboard-content'))
                    ->primary();
                ?>

                <?php echo $this->render('LlmMenus/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'llmMenu' => $llmMenu,
                    'isEdit' => true,
                ]); ?>

                    <div class="flex justify-end gap-4 mt-6">
                        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                            ->url($urlGenerator->generate('dboard.llmmenus'))
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
