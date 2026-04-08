# Installation

```bash
composer require blackcube/dboard
```

## Requirements

- PHP 8.2+

Optional:

- `blackcube/ssr` — required for SSR route options in Type admin forms

## Configuration

The package uses `config-plugin` for automatic Yii3 registration:

| Config file | Environment | Content |
|---|---|---|
| `config/common/params.php` | All | Package parameters |
| `config/common/di.php` | All | DI container definitions |
| `config/web/params.php` | Web | View injection registration |
| `config/web/bootstrap.php` | Web | Widgets initialization (RBAC, URL generator, session) |
| `config/console/params.php` | Console | CLI commands |
| `config/routes.php` | Web | Route definitions |

### Parameters

```php
'blackcube/dboard' => [
    'debug' => false,
    'routePrefix' => '/dboard',          // route namespace
    'rbacClasses' => [Rbac::class],       // classes defining RBAC items
    'adminTemplatesAlias' => null,        // Yii alias for custom admin views
    'oauth2' => [
        'issuer' => 'dboard',
        'publicKey' => '',                // path to RS256 public key
        'privateKey' => '',               // path to RS256 private key
        'algorithm' => 'RS256',
    ],
    'webauthn' => [
        'rpId' => null,                   // relying party ID (auto-derived from domain)
        'rpName' => 'Blackcube Admin',
        'timeout' => 60000,               // ms
        'challengeLength' => 32,          // bytes
    ],
],
```

### DI bindings

| Service | Purpose |
|---|---|
| `CypherKey` | JWT signing from `oauth2` config |
| `ScopeProvider` | OAuth2 scopes |
| `DboardConfig` | Aggregated package configuration |
| `ViewInjection` | RBAC + SSR routes injected into views |
| `WebauthnConfig` | WebAuthn relying party settings |

### Alias

`@dboard` points to the package `src/` directory.

## Migrations

The package registers its migration namespace automatically:

```php
'yiisoft/db-migration' => [
    'sourceNamespaces' => [
        'Blackcube\Dboard\Migrations',
    ],
],
```

5 tables are created:

| Table | Purpose |
|---|---|
| `administrators` | Admin users |
| `passkeys` | WebAuthn credentials |
| `rbacItems` | RBAC roles and permissions |
| `rbacAssignments` | RBAC user assignments |
| `refreshTokens` | OAuth2 refresh tokens |

```bash
./yii migrate/up
```

## Console commands

| Command | Purpose |
|---|---|
| `dboard:refreshAaguid` | Import/refresh WebAuthn AAGUID device registry |

## Authentication

Two methods:

- **JWT (OAuth2)** — password grant via `blackcube/oauth2`, access token in cookie (`HttpOnly=false`), refresh token in cookie (`HttpOnly=true`)
- **Passkeys (WebAuthn)** — FIDO2 passwordless authentication via `web-auth/webauthn-lib` (see [webauthn.md](webauthn.md))

## RBAC

To enable direct permission assignment (not just roles):

```php
use Yiisoft\Rbac\Manager;

return [
    ManagerInterface::class => static fn (
        ItemsStorageInterface $itemsStorage,
        AssignmentsStorageInterface $assignmentsStorage,
    ) => new Manager(
        itemsStorage: $itemsStorage,
        assignmentsStorage: $assignmentsStorage,
        enableDirectPermissions: true,
    ),
];
```

See [rbac.md](rbac.md) for the full permission/role reference.
