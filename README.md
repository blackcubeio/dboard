# Blackcube DBoard

Administration panel for Blackcube CMS. Purpose-built for dcore entities — CRUD for every entity, tree management, RBAC, JWT authentication with passkey support.

[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue.svg)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/blackcube/dboard.svg)](https://packagist.org/packages/blackcube/dboard)

## Quickstart

```bash
composer require blackcube/dboard
./yii migrate/up
```

DBoard depends on dcore. It is not a standalone package — it is the admin layer of the Blackcube stack.

## Where dboard sits

```
┌──────────────────────┐
│ dboard ← you are here │
│ admin backoffice      │
└───────────┬──────────┘
            ↓
       ┌──────────┐
       │  dcore   │
       │ (data)   │
       └──────────┘
            ↓
            DB
```

## Documentation

- [Installation](docs/installation.md) — requirements, config-plugin, parameters, migrations
- [API overview](docs/index.md) — managed entities, handler architecture, services, widgets
- [RBAC](docs/rbac.md) — roles, permissions, hierarchy, assignment
- [WebAuthn](docs/webauthn.md) — passkey registration and authentication

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
