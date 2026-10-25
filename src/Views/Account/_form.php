<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
                            <?php echo Bleet::input($formModel, 'firstname')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'lastname')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'email')
                                ->email()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::select($formModel, 'locale')
                                ->optionsData($localeOptions)
                                ->prompt($translator->translate('-- Browser default --', category: 'dboard-modules'))
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Security', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'password')
                                ->password()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'checkPassword')
                                ->password()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                </div>
