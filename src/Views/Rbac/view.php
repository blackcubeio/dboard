<?php

declare(strict_types=1);

/**
 * view.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
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

// Build sorted role list with colors
$roles = [];
foreach ($diff['currentRoles'] as $role) {
    $roles[$role] = 'secondary';
}
foreach ($diff['missingRoles'] as $role) {
    $roles[$role] = 'info';
}
foreach ($diff['obsoleteRoles'] as $role) {
    $roles[$role] = 'danger';
}
ksort($roles);

// Build sorted permission list with colors
$permissions = [];
foreach ($diff['currentPermissions'] as $perm) {
    $permissions[$perm] = 'secondary';
}
foreach ($diff['missingPermissions'] as $perm) {
    $permissions[$perm] = 'info';
}
foreach ($diff['obsoletePermissions'] as $perm) {
    $permissions[$perm] = 'danger';
}
ksort($permissions);

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Bleet::cardHeader()
                ->left(Bleet::a()->url($urlGenerator->generate('dboard.rbac'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                ->title($translator->translate('RBAC — Detail', category: 'dboard-modules'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-6">

                <div class="flex gap-4 mb-6">
                    <?php echo Bleet::badge($translator->translate('Synchronized', category: 'dboard-modules'))->secondary()->render(); ?>
                    <span class="text-sm text-gray-500"><?= $translator->translate('Present in DB and in code', category: 'dboard-modules') ?></span>
                    <?php echo Bleet::badge($translator->translate('To add', category: 'dboard-modules'))->info()->render(); ?>
                    <span class="text-sm text-gray-500"><?= $translator->translate('Present in code, missing from DB', category: 'dboard-modules') ?></span>
                    <?php echo Bleet::badge($translator->translate('To remove', category: 'dboard-modules'))->danger()->render(); ?>
                    <span class="text-sm text-gray-500"><?= $translator->translate('Present in DB, missing from code', category: 'dboard-modules') ?></span>
                </div>

                <?php echo Bleet::hr($translator->translate('Roles ({count})', ['count' => count($roles)], 'dboard-modules'))->secondary(); ?>

                <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach ($roles as $roleName => $color): ?>
                        <?php echo Bleet::badge($roleName)->$color()->render(); ?>
                    <?php endforeach; ?>
                    <?php if (empty($roles)): ?>
                        <span class="text-sm text-gray-400"><?= $translator->translate('No role defined', category: 'dboard-modules') ?></span>
                    <?php endif; ?>
                </div>

                <?php echo Bleet::hr($translator->translate('Permissions ({count})', ['count' => count($permissions)], 'dboard-modules'))->secondary(); ?>

                <div class="flex flex-wrap gap-2">
                    <?php foreach ($permissions as $permName => $color): ?>
                        <?php echo Bleet::badge($permName)->$color()->render(); ?>
                    <?php endforeach; ?>
                    <?php if (empty($permissions)): ?>
                        <span class="text-sm text-gray-400"><?= $translator->translate('No permission defined', category: 'dboard-modules') ?></span>
                    <?php endif; ?>
                </div>

            </div>
        </main>
