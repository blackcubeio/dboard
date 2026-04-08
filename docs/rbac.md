# RBAC — Roles and Permissions

Granular permission system for the admin panel. Each entity has individual permissions aggregated into manager roles.

## Permissions

### Content

| Constant | Value |
|---|---|
| `PERMISSION_CONTENT_VIEW` | `CONTENT:VIEW` |
| `PERMISSION_CONTENT_CREATE` | `CONTENT:CREATE` |
| `PERMISSION_CONTENT_UPDATE` | `CONTENT:UPDATE` |
| `PERMISSION_CONTENT_DELETE` | `CONTENT:DELETE` |
| `PERMISSION_CONTENT_IMPORT` | `CONTENT:IMPORT` |
| `PERMISSION_CONTENT_EXPORT` | `CONTENT:EXPORT` |

### Tag

| Constant | Value |
|---|---|
| `PERMISSION_TAG_VIEW` | `TAG:VIEW` |
| `PERMISSION_TAG_CREATE` | `TAG:CREATE` |
| `PERMISSION_TAG_UPDATE` | `TAG:UPDATE` |
| `PERMISSION_TAG_DELETE` | `TAG:DELETE` |
| `PERMISSION_TAG_IMPORT` | `TAG:IMPORT` |
| `PERMISSION_TAG_EXPORT` | `TAG:EXPORT` |

### Menu

| Constant | Value |
|---|---|
| `PERMISSION_MENU_VIEW` | `MENU:VIEW` |
| `PERMISSION_MENU_CREATE` | `MENU:CREATE` |
| `PERMISSION_MENU_UPDATE` | `MENU:UPDATE` |
| `PERMISSION_MENU_DELETE` | `MENU:DELETE` |

### Elastic Schema

| Constant | Value |
|---|---|
| `PERMISSION_ELASTICSCHEMA_VIEW` | `ELASTICSCHEMA:VIEW` |
| `PERMISSION_ELASTICSCHEMA_CREATE` | `ELASTICSCHEMA:CREATE` |
| `PERMISSION_ELASTICSCHEMA_UPDATE` | `ELASTICSCHEMA:UPDATE` |
| `PERMISSION_ELASTICSCHEMA_DELETE` | `ELASTICSCHEMA:DELETE` |

### Type

| Constant | Value |
|---|---|
| `PERMISSION_TYPE_VIEW` | `TYPE:VIEW` |
| `PERMISSION_TYPE_CREATE` | `TYPE:CREATE` |
| `PERMISSION_TYPE_UPDATE` | `TYPE:UPDATE` |
| `PERMISSION_TYPE_DELETE` | `TYPE:DELETE` |

### Language, Host, Xeo, Author, Parameter, Slug

Same pattern: `PREFIX:VIEW`, `PREFIX:CREATE`, `PREFIX:UPDATE`, `PREFIX:DELETE`.

### Administrator

| Constant | Value |
|---|---|
| `PERMISSION_ADMINISTRATOR_VIEW` | `ADMINISTRATOR:VIEW` |
| `PERMISSION_ADMINISTRATOR_CREATE` | `ADMINISTRATOR:CREATE` |
| `PERMISSION_ADMINISTRATOR_UPDATE` | `ADMINISTRATOR:UPDATE` |
| `PERMISSION_ADMINISTRATOR_DELETE` | `ADMINISTRATOR:DELETE` |

### RBAC

| Constant | Value |
|---|---|
| `PERMISSION_RBAC_VIEW` | `RBAC:VIEW` |
| `PERMISSION_RBAC_UPDATE` | `RBAC:UPDATE` |

### AI

| Constant | Value |
|---|---|
| `PERMISSION_IA_EXPORT` | `IA:EXPORT` |
| `PERMISSION_IA_IMPORT` | `IA:IMPORT` |

### Site

| Constant | Value |
|---|---|
| `PERMISSION_SITE_DASHBOARD` | `SITE:DASHBOARD` |
| `PERMISSION_SITE_SEARCH` | `SITE:SEARCH` |
| `PERMISSION_SITE_PREVIEW` | `SITE:PREVIEW` |

## Roles

Each entity has a `MANAGER` role that aggregates all its permissions (matching prefix).

| Role | Permissions included |
|---|---|
| `CONTENT:MANAGER` | VIEW, CREATE, UPDATE, DELETE, IMPORT, EXPORT |
| `TAG:MANAGER` | VIEW, CREATE, UPDATE, DELETE, IMPORT, EXPORT |
| `MENU:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `TYPE:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `ELASTICSCHEMA:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `LANGUAGE:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `HOST:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `XEO:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `AUTHOR:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `PARAMETER:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `SLUG:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `ADMINISTRATOR:MANAGER` | VIEW, CREATE, UPDATE, DELETE |
| `RBAC:MANAGER` | VIEW, UPDATE |
| `IA:MANAGER` | EXPORT, IMPORT |
| `SITE:MANAGER` | DASHBOARD, SEARCH, PREVIEW |

### ADMIN role

`ADMIN:MANAGER` inherits **all** other roles. An ADMIN has every permission in the system.

### Hierarchy

```
ADMIN:MANAGER
├── CONTENT:MANAGER
├── TAG:MANAGER
├── MENU:MANAGER
├── TYPE:MANAGER
├── ELASTICSCHEMA:MANAGER
├── LANGUAGE:MANAGER
├── HOST:MANAGER
├── XEO:MANAGER
├── AUTHOR:MANAGER
├── PARAMETER:MANAGER
├── SLUG:MANAGER
├── ADMINISTRATOR:MANAGER
├── RBAC:MANAGER
├── IA:MANAGER
└── SITE:MANAGER
```

## RbacInitializer

Synchronizes RBAC items from code constants to database.

```php
$initializer->initialize(): void        // create/update items and hierarchy
$initializer->isInSync(): bool           // check if DB matches code
$initializer->getDiff(): array           // detailed diff
```

`getDiff()` returns:

```php
[
    'currentRoles' => [...],
    'currentPermissions' => [...],
    'missingRoles' => [...],
    'missingPermissions' => [...],
    'obsoleteRoles' => [...],
    'obsoletePermissions' => [...],
]
```

The initializer reflects all classes listed in `rbacClasses` param for `PERMISSION_*` and `ROLE_*` constants, builds the hierarchy (roles inherit permissions by matching prefix, ADMIN inherits all roles), and cleans obsolete items.

## Utility methods

| Method | Description |
|---|---|
| `Rbac::extractPermission(string)` | Extract name from `PREFIX:NAME` |
| `Rbac::extractRole(string)` | Extract name from `PREFIX:MANAGER` |
| `Rbac::rbac2Id(string)` | Convert to slug format |
| `Rbac::rbac2Name(string)` | Convert to camelCase |
| `Rbac::name2Rbac(string)` | Convert camelCase to RBAC format |
| `Rbac::getAllRoles()` | All roles in display order |
| `Rbac::getAllPermissions()` | All permissions via reflection |
| `Rbac::getPermissionsByRole(string)` | Permissions for a role (prefix match) |
| `Rbac::getChildRoles(string)` | Child roles (ADMIN contains all) |
| `Rbac::rebuildAssignments(array, array, array)` | Compute new assignments from selected roles/permissions |

## Custom RBAC classes

Additional RBAC classes can be registered via the `rbacClasses` parameter. Each class must define `PERMISSION_*` and/or `ROLE_*` constants following the same `PREFIX:ACTION` / `PREFIX:MANAGER` convention.
