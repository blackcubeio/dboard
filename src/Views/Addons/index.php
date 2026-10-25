<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var array<string, array<string, mixed>> $addons
 */

?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Bleet::cardHeader()
                ->icon('puzzle-piece')
                ->title($translator->translate('Addons', category: 'dboard-modules'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-4">
                <?php if (empty($addons) === true): ?>
                    <p class="text-secondary-500">
                        <?php echo Html::encode($translator->translate('No addon declared.', category: 'dboard-modules')); ?>
                    </p>
                <?php else: ?>
                    <ul class="divide-y divide-secondary-200">
                        <?php foreach ($addons as $name => $config): ?>
                            <?php $configRoute = $config['configRoute'] ?? null; ?>
                            <li class="flex items-center justify-between py-3">
                                <span class="font-medium text-secondary-800">
                                    <?php echo Html::encode($name); ?>
                                </span>
                                <?php if ($configRoute !== null): ?>
                                    <?php echo Bleet::a($translator->translate('Configure', category: 'dboard-common'), $urlGenerator->generate($configRoute))
                                        ->button()
                                        ->icon('cog-6-tooth')
                                        ->primary()
                                        ->render();
                                    ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </main>
