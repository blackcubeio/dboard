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
| `config/common/di/*.php` | All | DI container definitions (dboard, oauth2) |
| `config/common/bootstrap.php` | All | `@dboard` alias registration |
| `config/web/params.php` | Web | View injection registration |
| `config/routes.php` | Web | Route definitions |

### Parameters

```php
'blackcube/dboard' => [
    'debug' => true,
    'routePrefix' => '/dboard',          // route namespace
    'rbacClasses' => [Rbac::class],       // classes defining RBAC items
    'adminTemplatesAlias' => null,        // Yii alias for custom admin views
    'oauth2' => [
        'issuer' => 'dboard',
        'rsPublicKey' => '',              // path to RS* public key
        'rsPrivateKey' => '',             // path to RS* private key
        'hsKey' => '',                    // shared secret, HS* algorithms only
        'algorithm' => 'RS256',
        'temporaryPath' => null,          // temporary directory, defaults to the system one
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
| `CypherKey` (`oauth2`) | JWT signing from `oauth2` config: with `RS*` the two paths are read when a token is signed and the pair is generated on first use when it is missing, with `HS*` the `hsKey` secret is used as it is given |
| `DboardConfig` | Aggregated package configuration; extends `Blackcube\Handler\HandlerConfig` (views alias, layout and debug of the panel handlers) |
| `WebauthnConfig` | WebAuthn relying party settings |

`ScopeProvider` and `ViewInjection` are resolved by autowiring (no explicit definition).

### Alias

`@dboard` points to the package `src/` directory.

## Migrations

The application registers the migration namespace in its own params:

```php
'yiisoft/db-migration' => [
    'sourceNamespaces' => [
        'Blackcube\Dboard\Migrations',
    ],
],
```

9 tables are created:

| Table | Purpose |
|---|---|
| `administrators` | Admin users |
| `passkeyDevices` | WebAuthn device metadata |
| `passkeys` | WebAuthn credentials |
| `yii_rbac_item` | RBAC roles and permissions |
| `yii_rbac_item_child` | RBAC hierarchy |
| `yii_rbac_assignment` | RBAC user assignments |
| `refreshTokens` | OAuth2 refresh tokens |
| `yii_source_message` | i18n source messages |
| `yii_message` | i18n translations |

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
