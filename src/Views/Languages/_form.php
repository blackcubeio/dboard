<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Language;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LanguageForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var Administrator $administrator
 * @var LanguageForm $formModel
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
                        <div class="basis-1/4 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'id')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'id')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
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
                        <div class="basis-auto flex items-end pb-1">
                            <?php echo Bleet::checkbox()
                                ->active($formModel, 'main')
                                ->primary()
                                ->render();
                            ?>
                        </div>
                    </div>
                </div>