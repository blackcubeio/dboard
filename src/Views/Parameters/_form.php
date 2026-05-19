<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ParameterForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var ParameterForm $formModel
 * @var CurrentRoute $currentRoute
 */

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="flex gap-4">
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'domain')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'domain')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'name')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'name')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Value', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php echo Bleet::label()
                                ->active($formModel, 'value')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::textarea()
                                    ->active($formModel, 'value')
                                    ->rows(6)
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
