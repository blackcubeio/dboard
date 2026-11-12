# dboard — Add-ons and modules

A Composer package plugs into the panel through the static interfaces of `src/Interfaces/`; `Modules` (`src/Services/Modules.php:33`) reads them. Two kinds of classes:

| Kind | Declared in | State |
|---|---|---|
| Add-on | `'blackcube/dcore' => ['addons' => [id => class]]`, class implements `Blackcube\Dcore\Addons\AddonInterface` | registry and migrations in dcore (`AddonService`); brings its interfaces only while active |
| Module | `'blackcube/dboard' => ['modules' => [class]]` (`config/common/params.php:27-30`) | always on, no migration, absent from the Addons screen |

Both declare themselves in the `params` of their own package. `Modules::getClasses()` = declared modules, then active add-ons, each class once (`:50-53`); read on each call.

| Interface | Method | Returns | Read by |
|---|---|---|---|
| `RbacInterface` (`:24`) | `getRbac()` | a class name holding `PERMISSION_*` / `ROLE_*` constants | `Modules::getRbacClasses()` (`src/Services/Modules.php:60-69`) → `RbacInitializer`, `Rbac::getAllRoles()` / `getAllPermissions()` |
| `SidebarInterface` (`:29`) | `getSidebar(?TranslatorInterface, UrlGeneratorInterface, ManagerInterface, string $userId, ?string $currentRoute)` | `SidebarItem[]` (Bleet), filtered by the class on its own permissions | `Modules::getSidebarItems()` (`:98-118`) → `Sidebar::render()`, after the dboard entries (`src/Widgets/Sidebar.php:322-328`) |
| `RoutesInterface` (`:26`) | `getRoutesGroup()` | a yiisoft `Group` | `config/routes.php:196-210`, spread in the main group (`:1592`) |
| `Oauth2Interface` (`:27`) | `getPopulation()`, `getScopes()` | the population name; the permissions its resource can be granted | `Modules::getPublishedScopes()` (`:77-91`), pooled per population → `ScopeProvider`, `Populations` |
| `AddonConfigureInterface` (`:23`) | `getConfigureRoute()` | a route name | `Configure` button of an active add-on (`src/Views/Addons/_list.php:68-76`) |

`rbacClasses` (`config/common/params.php`) stays the list of the RBAC classes of the panel and of the application; `Modules::getRbacClasses()` appends the ones of the modules.

```php
// params of the package
'blackcube/dcore' => [
    'addons' => [
        'blerp' => BlerpAddon::class,
    ],
],
```

```php
class BlerpAddon implements AddonInterface, RbacInterface, SidebarInterface, RoutesInterface, AddonConfigureInterface
{
    public static function getName(): string { return 'Blerp'; }
    public static function getPackage(): string { return 'acme/blerp'; }
    public static function getMigrationsNamespace(): ?string { return 'Acme\\Blerp\\Migrations'; }
    public static function getRbac(): string { return BlerpRbac::class; }
    public static function getConfigureRoute(): string { return 'blerp.settings'; }
    public static function getSidebar(...): array { /* SidebarItem[] */ }
    public static function getRoutesGroup(): Group { /* Group::create('/blerp')->routes(...) */ }
}
```

## Routes

The groups are collected at configuration time (`config/routes.php:196-210`) and mounted inside the group of the panel: `routePrefix`, `CsrfTokenMiddleware`, `JsonBodyParserMiddleware`, `LocaleMiddleware` (`:217-220`). A module group is mounted as it comes (`:200-204`). An add-on group gets `AddonRoutingMiddleware::of($addonId)` prepended (`:205-210`; `src/Middlewares/AddonRoutingMiddleware.php:40`): `process()` answers `404` while the add-on is inactive (`:57`). A route the add-on mounts outside the panel takes the same definition as its first middleware (`blackcube/mcp` `config/routes.php:35`, `:41`: the endpoint and its metadata route). Each route is guarded by the package with `JwtAuthMiddleware::withRbac([...])` on its own constants.

A handler of the package extends the dboard abstracts and implements `getViewsAlias()` (`src/Handlers/Commons/ViewsAliasInterface.php:22-24`) to render its own views inside the dboard layout.

## Activation

`AddonActivation` (`src/Services/AddonActivation.php:30`) wraps the dcore `AddonService` with what the panel owns; the Addons screen and the console call nothing else.

| Method | Sequence |
|---|---|
| `activate($id)` (`:43-47`) | `AddonService::activate()` (migrations up, row written), `RbacInitializer::initialize()` |
| `update($id)` (`:52-56`) | `AddonService::update()` (new migrations, version recorded), `RbacInitializer::initialize()` |
| `deactivate($id)` (`:64-75`) | `AddonService::deactivate()` (migrations down, row removed; refused before anything is touched when a migration is not revertible), `OauthClient` + `AuthorizationCode` rows of the population deleted when no remaining class publishes it (`:85-108`), `RbacInitializer::initialize()` (items and assignments of the add-on removed) |
| `forget($id)` (`:80-83`) | `AddonService::forget()`: row removed, tables untouched (package gone from Composer) |

