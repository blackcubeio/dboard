<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AccountForm;
use Blackcube\Bleet\Bleet;
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
 * @var array<string, string> $localeOptions
 */

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">

                    <div class="flex flex-wrap gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'firstname')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'firstname')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'lastname')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'lastname')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'email')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'email')
                                    ->email()
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'locale')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                    ->active($formModel, 'locale')
                                    ->options($localeOptions)
                                    ->placeholder($translator->translate('-- Browser default --', category: 'dboard-modules'))
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Security', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'password')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'password')
                                    ->password()
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'checkPassword')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'checkPassword')
                                    ->password()
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
