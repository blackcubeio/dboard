# dboard — RBAC

## Constants (`src/Components/Rbac.php:29-128`)

Permissions are `PREFIX:ACTION`, roles `PREFIX:MANAGER`, one prefix per module:

| Prefix | Permissions |
|---|---|
| `CONTENT`, `TAG` | `VIEW`, `CREATE`, `UPDATE`, `DELETE`, `IMPORT`, `EXPORT` |
| `MENU`, `LLMMENU`, `ELASTICSCHEMA`, `TYPE`, `PARAMETER`, `LANGUAGE`, `HOST`, `XEO`, `AUTHOR`, `ADMINISTRATOR`, `SLUG` | `VIEW`, `CREATE`, `UPDATE`, `DELETE` |
| `SITE` | `DASHBOARD`, `SEARCH`, `PREVIEW` |
| `RBAC` | `VIEW`, `REFRESH` |
| `ADDON` | `VIEW`, `UPDATE` (the Addons screen; activate, update, deactivate, forget) |
| `IA` | `IMPORT`, `EXPORT` (markdown export / import routes) |

`ROLE_<PREFIX>_MANAGER` per prefix and `ROLE_ADMIN = 'ADMIN'`, which holds every manager role (`:128`). Helpers: `extractPermission()`, `extractRole()`, `rbac2Id()`, `rbac2Name()`, `name2Rbac()`, `getAllRoles()`, `getAllPermissions()` (reflection on the constants), `getRolePermissions($role)` (prefix match), `getChildRoles($role)`, `rebuildAssignments()` (`:150-296`).

Routes bind one permission per action in `config/routes.php` (`withRbac([Rbac::PERMISSION_CONTENT_UPDATE])`, `:995-997`); the file upload routes of the Upload field accept `UPDATE` or `VIEW` (`:549-557`).

## `RbacInitializer` (`src/Services/RbacInitializer.php`)

Reads the `PERMISSION_*` and `ROLE_*` constants of every class given by `Modules::getRbacClasses()` (the `rbacClasses` param plus the `RbacInterface::getRbac()` of the declared modules and of the active add-ons, `src/Services/Modules.php:60-69`), writes them in the yiisoft/rbac storage with the hierarchy (role → its prefix permissions, `ADMIN` → every role) and removes obsolete items: `initialize()`, `isInSync()`, `getDiff()` (`currentRoles`, `currentPermissions`, `missingRoles`, `missingPermissions`, `obsoleteRoles`, `obsoletePermissions`) (`:35-172`). Screens: `Rbac\Index` (status, `_status` partial), `Rbac\View`, `Rbac\RefreshInit` + `Refresh` (confirmation then `initialize()`), routes `dboard.rbac*` under `PERMISSION_RBAC_VIEW` / `REFRESH` (`config/routes.php:643-668`). `AddonActivation` calls `initialize()` after each activation, update and deactivation of an add-on (`src/Services/AddonActivation.php:43-75`).

## Assignment

`Administrators\Permissions` (`PermissionForm`, route `dboard.administrators.permissions`, `config/routes.php:346-354`) edits the roles and direct permissions of an administrator through `Rbac::rebuildAssignments()`. Direct permissions need the yiisoft `Manager` built with `enableDirectPermissions: true` in the application DI (`docs/installation.md:127-142`).

## `CachedRbacManager` (`src/Services/CachedRbacManager.php`)

`ManagerInterface` decorator: `userHasPermission()` loads `getPermissionsByUserId()` once per user and answers from that array; guest checks (`null` user) and parameterised checks are delegated untouched (`:51-66`); the mutating calls (`assign`, `revoke`, `addChild`, …) delegate and reset the cache. Wired by the application: `ManagerInterface::class => static fn (Manager $manager) => new CachedRbacManager($manager)` (`docs/performance.md:75-82`). `Sidebar`, `ViewInjection::userCan()` and the middleware all go through it.
