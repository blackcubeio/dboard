<?php

declare(strict_types=1);

/**
 * Rbac.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Components;

use ReflectionClass;
use Yiisoft\Strings\Inflector;

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

    /*/
    public const PERMISSION_PLUGIN_DELETE = 'PLUGIN:DELETE';
    public const PERMISSION_PLUGIN_UPDATE = 'PLUGIN:UPDATE';
    public const PERMISSION_PLUGIN_VIEW = 'PLUGIN:VIEW';
    public const ROLE_PLUGIN_MANAGER = 'PLUGIN:MANAGER';
    /**/

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
        // self::ROLE_PLUGIN_MANAGER,
        self::ROLE_IA_MANAGER,
    ];

    public static function extractPermission(string $permission): string
    {
        if (str_contains($permission, ':')) {
            [, $name] = explode(':', $permission);
        } else {
            $name = $permission;
        }
        return $name;
    }

    public static function extractRole(string $role): string
    {
        if (str_contains($role, ':')) {
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

    public static function rbac2Id(string $item): string
    {
        $item = strtolower(str_replace(':', '_', $item));
        $item = self::inflector()->toCamelCase($item);
        return self::inflector()->toSlug($item);
    }

    public static function rbac2Name(string $item): string
    {
        $item = strtolower(str_replace(':', '_', $item));
        return self::inflector()->toCamelCase($item);
    }

    public static function name2Rbac(string $name): string
    {
        $name = self::inflector()->toWords($name);
        return strtoupper(str_replace(' ', ':', $name));
    }

    /**
     * Retourne tous les rôles dans l'ordre défini par ORDERING_ROLES.
     * @return string[]
     */
    public static function getAllRoles(): array
    {
        return self::ORDERING_ROLES;
    }

    /**
     * Retourne toutes les permissions (PERMISSION_*) via réflection.
     * @return string[]
     */
    public static function getAllPermissions(): array
    {
        $reflection = new ReflectionClass(self::class);
        $permissions = [];
        foreach ($reflection->getConstants() as $name => $value) {
            if (str_starts_with($name, 'PERMISSION_')) {
                $permissions[] = $value;
            }
        }
        return $permissions;
    }

    /**
     * Retourne les permissions d'un rôle (matching par préfixe).
     * TAG:MANAGER → [TAG:CREATE, TAG:DELETE, TAG:UPDATE, TAG:VIEW, ...]
     * @return string[]
     */
    public static function getPermissionsByRole(string $roleName): array
    {
        // ROLE_ADMIN n'a pas de permissions directes, il a des rôles enfants
        if ($roleName === self::ROLE_ADMIN) {
            return [];
        }

        // Extraire préfixe : TAG:MANAGER → TAG
        if (!str_contains($roleName, ':')) {
            return [];
        }
        [$rolePrefix] = explode(':', $roleName);

        // Matcher les permissions avec le même préfixe
        $permissions = [];
        foreach (self::getAllPermissions() as $permission) {
            if (str_contains($permission, ':')) {
                [$permPrefix] = explode(':', $permission);
                if ($permPrefix === $rolePrefix) {
                    $permissions[] = $permission;
                }
            }
        }
        return $permissions;
    }

    /**
     * Retourne les rôles enfants d'un rôle.
     * ROLE_ADMIN → tous les autres ROLE_*
     * Autres rôles → [] (pas de hiérarchie inter-rôles sauf ADMIN)
     * @return string[]
     */
    public static function getChildRoles(string $roleName): array
    {
        if ($roleName !== self::ROLE_ADMIN) {
            return [];
        }

        // ADMIN contient tous les autres rôles
        $childRoles = [];
        foreach (self::getAllRoles() as $role) {
            if ($role !== self::ROLE_ADMIN) {
                $childRoles[] = $role;
            }
        }
        return $childRoles;
    }

    /**
     * Calcule les assignments basé sur le DIFF entre AVANT et POST.
     *
     * Règles :
     * - [A] Added : dans POST mais pas dans AVANT
     * - [R] Removed : dans AVANT mais pas dans POST
     * - [U] Untouched : même état dans AVANT et POST
     *
     * - Un rôle est VALIDE si aucune de ses permissions n'est [R] Removed
     * - Si rôle valide : assigner le rôle, toutes ses perms deviennent héritées
     * - Si rôle invalide : ne pas assigner, garder seulement les perms qui étaient ON avant et sont encore ON
     *
     * @param string[] $selectedRoles Rôles cochés dans le POST
     * @param string[] $selectedPermissions Permissions cochées dans le POST
     * @param string[] $previousAssignments Assignments directs AVANT (rôles + permissions)
     * @return string[] Assignments finaux à stocker
     */
    public static function rebuildAssignments(
        array $selectedRoles,
        array $selectedPermissions,
        array $previousAssignments
    ): array {
        $selectedPermissionsMap = array_flip($selectedPermissions);
        $selectedRolesMap = array_flip($selectedRoles);
        $previousAssignmentsMap = array_flip($previousAssignments);

        // Helper: une permission était-elle "ON" avant ?
        // (directement assignée OU héritée d'un rôle OU héritée de ADMIN)
        $wasPermissionOnBefore = function (string $permName, ?string $parentRole) use ($previousAssignmentsMap): bool {
            // Directement assignée
            if (isset($previousAssignmentsMap[$permName])) {
                return true;
            }
            // Héritée du rôle parent
            if ($parentRole !== null && isset($previousAssignmentsMap[$parentRole])) {
                return true;
            }
            // Héritée de ADMIN
            if (isset($previousAssignmentsMap[self::ROLE_ADMIN])) {
                return true;
            }
            return false;
        };

        // Helper: trouver le rôle parent d'une permission
        $findParentRole = function (string $permName): ?string {
            foreach (self::getAllRoles() as $role) {
                if ($role === self::ROLE_ADMIN) {
                    continue;
                }
                if (in_array($permName, self::getPermissionsByRole($role), true)) {
                    return $role;
                }
            }
            return null;
        };

        $assignments = [];
        $assignedRolesMap = [];  // Track réellement assignés
        $coveredRoles = [];
        $coveredPermissions = [];

        // 1. Traiter ADMIN d'abord
        if (isset($selectedRolesMap[self::ROLE_ADMIN])) {
            $childRoles = self::getChildRoles(self::ROLE_ADMIN);
            $adminIsValid = true;
            $wasAdminBefore = isset($previousAssignmentsMap[self::ROLE_ADMIN]);

            // ADMIN valide si :
            // - Aucun rôle enfant n'est [R] Removed (était ON avant, OFF maintenant)
            // - Aucune permission n'est [R] Removed
            foreach ($childRoles as $childRole) {
                // Le rôle était ON avant si directement assigné OU si ADMIN était assigné
                $wasRoleOnBefore = isset($previousAssignmentsMap[$childRole]) || $wasAdminBefore;
                $isRoleOnNow = isset($selectedRolesMap[$childRole]);

                // [R] Removed = était ON, maintenant OFF
                if ($wasRoleOnBefore && !$isRoleOnNow) {
                    $adminIsValid = false;
                    break;
                }
            }

            if ($adminIsValid) {
                foreach ($childRoles as $childRole) {
                    foreach (self::getPermissionsByRole($childRole) as $perm) {
                        $wasOn = $wasPermissionOnBefore($perm, $childRole);
                        $isOn = isset($selectedPermissionsMap[$perm]);
                        // [R] Removed = était ON, maintenant OFF
                        if ($wasOn && !$isOn) {
                            $adminIsValid = false;
                            break 2;
                        }
                    }
                }
            }

            if ($adminIsValid) {
                $assignments[] = self::ROLE_ADMIN;
                $assignedRolesMap[self::ROLE_ADMIN] = true;
                foreach ($childRoles as $childRole) {
                    $coveredRoles[$childRole] = true;
                    foreach (self::getPermissionsByRole($childRole) as $perm) {
                        $coveredPermissions[$perm] = true;
                    }
                }
            }
        }

        // 2. Traiter les autres rôles
        $wasAdminBefore = isset($previousAssignmentsMap[self::ROLE_ADMIN]);
        $isAdminNow = isset($selectedRolesMap[self::ROLE_ADMIN]);

        foreach ($selectedRoles as $roleName) {
            if ($roleName === self::ROLE_ADMIN) {
                continue;
            }
            if (isset($coveredRoles[$roleName])) {
                continue;
            }

            // Si ADMIN était assigné avant et est maintenant décoché,
            // les rôles enfants qui n'étaient pas directement assignés sont "untouched"
            if ($wasAdminBefore && !$isAdminNow) {
                // Le rôle était-il directement assigné avant (pas hérité de ADMIN) ?
                if (!isset($previousAssignmentsMap[$roleName])) {
                    // Hérité de ADMIN, ADMIN décoché → untouched → ne pas assigner
                    continue;
                }
            }

            $rolePermissions = self::getPermissionsByRole($roleName);
            $isValid = true;

            // Rôle valide si aucune permission n'est [R] Removed
            foreach ($rolePermissions as $perm) {
                $wasOn = $wasPermissionOnBefore($perm, $roleName);
                $isOn = isset($selectedPermissionsMap[$perm]);
                // [R] Removed = était ON, maintenant OFF
                if ($wasOn && !$isOn) {
                    $isValid = false;
                    break;
                }
            }

            if ($isValid) {
                $assignments[] = $roleName;
                $assignedRolesMap[$roleName] = true;
                foreach ($rolePermissions as $perm) {
                    $coveredPermissions[$perm] = true;
                }
            }
        }

        // 3. Traiter les permissions non couvertes par un rôle valide
        foreach ($selectedPermissions as $permName) {
            if (isset($coveredPermissions[$permName])) {
                continue;
            }

            $parentRole = $findParentRole($permName);

            // Check si le rôle parent est RÉELLEMENT assigné (pas juste coché dans l'UI)
            $parentRoleAssigned = $parentRole !== null && (
                isset($assignedRolesMap[$parentRole]) || isset($coveredRoles[$parentRole])
            );

            // Si le rôle parent n'est PAS assigné
            if ($parentRole !== null && !$parentRoleAssigned) {
                // Si ADMIN a été décoché (chaîne brisée volontairement) → mode strict
                if ($wasAdminBefore && !$isAdminNow) {
                    if (isset($previousAssignmentsMap[$permName])) {
                        $assignments[] = $permName;
                    }
                    continue;
                }

                // Si le rôle parent était coché mais invalide → garder si était ON
                if (isset($selectedRolesMap[$parentRole])) {
                    $wasOn = $wasPermissionOnBefore($permName, $parentRole);
                    if ($wasOn) {
                        $assignments[] = $permName;
                    }
                    continue;
                }

                // Rôle parent pas coché → permission explicitement ajoutée par l'utilisateur
                // On l'ajoute car elle est dans selectedPermissions (la promotion se fera en étape 4)
                $assignments[] = $permName;
                continue;
            }

            // Le rôle parent est assigné (mais invalide car perms non couvertes) ou pas de rôle parent
            $wasOn = $wasPermissionOnBefore($permName, $parentRole);

            // On la garde si :
            // - Elle était ON avant (on la conserve)
            // - OU son rôle parent est assigné (l'utilisateur essaie d'ajouter le rôle mais il est invalide)
            if ($wasOn || $parentRoleAssigned) {
                $assignments[] = $permName;
            }
            // Sinon : permission "untouched OFF" → ne pas ajouter
        }

        // 4. Promotion : si toutes les permissions d'un rôle sont assignées, promouvoir vers le rôle
        $assignmentsMap = array_flip($assignments);
        foreach (self::getAllRoles() as $role) {
            if ($role === self::ROLE_ADMIN) {
                continue; // ADMIN traité séparément
            }
            if (isset($assignedRolesMap[$role]) || isset($coveredRoles[$role])) {
                continue; // Rôle déjà assigné ou couvert
            }

            $rolePermissions = self::getPermissionsByRole($role);
            if (empty($rolePermissions)) {
                continue;
            }

            // Vérifier si toutes les permissions du rôle sont dans assignments
            $allPermsAssigned = true;
            foreach ($rolePermissions as $perm) {
                if (!isset($assignmentsMap[$perm])) {
                    $allPermsAssigned = false;
                    break;
                }
            }

            if ($allPermsAssigned) {
                // Promouvoir vers le rôle
                $assignments[] = $role;
                $assignedRolesMap[$role] = true;
                // Retirer les permissions individuelles (elles seront héritées du rôle)
                $assignments = array_filter($assignments, fn($a) => !in_array($a, $rolePermissions, true));
                $assignmentsMap = array_flip($assignments);
            }
        }

        // 5. Promotion ADMIN : si tous les rôles enfants sont assignés
        if (!isset($assignedRolesMap[self::ROLE_ADMIN])) {
            $allChildRolesAssigned = true;
            foreach (self::getChildRoles(self::ROLE_ADMIN) as $childRole) {
                if (!isset($assignedRolesMap[$childRole])) {
                    $allChildRolesAssigned = false;
                    break;
                }
            }

            if ($allChildRolesAssigned) {
                // Promouvoir vers ADMIN (remplace tous les autres rôles)
                $assignments = [self::ROLE_ADMIN];
            }
        }

        return array_values(array_unique($assignments));
    }
}
