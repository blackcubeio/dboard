<?php

declare(strict_types=1);

/**
 * _permissions-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var string[] $roles
 * @var string[] $userRolesById
 * @var string[] $userPermissionsById
 * @var string[] $userAssignmentsById
 * @var bool $canAssign
 * @var string $formAction
 * @var string|null $csrf
 */

?>
<div class="p-6">
    <?php echo Html::form()
            ->post($formAction)
            ->csrf($csrf)
            ->noValidate()
            ->open();
    ?>

    <div class="mb-4">
        <?php echo Bleet::h3($administrator->getName())->render(); ?>
        <?php echo Bleet::p($administrator->getEmail())->secondary()->render(); ?>
    </div>

    <div class="space-y-4">
        <?php foreach ($roles as $roleName): ?>
            <?php
            $roleHasAssignment = in_array($roleName, $userAssignmentsById, true);
            $roleHasRole = in_array($roleName, $userRolesById, true);
            $roleIsInherited = $roleHasRole && !$roleHasAssignment;
            $rolePermissions = Rbac::getPermissionsByRole($roleName);
            ?>
            <div class="border border-secondary-200 rounded-lg overflow-hidden">
                <div class="bg-secondary-50 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <?php echo Bleet::svg()->outline('shield-check')->addClass('size-5', 'text-secondary-600')->render(); ?>
                        <span class="font-medium text-secondary-900"><?php echo Html::encode(Rbac::extractRole($roleName)); ?></span>
                        <?php if ($roleIsInherited): ?>
                            <?php echo Bleet::badge($translator->translate('Inherited', category: 'dboard-modules'))->info()->xs()->render(); ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($canAssign): ?>
                        <?php echo Bleet::toggle()
                            ->name('roles[' . $roleName . ']')
                            ->id('role-' . Rbac::rbac2Id($roleName))
                            ->value('1')
                            ->checked($roleHasRole)
                            ->secondary()
                            ->render();
                        ?>
                    <?php else: ?>
                        <?php echo Bleet::toggle()
                            ->id('role-' . Rbac::rbac2Id($roleName))
                            ->checked($roleHasRole)
                            ->disabled()
                            ->secondary()
                            ->render();
                        ?>
                    <?php endif; ?>
                </div>

                <?php if (count($rolePermissions) > 0): ?>
                    <div class="divide-y divide-secondary-100">
                        <?php foreach ($rolePermissions as $permName): ?>
                            <?php
                            $permHasAssignment = in_array($permName, $userAssignmentsById, true);
                            $permHasPerm = in_array($permName, $userPermissionsById, true);
                            $permIsInherited = $permHasPerm && !$permHasAssignment;
                            ?>
                            <div class="px-4 py-2 flex items-center justify-between bg-white hover:bg-secondary-25">
                                <div class="flex items-center gap-2 pl-6">
                                    <?php echo Bleet::svg()->outline('key')->addClass('size-4', 'text-secondary-400')->render(); ?>
                                    <span class="text-sm text-secondary-700"><?php echo Html::encode(Rbac::extractPermission($permName)); ?></span>
                                    <?php if ($permIsInherited): ?>
                                        <?php echo Bleet::badge($translator->translate('Inherited', category: 'dboard-modules'))->info()->xs()->render(); ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($canAssign): ?>
                                    <?php echo Bleet::toggle()
                                        ->name('permissions[' . $permName . ']')
                                        ->id('permission-' . Rbac::rbac2Id($permName))
                                        ->value('1')
                                        ->checked($permHasPerm)
                                        ->secondary()
                                        ->render();
                                    ?>
                                <?php else: ?>
                                    <?php echo Bleet::toggle()
                                        ->id('permission-' . Rbac::rbac2Id($permName))
                                        ->checked($permHasPerm)
                                        ->disabled()
                                        ->secondary()
                                        ->render();
                                    ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$canAssign): ?>
        <div class="mt-4">
            <?php echo Bleet::alert($translator->translate('You cannot modify your own permissions.', category: 'dboard-modules'))->warning()->render(); ?>
        </div>
    <?php endif; ?>

    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
                ->secondary()
                ->attribute('data-drawer', 'close')
                ->render();
        ?>
        <?php if ($canAssign): ?>
            <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))
                    ->submit()
                    ->primary()
                    ->render();
            ?>
        <?php endif; ?>
    </div>

    <?php echo Html::closeTag('form'); ?>
</div>
