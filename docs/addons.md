# Add-ons and modules

A Composer package plugs into the panel through static interfaces: it brings its RBAC items, its sidebar entries, its routes and its OAuth2 population, and the application copies no block of configuration. Two kinds of packages do so:

| Kind | Declared in | State | Addons screen |
|---|---|---|---|
| **Add-on** | `'blackcube/dcore' => 'addons'`, by its own params | activated and deactivated by the administrator, with its migrations | listed |
| **Module** | `'blackcube/dboard' => 'modules'`, by its own params | always on, no migration, no activation | not listed |

dcore keeps the registry of the add-ons and plays their migrations (see the `docs/addons.md` of `blackcube/dcore`). dboard adds what belongs to the panel: the interfaces, the activation that keeps RBAC and OAuth2 clients in line, the Addons screen and the console commands.

## Interfaces

All in `Blackcube\Dboard\Interfaces\`, all optional, all static. A class implements the ones it needs.

| Interface | Method | Role |
|---|---|---|
| `RbacInterface` | `getRbac(): string` | The class holding the `PERMISSION_*` and `ROLE_*` constants of the package. Its items are written in the RBAC storage and listed on the permission screens. |
| `SidebarInterface` | `getSidebar(?TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator, ManagerInterface $rbacManager, string $userId, ?string $currentRoute): array` | The `SidebarItem` list (Bleet) appended after the entries of the panel. The package filters them itself on the permissions of the administrator. |
| `RoutesInterface` | `getRoutesGroup(): Group` | The yiisoft `Group` of the package, mounted under the route prefix of the panel. |
| `Oauth2Interface` | `getPopulation(): string`, `getScopes(): array` | The OAuth2 population of the resource and the permissions a client can be granted on it. Packages naming the same population pool their scopes. |
| `AddonConfigureInterface` | `getConfigureRoute(): string` | The route name of the configuration screen of an add-on: the Addons screen shows a `Configure` button on an active add-on. |

`Blackcube\Dboard\Services\Modules` reads them: the declared modules first, then the **active** add-ons. An inactive add-on brings nothing. The registry is read on each call, an activation is seen at once.

## Declaring an add-on

The class implements `Blackcube\Dcore\Addons\AddonInterface` and the interfaces of the panel it needs:

```php
use Blackcube\Dboard\Interfaces\AddonConfigureInterface;
use Blackcube\Dboard\Interfaces\RbacInterface;
use Blackcube\Dboard\Interfaces\RoutesInterface;
use Blackcube\Dboard\Interfaces\SidebarInterface;
use Blackcube\Dboard\Middlewares\JwtAuthMiddleware;
use Blackcube\Dcore\Addons\AddonInterface;

class NewsletterAddon implements AddonInterface, RbacInterface, SidebarInterface, RoutesInterface, AddonConfigureInterface
{
    public static function getName(): string
    {
        return 'Newsletter';
    }

    public static function getPackage(): string
    {
        return 'acme/newsletter';
    }

    public static function getMigrationsNamespace(): ?string
    {
        return 'Acme\\Newsletter\\Migrations';
    }

    public static function getRbac(): string
    {
        return NewsletterRbac::class;
    }

    public static function getConfigureRoute(): string
    {
        return 'newsletter.lists';
    }

    public static function getSidebar(
        ?TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ManagerInterface $rbacManager,
        string $userId,
        ?string $currentRoute,
    ): array {
        $items = [];
        if ($rbacManager->userHasPermission($userId, NewsletterRbac::PERMISSION_NEWSLETTER_VIEW) === true) {
            $items[] = Bleet::sidebarItem('Newsletter')
                ->outline('envelope')
                ->url($urlGenerator->generate('newsletter.lists'));
        }
        return $items;
    }

    public static function getRoutesGroup(): Group
    {
        return Group::create('/newsletter')
            ->routes(
                Route::get('/lists')
                    ->middleware(JwtAuthMiddleware::withRbac([
                        NewsletterRbac::PERMISSION_NEWSLETTER_VIEW,
                    ]))
                    ->action(ListsIndex::class)
                    ->name('newsletter.lists'),
            );
    }
}
```

The package declares it in its own `params`, merged by `yiisoft/config`:

```php
return [
    'blackcube/dcore' => [
        'addons' => [
            'newsletter' => NewsletterAddon::class,
        ],
    ],
];
```

The RBAC class follows the convention of the panel: `PERMISSION_<PREFIX>_<ACTION> = 'PREFIX:ACTION'`, `ROLE_<PREFIX>_MANAGER = 'PREFIX:MANAGER'` (see [rbac.md](rbac.md)).

## Declaring a module

A package that needs no activation declares its class to the panel, in its own `params`:

```php
return [
    'blackcube/dboard' => [
        'modules' => [
            StatisticsModule::class,
        ],
    ],
];
```

The class implements the interfaces above, and not `AddonInterface`. Its RBAC items are written by the RBAC refresh of the panel (RBAC screen, `Refresh`), as the ones of the panel are.

`rbacClasses` keeps its role: the RBAC classes of the panel itself and of the application.

## Routes

The groups are mounted inside the group of the panel, under `routePrefix`, behind `CsrfTokenMiddleware`, `JsonBodyParserMiddleware` and `LocaleMiddleware`. The package guards each of its routes with `JwtAuthMiddleware::withRbac([...])` on its own permissions.

- The group of a **module** is mounted as it comes.
- The group of an **add-on** goes behind `Blackcube\Dboard\Middlewares\AddonRoutingMiddleware`: its routes answer `404` while the add-on is inactive.

An add-on that mounts routes of its own outside the panel puts the same middleware on them, first in the stack:

```php
Route::get('/newsletter/subscribe')
    ->middleware(AddonRoutingMiddleware::of('newsletter'))
    ->action(Subscribe::class)
    ->name('newsletter.subscribe'),
