<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var array $diff
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var CurrentRoute $currentRoute
 */

$roles = [];
foreach ($diff['currentRoles'] as $role):
    $roles[$role] = 'secondary';
endforeach;
foreach ($diff['missingRoles'] as $role):
    $roles[$role] = 'info';
endforeach;
foreach ($diff['obsoleteRoles'] as $role):
    $roles[$role] = 'danger';
endforeach;
ksort($roles);

$permissions = [];
foreach ($diff['currentPermissions'] as $perm):
    $permissions[$perm] = 'secondary';
endforeach;
foreach ($diff['missingPermissions'] as $perm):
    $permissions[$perm] = 'info';
endforeach;
foreach ($diff['obsoletePermissions'] as $perm):
    $permissions[$perm] = 'danger';
endforeach;
ksort($permissions);

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('RBAC — Detail', category: 'dboard-modules'))
                ->backTo($urlGenerator->generate('dboard.rbac'), $translator->translate('RBAC', category: 'dboard-modules'))
                ->render();
            ?>

            <div class="dboard-surface p-6">

                <div class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-2">
                    <?php echo Bleet::badge($translator->translate('Synchronized', category: 'dboard-modules'))->secondary()->render(); ?>
                    <span class="text-sm text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Present in DB and in code', category: 'dboard-modules'); ?></span>
                    <?php echo Bleet::badge($translator->translate('To add', category: 'dboard-modules'))->info()->render(); ?>
                    <span class="text-sm text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Present in code, missing from DB', category: 'dboard-modules'); ?></span>
                    <?php echo Bleet::badge($translator->translate('To remove', category: 'dboard-modules'))->danger()->render(); ?>
                    <span class="text-sm text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Present in DB, missing from code', category: 'dboard-modules'); ?></span>
                </div>

                <?php echo Bleet::hr($translator->translate('Roles ({count})', [
                    'count' => count($roles),
                ], 'dboard-modules'))->secondary(); ?>

                <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach ($roles as $roleName => $color): ?>
                        <?php echo Bleet::badge($roleName)->$color()->render(); ?>
                    <?php endforeach; ?>
                    <?php if (empty($roles) === true): ?>
                        <span class="text-sm text-secondary-400"><?php echo $translator->translate('No role defined', category: 'dboard-modules'); ?></span>
                    <?php endif; ?>
                </div>

                <?php echo Bleet::hr($translator->translate('Permissions ({count})', [
                    'count' => count($permissions),
                ], 'dboard-modules'))->secondary(); ?>

                <div class="flex flex-wrap gap-2">
                    <?php foreach ($permissions as $permName => $color): ?>
                        <?php echo Bleet::badge($permName)->$color()->render(); ?>
                    <?php endforeach; ?>
                    <?php if (empty($permissions) === true): ?>
                        <span class="text-sm text-secondary-400"><?php echo $translator->translate('No permission defined', category: 'dboard-modules'); ?></span>
                    <?php endif; ?>
                </div>

            </div>
        </main>
