<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
                        <?php echo Bleet::toggle($formModel, 'active')
                            ->secondary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-1/4 grow">
                            <?php echo Bleet::input($formModel, 'id')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'name')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-auto flex items-end pb-1">
                            <?php echo Bleet::checkbox($formModel, 'main')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                </div>