## Addons screen

Routes (`config/routes.php:1542-1591`): `GET <prefix>/addons` → `Addons\Index` (`dboard.addons`, `Rbac::PERMISSION_ADDON_VIEW`); `GET` + `POST <prefix>/addons/{id}/activate|update|deactivate|forget` → `Addons\<Action>Init` (dialog, route name `dboard.addons.<action>`) and `Addons\<Action>` (`Rbac::PERMISSION_ADDON_UPDATE`). `ROLE_ADDON_MANAGER` holds both permissions (`src/Components/Rbac.php:120-122`). Sidebar entry `Addons` under `PERMISSION_ADDON_VIEW` (`src/Widgets/Sidebar.php:330-338`).

Handlers in `src/Handlers/Addons/`: `AbstractActionInit` renders the confirmation dialog (`:41`), `AbstractAction` runs `act($id)` then closes the dialog, reloads `addons-list` and toasts (`:77`); both answer `404` when `isOpen($id)` is false. One row per add-on (`src/Views/Addons/_list.php`):

| State | Badge | Buttons |
|---|---|---|
| declared, no row | `Inactive` | `Activate` |
| row, migrations up to date | `Active` | `Configure` (with `AddonConfigureInterface`), `Deactivate` |
| row, pending migrations | `Update available` | `Update`, `Configure`, `Deactivate` |
| row, class not declared any more | `Package missing` | `Forget` |

## Console

Group `params-console` (`config/configuration.php:12-23`, `config/console/commands.php:17-23`), classes in `src/Commands/`:

| Command | Class | Does |
|---|---|---|
| `dboard:addonList` | `AddonListCommand` | table id, name, version, status (`inactive`, `active`, `update available`, `package missing`) |
| `dboard:addonActivate <id>` | `AddonActivateCommand` | `AddonActivation::activate()` on a declared, inactive add-on |
| `dboard:addonUpdate <id>` | `AddonUpdateCommand` | `AddonActivation::update()` on an active add-on |
| `dboard:addonDeactivate <id>` | `AddonDeactivateCommand` | confirmation (default no), then `AddonActivation::deactivate()` |

## Population authorization server

For a package whose resource lives outside the panel, dboard is an OAuth2 authorization server per population (`src/Services/Population.php:25-29`): the administrators are the users, clients are public and dynamically registered, codes are exchanged with PKCE, tokens are signed with the dboard `CypherKey`, the population is the `iss` and `aud` claims. `PopulationConfig`: grants `authorization_code` + `refresh_token`, `OauthClient` as client class, `AuthorizationCode` storage, access TTL 3600 s, refresh 30 days, code 60 s (`:44-116`).

Routes (`config/routes.php:214`, `:1594-1617`), `{population:[a-z0-9]+}`:

| Route | Handler | Role |
|---|---|---|
| `GET /.well-known/oauth-authorization-server<prefix>/oauth/{population}` | `Oauth\Metadata` | RFC 8414 document |
| `POST <prefix>/oauth/{population}/register` | `Oauth\Register` | RFC 7591 dynamic registration, public clients only |
| `GET\|POST <prefix>/oauth/{population}/authorize` | `Oauth\Authorize` | consent screen: the published scopes bounded by the administrator permissions; POST issues the code with the PKCE challenge (`src/Handlers/Oauth/Authorize.php:39-45`) |
| `POST <prefix>/oauth/{population}/token` | `Oauth\Token` | code (+ verifier) or refresh token → tokens |

`Populations::get($name)` returns `null` when no class publishes the population, and the four handlers answer `404`: the population of an add-on exists while the add-on is active (`src/Services/Populations.php:42-62`; `names()` `:64`). `ScopeProvider` (`src/Services/ScopeProvider.php:26-72`): the available scopes are the union of the published ones; a client asks by default everything its population publishes. `OauthClient` (`src/Models/OauthClient.php:30`) carries `population`, `redirectUris`, `allowedGrants`; `AuthorizationCode` the code, PKCE challenge and method (`src/Models/AuthorizationCode.php:26`).

On the resource side, `AccessTokenValidator::validate($token, $population)` checks the signature with the dboard key, `iss` and `aud` equal to the population, the expiration and an active administrator, and returns `['administrator' => Administrator, 'permissions' => granted scopes ∩ permissions the administrator holds now]` (`src/Services/AccessTokenValidator.php:30-70`): a right lost in dboard is lost on the resource at once.

## Extension

- **New add-on**: a class implementing `AddonInterface` and the interfaces it needs, declared under `blackcube/dcore.addons` in the params of its package; migrations in its own namespace, numbered `M8` + 11 digits, all revertible. Nothing to copy in the application.
- **New module**: a class implementing the interfaces only, declared under `blackcube/dboard.modules`.
- **New thing a package can bring**: a new interface in `src/Interfaces/`, read by a new method of `Modules` iterating `getClasses()` with `is_subclass_of`.
- **Something to do on activation or deactivation**: a step in the matching method of `AddonActivation`; the screen and the commands follow.
