# dboard — Addons

An addon is an array in the `addons` parameter of `blackcube/dboard`; each key is a static callable the panel calls at configuration time (`config/common/di/dboard.php:17-34`, `config/routes.php:185-190`, `src/Widgets/Sidebar.php:309-319`). The four interfaces of `src/Addons/` name the expected signatures:

| Key | Interface | Returns | Used by |
|---|---|---|---|
| `routes` | `AddonRoutesInterface::getRoutesGroup()` | a yiisoft `Group` | appended to the route list of `config/routes.php` (`:185-190`) |
| `sidebar` | `AddonSidebarInterface::getSidebar(?TranslatorInterface, UrlGeneratorInterface, ManagerInterface, string $userId, ?string $currentRoute)` | `SidebarItem[]` (Bleet) | `Sidebar::render()`, after the dboard entries (`src/Widgets/Sidebar.php:309-319`) |
| `rbac` | `AddonRbacInterface::getRbac()` | a class name holding `PERMISSION_*` / `ROLE_*` constants | added to `DboardConfig::$rbacClasses`, read by `RbacInitializer` (`config/common/di/dboard.php:23-25`) |
| `population` + `scopes` | `AddonOauth2Interface::getPopulation()`, `getScopes()` | the population name; the permissions its resource can be granted | pooled per population into `ScopeProvider` and `AddonPopulations` (`config/common/di/dboard.php:26-33`, `:47-58`) |
| `configRoute` | — | a route name | the `Addons\Index` page links to the addon configuration (`src/Handlers/Addons/Index.php:18-19`) |

```php
'blackcube/dboard' => [
    'addons' => [
        'blerp' => [
            'routes' => [BlerpAddon::class, 'getRoutesGroup'],
            'sidebar' => [BlerpAddon::class, 'getSidebar'],
            'rbac' => [BlerpAddon::class, 'getRbac'],
            'population' => [BlerpAddon::class, 'getPopulation'],
            'scopes' => [BlerpAddon::class, 'getScopes'],
            'configRoute' => 'blerp.settings',
        ],
    ],
],
```

An addon handler extends the dboard abstracts and overrides `getViewsAlias()` (`ViewsAliasInterface`) to render its own views inside the dboard layout (`src/Handlers/Commons/ViewsAliasInterface.php:13-16`). Its routes sit in its own group, guarded with `JwtAuthMiddleware::withRbac([...])` on its own constants.

## Population authorization server

For an addon whose resource lives outside the panel, dboard is an OAuth2 authorization server per population (`src/Services/AddonPopulation.php:25-29`): the administrators are the users, clients are public and dynamically registered, codes are exchanged with PKCE, tokens are signed with the dboard `CypherKey`, the population is the `iss` and `aud` claims. `PopulationConfig`: grants `authorization_code` + `refresh_token`, `OauthClient` as client class, `AuthorizationCode` storage, access TTL 3600 s, refresh 30 days, code 60 s (`:44-94`).

Routes (`config/routes.php:192-194`, `:1523-1546`), `{population:[a-z0-9]+}`:

| Route | Handler | Role |
|---|---|---|
| `GET /.well-known/oauth-authorization-server<prefix>/oauth/{population}` | `Oauth\Metadata` | RFC 8414 document |
| `POST <prefix>/oauth/{population}/register` | `Oauth\Register` | RFC 7591 dynamic registration, public clients only |
| `GET|POST <prefix>/oauth/{population}/authorize` | `Oauth\Authorize` | consent screen: the published scopes bounded by the administrator permissions; POST issues the code with the PKCE challenge (`src/Handlers/Oauth/Authorize.php:39-45`) |
| `POST <prefix>/oauth/{population}/token` | `Oauth\Token` | code (+ verifier) or refresh token → tokens |

`ScopeProvider` (`src/Services/ScopeProvider.php:26-58`): the available scopes are the union of the published ones; a client asks by default everything its population publishes. `AddonPopulations::get($name)` / `names()` give the populations (`src/Services/AddonPopulations.php:42-63`). `OauthClient` (`src/Models/OauthClient.php:30`) carries `population`, `redirectUris`, `allowedGrants`; `AuthorizationCode` the code, PKCE challenge and method (`src/Models/AuthorizationCode.php:26`).

On the resource side, `AccessTokenValidator::validate($token, $population)` checks the signature with the dboard key, `iss` and `aud` equal to the population, the expiration and an active administrator, and returns `['administrator' => Administrator, 'permissions' => granted scopes ∩ permissions the administrator holds now]` (`src/Services/AccessTokenValidator.php:19-70`): a right lost in dboard is lost on the resource at once.
