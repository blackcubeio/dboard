<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Closure;
use Stringable;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

/**
 * RBAC manager decorator caching a user's permissions for the lifetime of the
 * request. Sidebar entries, views and the auth middleware all check the same
 * user's permissions; without caching each userHasPermission() hits the
 * database (~3 queries) with no memoization. This decorator loads the whole
 * permission set once via getPermissionsByUserId() and answers from memory.
 *
 * Transparent: implements ManagerInterface and delegates everything to the
 * wrapped manager, only short-circuiting userHasPermission(). Mutating calls
 * invalidate the cache.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class CachedRbacManager implements ManagerInterface
{
    /**
     * @var array<string, Permission>|null Permissions of the loaded user, indexed by name
     */
    private ?array $permissions = null;

    private ?string $loadedFor = null;

    public function __construct(
        private readonly ManagerInterface $manager,
    ) {
    }

    /**
     * Guest checks and parameterised checks (rules) are delegated as-is.
     */
    public function userHasPermission(int|string|Stringable|null $userId, string $permissionName, array $parameters = []): bool
    {
        if ($userId === null || empty($parameters) === false) {
            $hasPermission = $this->manager->userHasPermission($userId, $permissionName, $parameters);
        } else {
            $userId = (string) $userId;
            if ($this->loadedFor !== $userId) {
                $this->permissions = $this->manager->getPermissionsByUserId($userId);
                $this->loadedFor = $userId;
            }

            $hasPermission = isset($this->permissions[$permissionName]) === true;
        }

        return $hasPermission;
    }

    public function canAddChild(string $parentName, string $childName): bool
    {
        return $this->manager->canAddChild($parentName, $childName);
    }

    public function addChild(string $parentName, string $childName): self
    {
        $this->manager->addChild($parentName, $childName);
        $this->invalidate();
        return $this;
    }

    public function removeChild(string $parentName, string $childName): self
    {
        $this->manager->removeChild($parentName, $childName);
        $this->invalidate();
        return $this;
    }

    public function removeChildren(string $parentName): self
    {
        $this->manager->removeChildren($parentName);
        $this->invalidate();
        return $this;
    }

    public function hasChild(string $parentName, string $childName): bool
    {
        return $this->manager->hasChild($parentName, $childName);
    }

    public function hasChildren(string $parentName): bool
    {
        return $this->manager->hasChildren($parentName);
    }

    public function assign(string $itemName, int|Stringable|string $userId, ?int $createdAt = null): self
    {
        $this->manager->assign($itemName, $userId, $createdAt);
        $this->invalidate();
        return $this;
    }

    public function revoke(string $itemName, int|Stringable|string $userId): self
    {
        $this->manager->revoke($itemName, $userId);
        $this->invalidate();
        return $this;
    }

    public function revokeAll(int|Stringable|string $userId): self
    {
        $this->manager->revokeAll($userId);
        $this->invalidate();
        return $this;
    }

    public function getItemsByUserId(int|Stringable|string $userId): array
    {
        return $this->manager->getItemsByUserId($userId);
    }

    public function getRolesByUserId(int|Stringable|string $userId): array
    {
        return $this->manager->getRolesByUserId($userId);
    }

    public function getChildRoles(string $roleName): array
    {
        return $this->manager->getChildRoles($roleName);
    }

    public function getPermissionsByRoleName(string $roleName): array
    {
        return $this->manager->getPermissionsByRoleName($roleName);
    }

    public function getPermissionsByUserId(int|Stringable|string $userId): array
    {
        return $this->manager->getPermissionsByUserId($userId);
    }

    public function getUserIdsByRoleName(string $roleName): array
    {
        return $this->manager->getUserIdsByRoleName($roleName);
    }

    public function addRole(Role $role): self
    {
        $this->manager->addRole($role);
        $this->invalidate();
        return $this;
    }

    public function getRole(string $name): ?Role
    {
        return $this->manager->getRole($name);
    }

    public function updateRole(string $name, Role $role): self
    {
        $this->manager->updateRole($name, $role);
        $this->invalidate();
        return $this;
    }

    public function removeRole(string $name): self
    {
        $this->manager->removeRole($name);
        $this->invalidate();
        return $this;
    }

    public function addPermission(Permission $permission): self
    {
        $this->manager->addPermission($permission);
        $this->invalidate();
        return $this;
    }

    public function getPermission(string $name): ?Permission
    {
        return $this->manager->getPermission($name);
    }

    public function removePermission(string $name): self
    {
        $this->manager->removePermission($name);
        $this->invalidate();
        return $this;
    }

    public function updatePermission(string $name, Permission $permission): self
    {
        $this->manager->updatePermission($name, $permission);
        $this->invalidate();
        return $this;
    }

    public function setDefaultRoleNames(array|Closure $roleNames): self
    {
        $this->manager->setDefaultRoleNames($roleNames);
        return $this;
    }

    public function getDefaultRoleNames(): array
    {
        return $this->manager->getDefaultRoleNames();
    }

    public function getDefaultRoles(): array
    {
        return $this->manager->getDefaultRoles();
    }

    public function setGuestRoleName(?string $name): self
    {
        $this->manager->setGuestRoleName($name);
        return $this;
    }

    public function getGuestRoleName(): ?string
    {
        return $this->manager->getGuestRoleName();
    }

    public function getGuestRole(): ?Role
    {
        return $this->manager->getGuestRole();
    }

    private function invalidate(): void
    {
        $this->permissions = null;
        $this->loadedFor = null;
    }
}
