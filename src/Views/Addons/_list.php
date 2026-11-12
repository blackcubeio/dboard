<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array<int, array{id: string, name: string, version: ?string, active: bool, pending: bool, configureRoute: ?string, orphan: bool}> $addons
 */

?>
<?php if (empty($addons) === true): ?>
    <p class="text-sm text-secondary-500 dark:text-secondary-400">
        <?php echo Html::encode($translator->translate('No addon declared.', category: 'dboard-modules')); ?>
    </p>
<?php else: ?>
    <ul class="divide-y divide-secondary-200 dark:divide-white/10">
        <?php foreach ($addons as $addon): ?>
            <?php
            $routeArguments = [
                'id' => $addon['id'],
            ];
            if ($addon['orphan'] === true) {
                $badge = Bleet::badge($translator->translate('Package missing', category: 'dboard-modules'))->danger();
            } elseif ($addon['active'] === false) {
                $badge = Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->secondary();
            } elseif ($addon['pending'] === true) {
                $badge = Bleet::badge($translator->translate('Update available', category: 'dboard-modules'))->warning();
            } else {
                $badge = Bleet::badge($translator->translate('Active', category: 'dboard-modules'))->success();
            }
            $buttonGroup = Bleet::buttonGroup();
            if ($addon['orphan'] === true) {
                $buttonGroup = $buttonGroup->addButton(Bleet::button($translator->translate('Forget', category: 'dboard-modules'))
                    ->icon('trash')
                    ->danger()
                    ->outline()
                    ->xs()
                    ->addAttributes(Bleet::modal()->trigger($urlGenerator->generate('dboard.addons.forget', $routeArguments))));
            } elseif ($addon['active'] === false) {
                $buttonGroup = $buttonGroup->addButton(Bleet::button($translator->translate('Activate', category: 'dboard-common'))
                    ->icon('power')
                    ->success()
                    ->outline()
                    ->xs()
                    ->addAttributes(Bleet::modal()->trigger($urlGenerator->generate('dboard.addons.activate', $routeArguments))));
            } else {
                if ($addon['pending'] === true) {
                    $buttonGroup = $buttonGroup->addButton(Bleet::button($translator->translate('Update', category: 'dboard-modules'))
                        ->icon('arrow-path')
                        ->warning()
                        ->outline()
                        ->xs()
                        ->addAttributes(Bleet::modal()->trigger($urlGenerator->generate('dboard.addons.update', $routeArguments))));
                }
                if ($addon['configureRoute'] !== null) {
                    $buttonGroup = $buttonGroup->addButton(Bleet::a($translator->translate('Configure', category: 'dboard-modules'))
                        ->url($urlGenerator->generate($addon['configureRoute']))
                        ->icon('cog-6-tooth')
                        ->secondary()
                        ->outline()
                        ->xs()
                        ->button());
                }
                $buttonGroup = $buttonGroup->addButton(Bleet::button($translator->translate('Deactivate', category: 'dboard-common'))
                    ->icon('power')
                    ->danger()
                    ->outline()
                    ->xs()
                    ->addAttributes(Bleet::modal()->trigger($urlGenerator->generate('dboard.addons.deactivate', $routeArguments))));
            }
            ?>
            <li class="flex items-center justify-between gap-4 py-3">
                <div class="min-w-0">
                    <span class="font-medium text-secondary-800 dark:text-secondary-200">
                        <?php echo Html::encode($addon['name']); ?>
                    </span>
                    <span class="ml-2 text-xs text-secondary-500 dark:text-secondary-400">
                        <?php echo Html::encode($addon['id']); ?>
                        <?php if ($addon['version'] !== null): ?>
                            &middot; <?php echo Html::encode($addon['version']); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <?php echo $badge->render(); ?>
                    <?php echo $buttonGroup->render(); ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
