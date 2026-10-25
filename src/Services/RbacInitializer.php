<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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

/**
 * Writes the roles and permissions of the declared RBAC classes in the RBAC storage and reports the differences.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RbacInitializer
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

            foreach ($reflection->getConstants() as $name => $rbacItemName) {
                if (str_starts_with($name, 'PERMISSION_') === true) {
                    $permissions[] = $rbacItemName;
                    if ($this->manager->getPermission($rbacItemName) === null) {
                        $permission = new Permission($rbacItemName);
                        $this->manager->addPermission($permission);
                    }
                } elseif (str_starts_with($name, 'ROLE_') === true) {
                    $roles[] = $rbacItemName;
                    if ($this->manager->getRole($rbacItemName) === null) {
                        $role = new Role($rbacItemName);
                        $this->manager->addRole($role);
                    }
                }
            }
        }

        foreach ($roles as $role) {
            if (str_contains($role, ':') === true) {
                [$roleType] = explode(':', $role);
                foreach ($permissions as $permission) {
                    if (str_contains($permission, ':') === true) {
                        [$permissionType] = explode(':', $permission);
                        if ($roleType === $permissionType) {
                            if ($this->manager->hasChild($role, $permission) === false) {
                                $this->manager->addChild($role, $permission);
                            }
                        }
                    }
                }
            } elseif ($role === Rbac::ROLE_ADMIN) {
                foreach ($roles as $innerRole) {
                    if ($innerRole !== Rbac::ROLE_ADMIN && $this->manager->hasChild($role, $innerRole) === false) {
                        $this->manager->addChild($role, $innerRole);
                    }
                }
            }
        }

        foreach ($this->itemsStorage->getRoles() as $existingRole) {
            if (in_array($existingRole->getName(), $roles, true) === false) {
                $this->manager->removeRole($existingRole->getName());
            }
        }

        foreach ($this->itemsStorage->getPermissions() as $existingPermission) {
            if (in_array($existingPermission->getName(), $permissions, true) === false) {
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

            foreach ($reflection->getConstants() as $name => $rbacItemName) {
                if (str_starts_with($name, 'PERMISSION_') === true) {
                    $permissions[] = $rbacItemName;
                } elseif (str_starts_with($name, 'ROLE_') === true) {
                    $roles[] = $rbacItemName;
                }
            }
        }

        return [
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    /**
     * Returns whether the DB RBAC state is in sync with the code.
     *
     * @return bool True if all roles/permissions match, false if any diff exists
     */
    public function isInSync(): bool
    {
        $diff = $this->getDiff();
        return empty($diff['missingRoles']) === true
            && empty($diff['missingPermissions']) === true
            && empty($diff['obsoleteRoles']) === true
            && empty($diff['obsoletePermissions']) === true;
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

        $storedRoles = $this->itemsStorage->getRoles();
        $dbRoles = [];
        foreach ($storedRoles as $roleName => $role) {
            $dbRoles[$roleName] = $role->getName();
        }
        $storedPermissions = $this->itemsStorage->getPermissions();
        $dbPermissions = [];
        foreach ($storedPermissions as $permissionName => $permission) {
            $dbPermissions[$permissionName] = $permission->getName();
        }

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
