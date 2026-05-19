<?php

declare(strict_types=1);

/**
 * RbacInitializer.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Components\Rbac;
use ReflectionClass;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final class RbacInitializer
{
    /**
     * @param ManagerInterface $manager
     * @param ItemsStorageInterface $itemsStorage
     * @param array<class-string> $rbacClasses
     */
    public function __construct(
        private ManagerInterface $manager,
        private ItemsStorageInterface $itemsStorage,
        private DboardConfig $dboardConfig,
    ) {}

    public function initialize(): void
    {
        $roles = [];
        $permissions = [];

        foreach ($this->dboardConfig->rbacClasses as $rbacClass) {
            $reflection = new ReflectionClass($rbacClass);

            foreach ($reflection->getConstants() as $name => $value) {
                if (str_starts_with($name, 'PERMISSION_')) {
                    $permissions[] = $value;
                    if ($this->manager->getPermission($value) === null) {
                        $permission = new Permission($value);
                        $this->manager->addPermission($permission);
                    }
                } elseif (str_starts_with($name, 'ROLE_')) {
                    $roles[] = $value;
                    if ($this->manager->getRole($value) === null) {
                        $role = new Role($value);
                        $this->manager->addRole($role);
                    }
                }
            }
        }

        // Build hierarchy: ROLE_X_MANAGER inherits all X:* permissions
        foreach ($roles as $role) {
            if (str_contains($role, ':')) {
                [$roleType] = explode(':', $role);
                foreach ($permissions as $permission) {
                    if (str_contains($permission, ':')) {
                        [$permissionType] = explode(':', $permission);
                        if ($roleType === $permissionType) {
                            if (!$this->manager->hasChild($role, $permission)) {
                                $this->manager->addChild($role, $permission);
                            }
                        }
                    }
                }
            } elseif ($role === Rbac::ROLE_ADMIN) {
                // ROLE_ADMIN inherits all other roles
                foreach ($roles as $innerRole) {
                    if ($innerRole !== Rbac::ROLE_ADMIN && !$this->manager->hasChild($role, $innerRole)) {
                        $this->manager->addChild($role, $innerRole);
                    }
                }
            }
        }

        // Clean obsolete roles
        foreach ($this->itemsStorage->getRoles() as $existingRole) {
            if (!in_array($existingRole->getName(), $roles, true)) {
                $this->manager->removeRole($existingRole->getName());
            }
        }

        // Clean obsolete permissions
        foreach ($this->itemsStorage->getPermissions() as $existingPermission) {
            if (!in_array($existingPermission->getName(), $permissions, true)) {
                $this->manager->removePermission($existingPermission->getName());
            }
        }
    }

    /**
     * Extracts all roles and permissions from the configured RBAC classes via reflection.
     *
     * @return array{roles: string[], permissions: string[]}
     */
    private function extractFromCode(): array
    {
        $roles = [];
        $permissions = [];

        foreach ($this->dboardConfig->rbacClasses as $rbacClass) {
            $reflection = new ReflectionClass($rbacClass);

            foreach ($reflection->getConstants() as $name => $value) {
                if (str_starts_with($name, 'PERMISSION_')) {
                    $permissions[] = $value;
                } elseif (str_starts_with($name, 'ROLE_')) {
                    $roles[] = $value;
                }
            }
        }

        return ['roles' => $roles, 'permissions' => $permissions];
    }

    /**
     * Returns whether the DB RBAC state is in sync with the code.
     *
     * @return bool True if all roles/permissions match, false if any diff exists
     */
    public function isInSync(): bool
    {
        $diff = $this->getDiff();
        return empty($diff['missingRoles'])
            && empty($diff['missingPermissions'])
            && empty($diff['obsoleteRoles'])
            && empty($diff['obsoletePermissions']);
    }

    /**
     * Returns the diff between code and DB RBAC state.
     *
     * @return array{
     *     currentRoles: string[],
     *     currentPermissions: string[],
     *     missingRoles: string[],
     *     missingPermissions: string[],
     *     obsoleteRoles: string[],
     *     obsoletePermissions: string[]
     * }
     */
    public function getDiff(): array
    {
        $code = $this->extractFromCode();

        $dbRoles = array_map(
            fn($role) => $role->getName(),
            $this->itemsStorage->getRoles()
        );
        $dbPermissions = array_map(
            fn($perm) => $perm->getName(),
            $this->itemsStorage->getPermissions()
        );

        return [
            'currentRoles' => array_values(array_intersect($code['roles'], $dbRoles)),
            'currentPermissions' => array_values(array_intersect($code['permissions'], $dbPermissions)),
            'missingRoles' => array_values(array_diff($code['roles'], $dbRoles)),
            'missingPermissions' => array_values(array_diff($code['permissions'], $dbPermissions)),
            'obsoleteRoles' => array_values(array_diff($dbRoles, $code['roles'])),
            'obsoletePermissions' => array_values(array_diff($dbPermissions, $code['permissions'])),
        ];
    }
}
