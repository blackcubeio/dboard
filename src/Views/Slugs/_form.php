<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
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

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle()
                            ->active($formModel, 'active')
                            ->primary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'hostId')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                    ->active($formModel, 'hostId')
                                    ->options(SlugForm::getHostOptions())
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'path')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'path')
                                    ->placeholder($translator->translate('e.g. old-path, page/old', category: 'dboard-modules'))
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Technical', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'targetUrl')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'targetUrl')
                                    ->placeholder($translator->translate('e.g. https://example.com/new-page', category: 'dboard-modules'))
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'httpCode')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                    ->active($formModel, 'httpCode')
                                    ->options(SlugForm::getHttpCodeOptions())
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
