<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\SlugForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var SlugForm $formModel
 * @var CurrentRoute $currentRoute
 */

?>

                <div class="dboard-surface p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle($formModel, 'active')
                            ->secondary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::select($formModel, 'hostId')
                                ->optionsData(SlugForm::getHostOptions())
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::input($formModel, 'path')
                                ->placeholder($translator->translate('e.g. old-path, page/old', category: 'dboard-modules'))
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Technical', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::input($formModel, 'targetUrl')
                                ->placeholder($translator->translate('e.g. https://example.com/new-page', category: 'dboard-modules'))
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::select($formModel, 'httpCode')
                                ->optionsData(SlugForm::getHttpCodeOptions())
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                </div>
