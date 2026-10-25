<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Components;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Injector\Injector;
use ReflectionClass;
use Yiisoft\Strings\Inflector;

/**
 * Roles and permissions of the admin panel, with the helpers that name, order and rebuild them.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Rbac
{
    private static ?Inflector $inflector = null;

    public const PERMISSION_TAG_IMPORT = 'TAG:IMPORT';
    public const PERMISSION_TAG_EXPORT = 'TAG:EXPORT';
    public const PERMISSION_TAG_CREATE = 'TAG:CREATE';
    public const PERMISSION_TAG_DELETE = 'TAG:DELETE';
    public const PERMISSION_TAG_UPDATE = 'TAG:UPDATE';
    public const PERMISSION_TAG_VIEW = 'TAG:VIEW';
    public const ROLE_TAG_MANAGER = 'TAG:MANAGER';

    public const PERMISSION_CONTENT_IMPORT = 'CONTENT:IMPORT';
    public const PERMISSION_CONTENT_EXPORT = 'CONTENT:EXPORT';
    public const PERMISSION_CONTENT_CREATE = 'CONTENT:CREATE';
    public const PERMISSION_CONTENT_DELETE = 'CONTENT:DELETE';
    public const PERMISSION_CONTENT_UPDATE = 'CONTENT:UPDATE';
    public const PERMISSION_CONTENT_VIEW = 'CONTENT:VIEW';
    public const ROLE_CONTENT_MANAGER = 'CONTENT:MANAGER';

    public const PERMISSION_ELASTICSCHEMA_CREATE = 'ELASTICSCHEMA:CREATE';
    public const PERMISSION_ELASTICSCHEMA_DELETE = 'ELASTICSCHEMA:DELETE';
    public const PERMISSION_ELASTICSCHEMA_UPDATE = 'ELASTICSCHEMA:UPDATE';
    public const PERMISSION_ELASTICSCHEMA_VIEW = 'ELASTICSCHEMA:VIEW';
    public const ROLE_ELASTICSCHEMA_MANAGER = 'ELASTICSCHEMA:MANAGER';

    public const PERMISSION_TYPE_CREATE = 'TYPE:CREATE';
    public const PERMISSION_TYPE_DELETE = 'TYPE:DELETE';
    public const PERMISSION_TYPE_UPDATE = 'TYPE:UPDATE';
    public const PERMISSION_TYPE_VIEW = 'TYPE:VIEW';
    public const ROLE_TYPE_MANAGER = 'TYPE:MANAGER';

    public const PERMISSION_PARAMETER_CREATE = 'PARAMETER:CREATE';
    public const PERMISSION_PARAMETER_DELETE = 'PARAMETER:DELETE';
    public const PERMISSION_PARAMETER_UPDATE = 'PARAMETER:UPDATE';
    public const PERMISSION_PARAMETER_VIEW = 'PARAMETER:VIEW';
    public const ROLE_PARAMETER_MANAGER = 'PARAMETER:MANAGER';

    public const PERMISSION_MENU_CREATE = 'MENU:CREATE';
    public const PERMISSION_MENU_DELETE = 'MENU:DELETE';
    public const PERMISSION_MENU_UPDATE = 'MENU:UPDATE';
    public const PERMISSION_MENU_VIEW = 'MENU:VIEW';
    public const ROLE_MENU_MANAGER = 'MENU:MANAGER';

    public const PERMISSION_LLMMENU_CREATE = 'LLMMENU:CREATE';
    public const PERMISSION_LLMMENU_DELETE = 'LLMMENU:DELETE';
    public const PERMISSION_LLMMENU_UPDATE = 'LLMMENU:UPDATE';
    public const PERMISSION_LLMMENU_VIEW = 'LLMMENU:VIEW';
    public const ROLE_LLMMENU_MANAGER = 'LLMMENU:MANAGER';

    public const PERMISSION_LANGUAGE_CREATE = 'LANGUAGE:CREATE';
    public const PERMISSION_LANGUAGE_DELETE = 'LANGUAGE:DELETE';
    public const PERMISSION_LANGUAGE_UPDATE = 'LANGUAGE:UPDATE';
    public const PERMISSION_LANGUAGE_VIEW = 'LANGUAGE:VIEW';
    public const ROLE_LANGUAGE_MANAGER = 'LANGUAGE:MANAGER';

    public const PERMISSION_HOST_CREATE = 'HOST:CREATE';
    public const PERMISSION_HOST_DELETE = 'HOST:DELETE';
    public const PERMISSION_HOST_UPDATE = 'HOST:UPDATE';
    public const PERMISSION_HOST_VIEW = 'HOST:VIEW';
    public const ROLE_HOST_MANAGER = 'HOST:MANAGER';

    public const PERMISSION_XEO_CREATE = 'XEO:CREATE';
    public const PERMISSION_XEO_DELETE = 'XEO:DELETE';
    public const PERMISSION_XEO_UPDATE = 'XEO:UPDATE';
    public const PERMISSION_XEO_VIEW = 'XEO:VIEW';
    public const ROLE_XEO_MANAGER = 'XEO:MANAGER';

    public const PERMISSION_AUTHOR_CREATE = 'AUTHOR:CREATE';
    public const PERMISSION_AUTHOR_DELETE = 'AUTHOR:DELETE';
    public const PERMISSION_AUTHOR_UPDATE = 'AUTHOR:UPDATE';
    public const PERMISSION_AUTHOR_VIEW = 'AUTHOR:VIEW';
    public const ROLE_AUTHOR_MANAGER = 'AUTHOR:MANAGER';

    public const ROLE_SITE_MANAGER = 'SITE:MANAGER';
    public const PERMISSION_SITE_PREVIEW = 'SITE:PREVIEW';
    public const PERMISSION_SITE_DASHBOARD = 'SITE:DASHBOARD';
    public const PERMISSION_SITE_SEARCH = 'SITE:SEARCH';

    public const PERMISSION_ADMINISTRATOR_CREATE = 'ADMINISTRATOR:CREATE';
    public const PERMISSION_ADMINISTRATOR_DELETE = 'ADMINISTRATOR:DELETE';
    public const PERMISSION_ADMINISTRATOR_UPDATE = 'ADMINISTRATOR:UPDATE';
    public const PERMISSION_ADMINISTRATOR_VIEW = 'ADMINISTRATOR:VIEW';
    public const ROLE_ADMINISTRATOR_MANAGER = 'ADMINISTRATOR:MANAGER';

    public const PERMISSION_SLUG_CREATE = 'SLUG:CREATE';
    public const PERMISSION_SLUG_DELETE = 'SLUG:DELETE';
    public const PERMISSION_SLUG_UPDATE = 'SLUG:UPDATE';
    public const PERMISSION_SLUG_VIEW = 'SLUG:VIEW';
    public const ROLE_SLUG_MANAGER = 'SLUG:MANAGER';

    public const PERMISSION_RBAC_REFRESH = 'RBAC:REFRESH';
    public const PERMISSION_RBAC_VIEW = 'RBAC:VIEW';
    public const ROLE_RBAC_MANAGER = 'RBAC:MANAGER';

    public const PERMISSION_ADDON_VIEW = 'ADDON:VIEW';
    public const ROLE_ADDON_MANAGER = 'ADDON:MANAGER';

    public const PERMISSION_IA_IMPORT = 'IA:IMPORT';
    public const PERMISSION_IA_EXPORT = 'IA:EXPORT';
    public const ROLE_IA_MANAGER = 'IA:MANAGER';

    public const ROLE_ADMIN = 'ADMIN';

    public const ORDERING_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SITE_MANAGER,
        self::ROLE_ADMINISTRATOR_MANAGER,
        self::ROLE_CONTENT_MANAGER,
        self::ROLE_TAG_MANAGER,
        self::ROLE_MENU_MANAGER,
        self::ROLE_SLUG_MANAGER,
        self::ROLE_RBAC_MANAGER,
        self::ROLE_PARAMETER_MANAGER,
        self::ROLE_LANGUAGE_MANAGER,
        self::ROLE_HOST_MANAGER,
        self::ROLE_XEO_MANAGER,
        self::ROLE_LLMMENU_MANAGER,
        self::ROLE_AUTHOR_MANAGER,
        self::ROLE_TYPE_MANAGER,
        self::ROLE_ELASTICSCHEMA_MANAGER,
        self::ROLE_IA_MANAGER,
    ];

    public static function extractPermission(string $permission): string
    {
        if (str_contains($permission, ':') === true) {
            [, $name] = explode(':', $permission);
        } else {
            $name = $permission;
        }
        return $name;
    }

    public static function extractRole(string $role): string
    {
        if (str_contains($role, ':') === true) {
            [$name] = explode(':', $role);
        } else {
            $name = $role;
        }
        return $name;
    }

    private static function inflector(): Inflector
    {
        if (self::$inflector === null) {
            self::$inflector = new Inflector();
        }
        return self::$inflector;
    }

    public static function rbac2Id(string $rbacName): string
    {
        $rbacName = strtolower(str_replace(':', '_', $rbacName));
        $rbacName = self::inflector()->toCamelCase($rbacName);
        return self::inflector()->toSlug($rbacName);
    }

    public static function rbac2Name(string $rbacName): string
    {
        $rbacName = strtolower(str_replace(':', '_', $rbacName));
        return self::inflector()->toCamelCase($rbacName);
    }

    public static function name2Rbac(string $name): string
    {
        $name = self::inflector()->toWords($name);
        return strtoupper(str_replace(' ', ':', $name));
    }

    /**
     * Returns every role: ORDERING_ROLES first, then the roles (ROLE_*) of the
     * other declared RBAC classes (DboardConfig::rbacClasses).
     * @return string[]
     */
    public static function getAllRoles(): array
    {
        $roles = self::ORDERING_ROLES;
        foreach (Injector::get(DboardConfig::class)->rbacClasses as $rbacClass) {
            if ($rbacClass !== self::class) {
                $reflection = new ReflectionClass($rbacClass);
                foreach ($reflection->getConstants() as $name => $roleName) {
                    if (str_starts_with($name, 'ROLE_') === true) {
                        $roles[] = $roleName;
                    }
                }
            }
        }
        return array_values(array_unique($roles));
    }

    /**
     * Returns every permission (PERMISSION_*) of the declared RBAC classes
     * (DboardConfig::rbacClasses) through reflection.
     * @return string[]
     */
    public static function getAllPermissions(): array
    {
        $permissions = [];
        foreach (Injector::get(DboardConfig::class)->rbacClasses as $rbacClass) {
            $reflection = new ReflectionClass($rbacClass);
            foreach ($reflection->getConstants() as $name => $permissionName) {
                if (str_starts_with($name, 'PERMISSION_') === true) {
                    $permissions[] = $permissionName;
                }
            }
        }
        return array_values(array_unique($permissions));
    }

    /**
     * Returns the permissions of a role (prefix matching).
     * TAG:MANAGER → [TAG:CREATE, TAG:DELETE, TAG:UPDATE, TAG:VIEW, ...]
     * @return string[]
     */
    public static function getRolePermissions(string $roleName): array
    {
        $permissions = [];
        if ($roleName !== self::ROLE_ADMIN && str_contains($roleName, ':') === true) {
            [$rolePrefix] = explode(':', $roleName);

            foreach (self::getAllPermissions() as $permission) {
                if (str_contains($permission, ':') === true) {
                    [$permPrefix] = explode(':', $permission);
                    if ($permPrefix === $rolePrefix) {
                        $permissions[] = $permission;
                    }
                }
            }
        }
        return $permissions;
    }

    /**
     * Returns the child roles of a role.
     * ROLE_ADMIN -> every other ROLE_*
     * Other roles -> [] (no hierarchy between roles except ADMIN)
     * @return string[]
     */
    public static function getChildRoles(string $roleName): array
    {
        $childRoles = [];
        if ($roleName === self::ROLE_ADMIN) {
            foreach (self::getAllRoles() as $role) {
                if ($role !== self::ROLE_ADMIN) {
                    $childRoles[] = $role;
                }
            }
        }
        return $childRoles;
    }

    /**
     * Computes the assignments from the DIFF between BEFORE and POST.
     *
     * Rules:
     * - [A] Added: in POST but not in BEFORE
     * - [R] Removed: in BEFORE but not in POST
     * - [U] Untouched: same state in BEFORE and POST
     *
     * - A role is VALID if none of its permissions is [R] Removed
     * - Valid role: assign the role, all its permissions become inherited
     * - Invalid role: do not assign, keep only the permissions that were ON before and still are
     *
     * @param string[] $selectedRoles Roles checked in the POST
     * @param string[] $selectedPermissions Permissions checked in the POST
     * @param string[] $previousAssignments Direct assignments BEFORE (roles + permissions)
     * @return string[] Final assignments to store
     */
    public static function rebuildAssignments(
        array $selectedRoles,
        array $selectedPermissions,
        array $previousAssignments
    ): array {
        $selectedPermissionsMap = array_flip($selectedPermissions);
        $selectedRolesMap = array_flip($selectedRoles);
        $previousAssignmentsMap = array_flip($previousAssignments);
        $assignments = [];
        $assignedRolesMap = [];
        $coveredRoles = [];
        $coveredPermissions = [];

        if (isset($selectedRolesMap[self::ROLE_ADMIN]) === true) {
            $childRoles = self::getChildRoles(self::ROLE_ADMIN);
            $adminIsValid = true;
            $wasAdminBefore = isset($previousAssignmentsMap[self::ROLE_ADMIN]);

            foreach ($childRoles as $childRole) {
                $wasRoleOnBefore = isset($previousAssignmentsMap[$childRole]) === true || $wasAdminBefore === true;
                $isRoleOnNow = isset($selectedRolesMap[$childRole]);

                if ($wasRoleOnBefore === true && $isRoleOnNow === false) {
                    $adminIsValid = false;
                }
            }

            if ($adminIsValid === true) {
                foreach ($childRoles as $childRole) {
                    if ($adminIsValid === true) {
                        foreach (self::getRolePermissions($childRole) as $perm) {
                            $wasOn = self::wasPermissionOnBefore($perm, $childRole, $previousAssignmentsMap);
                            $isOn = isset($selectedPermissionsMap[$perm]);
                            if ($wasOn === true && $isOn === false) {
                                $adminIsValid = false;
                            }
                        }
                    }
                }
            }

            if ($adminIsValid === true) {
                $assignments[] = self::ROLE_ADMIN;
                $assignedRolesMap[self::ROLE_ADMIN] = true;
                foreach ($childRoles as $childRole) {
                    $coveredRoles[$childRole] = true;
                    foreach (self::getRolePermissions($childRole) as $perm) {
                        $coveredPermissions[$perm] = true;
                    }
                }
            }
        }

        $wasAdminBefore = isset($previousAssignmentsMap[self::ROLE_ADMIN]);
        $isAdminNow = isset($selectedRolesMap[self::ROLE_ADMIN]);

        foreach ($selectedRoles as $roleName) {
            $isCandidateRole = $roleName !== self::ROLE_ADMIN
                && isset($coveredRoles[$roleName]) === false
                && ($wasAdminBefore === false || $isAdminNow === true || isset($previousAssignmentsMap[$roleName]) === true);

            if ($isCandidateRole === true) {
                $rolePermissions = self::getRolePermissions($roleName);
                $isValid = true;

                foreach ($rolePermissions as $perm) {
                    $wasOn = self::wasPermissionOnBefore($perm, $roleName, $previousAssignmentsMap);
                    $isOn = isset($selectedPermissionsMap[$perm]);
                    if ($wasOn === true && $isOn === false) {
                        $isValid = false;
                    }
                }

                if ($isValid === true) {
                    $assignments[] = $roleName;
                    $assignedRolesMap[$roleName] = true;
                    foreach ($rolePermissions as $perm) {
                        $coveredPermissions[$perm] = true;
                    }
                }
            }
        }

        foreach ($selectedPermissions as $permName) {
            if (isset($coveredPermissions[$permName]) === false) {
                $parentRole = self::findParentRole($permName);

                $parentRoleAssigned = $parentRole !== null && (
                    isset($assignedRolesMap[$parentRole]) === true || isset($coveredRoles[$parentRole]) === true
                );

                if ($parentRole !== null && $parentRoleAssigned === false) {
                    if ($wasAdminBefore === true && $isAdminNow === false) {
                        if (isset($previousAssignmentsMap[$permName]) === true) {
                            $assignments[] = $permName;
                        }
                    } elseif (isset($selectedRolesMap[$parentRole]) === true) {
                        $wasOn = self::wasPermissionOnBefore($permName, $parentRole, $previousAssignmentsMap);
                        if ($wasOn === true) {
                            $assignments[] = $permName;
                        }
                    } else {
                        $assignments[] = $permName;
                    }
                } else {
                    $wasOn = self::wasPermissionOnBefore($permName, $parentRole, $previousAssignmentsMap);

                    if ($wasOn === true || $parentRoleAssigned === true) {
                        $assignments[] = $permName;
                    }
                }
            }
        }

        $assignmentsMap = array_flip($assignments);
        foreach (self::getAllRoles() as $role) {
            if ($role !== self::ROLE_ADMIN && isset($assignedRolesMap[$role]) === false && isset($coveredRoles[$role]) === false) {
                $rolePermissions = self::getRolePermissions($role);
                if (empty($rolePermissions) === false) {
                    $allPermsAssigned = true;
                    foreach ($rolePermissions as $perm) {
                        if (isset($assignmentsMap[$perm]) === false) {
                            $allPermsAssigned = false;
                        }
                    }

                    if ($allPermsAssigned === true) {
                        $assignments[] = $role;
                        $assignedRolesMap[$role] = true;
                        $remainingAssignments = [];
                        foreach ($assignments as $assignment) {
                            if (in_array($assignment, $rolePermissions, true) === false) {
                                $remainingAssignments[] = $assignment;
                            }
                        }
                        $assignments = $remainingAssignments;
                        $assignmentsMap = array_flip($assignments);
                    }
                }
            }
        }

        if (isset($assignedRolesMap[self::ROLE_ADMIN]) === false) {
            $allChildRolesAssigned = true;
            foreach (self::getChildRoles(self::ROLE_ADMIN) as $childRole) {
                if (isset($assignedRolesMap[$childRole]) === false) {
                    $allChildRolesAssigned = false;
                }
            }

            if ($allChildRolesAssigned === true) {
                $assignments = [
                    self::ROLE_ADMIN,
                ];
            }
        }

        return array_values(array_unique($assignments));
    }

    /**
     * Tells whether a permission was ON before the POST: assigned directly, inherited from its role or from ADMIN.
     *
     * @param array<string, int> $previousAssignmentsMap Direct assignments BEFORE, flipped
     */
    private static function wasPermissionOnBefore(string $permName, ?string $parentRole, array $previousAssignmentsMap): bool
    {
        $wasOn = false;
        if (isset($previousAssignmentsMap[$permName]) === true) {
            $wasOn = true;
        }
        if ($parentRole !== null && isset($previousAssignmentsMap[$parentRole]) === true) {
            $wasOn = true;
        }
        if (isset($previousAssignmentsMap[self::ROLE_ADMIN]) === true) {
            $wasOn = true;
        }
        return $wasOn;
    }

    /**
     * Returns the first role (ADMIN excepted) whose permissions contain the permission.
     */
    private static function findParentRole(string $permName): ?string
    {
        $parentRole = null;
        foreach (self::getAllRoles() as $role) {
            if ($parentRole === null && $role !== self::ROLE_ADMIN && in_array($permName, self::getRolePermissions($role), true) === true) {
                $parentRole = $role;
            }
        }
        return $parentRole;
    }
}
