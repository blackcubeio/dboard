<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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

                <div class="dboard-surface p-6">
                    <div class="flex gap-4">
                        <div class="basis-1/3 grow">
                            <?php echo Bleet::input($formModel, 'domain')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-2/3 grow">
                            <?php echo Bleet::input($formModel, 'name')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Value', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php echo Bleet::textarea($formModel, 'value')
                                ->rows(6)
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                </div>
