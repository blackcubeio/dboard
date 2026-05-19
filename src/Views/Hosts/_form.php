<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\HostForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var Administrator $administrator
 * @var HostForm $formModel
 * @var CurrentRoute $currentRoute
 * @var bool $isProtected
 */

$isProtected = $isProtected ?? false;

?>

<div class="bg-white rounded-b-lg shadow-lg p-6">
    <div class="basis-full mb-4">
        <?php
        $toggle = Bleet::toggle()
                ->active($formModel, 'active')
                ->primary();
        if ($isProtected) {
            $toggle = $toggle->disabled(true);
        }
        echo $toggle->render();
        ?>
    </div>

    <div class="flex gap-4">
        <div class="basis-full">
            <?php echo Bleet::label()
                    ->active($formModel, 'name')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php
                $nameInput = Bleet::input()
                        ->active($formModel, 'name')
                        ->placeholder('ex: localhost, blackcube.io')
                        ->primary();
                if ($isProtected) {
                    $nameInput = $nameInput->disabled(true);
                }
                echo $nameInput->render();
                ?>
            </div>
        </div>
    </div>

    <div class="flex gap-4 mt-4">
        <div class="basis-1/2 grow">
            <?php echo Bleet::label()
                    ->active($formModel, 'siteName')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::input()
                        ->active($formModel, 'siteName')
                        ->primary()
                        ->render();
                ?>
            </div>
        </div>
        <div class="basis-1/2 grow">
            <?php echo Bleet::label()
                    ->active($formModel, 'siteAlternateName')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::input()
                        ->active($formModel, 'siteAlternateName')
                        ->primary()
                        ->render();
                ?>
            </div>
        </div>
    </div>

    <div class="flex gap-4 mt-4">
        <div class="basis-full">
            <?php echo Bleet::label()
                    ->active($formModel, 'siteDescription')
                    ->primary()
                    ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::textarea()
                        ->active($formModel, 'siteDescription')
                        ->rows(3)
                        ->primary()
                        ->render();
                ?>
            </div>
        </div>
    </div>
</div>