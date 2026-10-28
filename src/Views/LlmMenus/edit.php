<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;
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
 * @var LlmMenuForm $formModel
 * @var CurrentRoute $currentRoute
 * @var LlmMenu $llmMenu
 * @var string|null $csrf
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($llmMenu->getName())
                ->subtitle($translator->translate('LLM Menu', category: 'dboard-content') . ' #' . $llmMenu->getId())
                ->backTo($urlGenerator->generate('dboard.llmmenus'), $translator->translate('LLM Menus', category: 'dboard-content'))
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.llmmenus.edit', [
                    'id' => $llmMenu->getId(),
                ]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <?php echo $this->render('LlmMenus/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'llmMenu' => $llmMenu,
                    'isEdit' => true,
                ]); ?>

                    <div class="dboard-actions">
                        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                            ->url($urlGenerator->generate('dboard.llmmenus'))
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