```

A handler of the package extends the abstract handlers of the panel and implements `ViewsAliasInterface::getViewsAlias()` to render its own views inside the layout of the panel.

## Activation

`Blackcube\Dboard\Services\AddonActivation` carries the complete sequences, shared by the Addons screen and the console:

| Method | Sequence |
|---|---|
| `activate(string $id): void` | dcore plays the migrations of the add-on and writes its row, then the RBAC items are synchronized. |
| `update(string $id): void` | dcore plays the migrations a newer package brought and records the version, then the RBAC items are synchronized. |
| `deactivate(string $id): void` | dcore reverts the migrations and removes the row, the OAuth2 clients of the population are deleted when no other package publishes it, then the RBAC items are synchronized: the items of the add-on and their assignments are removed. |
| `forget(string $id): void` | The row of an add-on whose package is gone from Composer is removed, its tables are left as they are. |

Deactivation deletes the tables of the add-on and their data. It is refused before anything is touched when one of its migrations is not revertible.

## Addons screen

`<prefix>/addons` lists the declared add-ons with their id, their installed version and their state:

| State | Meaning | Actions |
|---|---|---|
| Inactive | declared, no row in the registry | Activate |
| Active | row in the registry, migrations up to date | Configure (with `AddonConfigureInterface`), Deactivate |
| Update available | active, the installed package carries migrations the base has not played | Update, Configure, Deactivate |
| Package missing | row in the registry, add-on not declared any more | Forget |

Each action opens a confirmation dialog; once done, the list is reloaded and a toast confirms.

| Permission | Gives |
|---|---|
| `ADDON:VIEW` | the screen and the `Addons` entry of the sidebar |
| `ADDON:UPDATE` | activate, update, deactivate, forget |

The role `ADDON:MANAGER` holds both.

## Console commands

| Command | Purpose |
|---|---|
| `dboard:addonList` | Table of the add-ons: id, name, version, status (`inactive`, `active`, `update available`, `package missing`) |
| `dboard:addonActivate <id>` | Activates a declared, inactive add-on |
| `dboard:addonUpdate <id>` | Plays the pending migrations of an active add-on |
| `dboard:addonDeactivate <id>` | Deactivates an active add-on, after a confirmation that defaults to no |

```bash
./yii dboard:addonList
./yii dboard:addonActivate newsletter
```

The commands run the same sequences as the screen, RBAC and OAuth2 clients included.

## OAuth2 population

A package whose resource lives outside the panel (an API, a server of its own) implements `Oauth2Interface`. dboard is then the authorization server of that population: the administrators are its users, the clients register themselves and exchange their codes with PKCE.

| Route | Role |
|---|---|
| `GET /.well-known/oauth-authorization-server<prefix>/oauth/{population}` | Authorization server metadata (RFC 8414) |
| `POST <prefix>/oauth/{population}/register` | Dynamic client registration (RFC 7591), public clients |
| `GET\|POST <prefix>/oauth/{population}/authorize` | Consent screen: the published scopes the administrator holds |
| `POST <prefix>/oauth/{population}/token` | Code or refresh token exchanged for tokens |

A population nobody publishes answers `404`: the one of an add-on exists while the add-on is active.

On the resource side, `AccessTokenValidator::validate($token, $population)` returns the administrator and the granted scopes the administrator still holds, or `null`.

## Upgrading an existing base

The migrations of the panel are named `M1` + 11 digits (see the numbering in the dcore documentation). A base migrated under the former names gets its history rewritten by the upgrade the package declares, `Blackcube\Dboard\Upgrades\RenameMigrations`:

```bash
composer update
./yii upgrade
./yii migrate/up
```

The block `'blackcube/dboard' => 'addons'` of the application params is gone: an add-on formerly declared there now declares itself, and is activated from the Addons screen or by `dboard:addonActivate`. Its migration namespace leaves the `sourceNamespaces` of the application.
