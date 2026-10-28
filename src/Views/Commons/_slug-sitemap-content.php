<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\SitemapForm;
use Blackcube\Dboard\Models\Forms\SlugForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model
 * @var SlugForm $slugForm
 * @var SitemapForm $sitemapForm
 * @var UrlGeneratorInterface $urlGenerator
 * @var string $formAction
 * @var string $slugGeneratorUrl
 * @var string|null $csrf
 */

$hostOptions = SlugForm::getHostOptions();
$frequencyOptions = SitemapForm::getFrequencyOptions();
?>
<div class="p-6">
    <?php echo Html::form()
            ->post($formAction)
            ->csrf($csrf)
            ->noValidate()
            ->open();
    ?>

    <div class="space-y-6">
        <!-- Slug Section -->
        <div>
            <h4 class="text-sm font-semibold text-secondary-900 mb-4">Slug</h4>
            <div class="space-y-4">
                <div>
                    <?php echo Bleet::toggle($slugForm, 'active')
                            ->secondary()
                            ->render();
                    ?>
                </div>
                <div>
                    <?php echo Bleet::label('', $slugForm, 'hostId')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::select($slugForm, 'hostId')
                                ->hideLabel()
                                ->optionsData($hostOptions)
                                ->secondary()
                                ->render();
                        ?>
                    </div>
                </div>

                <div dboard-slug-generator="<?php echo $slugGeneratorUrl; ?>">
                    <?php echo Bleet::label('', $slugForm, 'path')->secondary()->render(); ?>
                    <div class="mt-2 flex gap-2">
                        <div class="flex-1">
                            <?php echo Bleet::input($slugForm, 'path')
                                    ->hideLabel()
                                    ->text()
                                    ->secondary()
                                    ->fieldData([
                                        'slug-generator' => 'target',
                                    ])
                                    ->render();
                            ?>
                        </div>
                        <?php echo Bleet::button()
                                ->icon('arrow-path', 'solid')
                                ->outline()
                                ->secondary()
                                ->addAttributes([
                                    'data-slug-generator' => 'button',
                                    'title' => $translator->translate('Generate slug', category: 'dboard-common'),
                                ])
                                ->render();
                        ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sitemap Section -->
        <div class="border-t border-secondary-200 pt-6">
            <h4 class="text-sm font-semibold text-secondary-900 mb-4">Sitemap</h4>
            <div class="space-y-4">
                <div>
                    <?php echo Bleet::toggle($sitemapForm, 'active')
                            ->secondary()
                            ->render();
                    ?>
                </div>
                <div>
                    <?php echo Bleet::label('', $sitemapForm, 'frequency')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::select($sitemapForm, 'frequency')
                                ->hideLabel()
                                ->optionsData($frequencyOptions)
                                ->secondary()
                                ->render();
                        ?>
                    </div>
                </div>

                <div>
                    <?php echo Bleet::label('', $sitemapForm, 'priority')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::input($sitemapForm, 'priority')
                                ->hideLabel()
                                ->number()
                                ->secondary()
                                ->render();
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="flex gap-4 pt-6 mt-6 border-t border-secondary-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
                ->secondary()
                ->attribute('data-dialog', 'close')
                ->render();
        ?>
        <?php echo Bleet::submitButton($translator->translate('Save', category: 'dboard-common'))
                ->primary()
                ->render();
        ?>
    </div>

    <?php echo Html::closeTag('form'); ?>
</div>
