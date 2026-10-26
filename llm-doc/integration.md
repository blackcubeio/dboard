# dboard — Integration

## Split

- Package: routes, handlers, views, layouts, forms, middlewares, RBAC constants, OAuth2 population server, migrations, translations, compiled assets.
- Application: the Yii3 web application (`yiisoft/config`, router, session, cookies, CSRF, view renderer, error handler), the database connection and `yiisoft/db-migration` with the dboard namespace, the RBAC manager wiring (`CachedRbacManager`, direct permissions), the yiisoft `AssetManager` with `@assets` / `@assetsUrl`, `blackcube/dcore` and `blackcube/fileprovider` configured, the i18n translator with `Dboard::getI18nSources()`, the `oauth2` key paths, and the layout (`main.php`) when the dboard one is not wanted.

## config-plugin (`config/configuration.php:12-20`)

| Group | File | Content |
|---|---|---|
| `params` | `config/common/params.php` | `blackcube/dboard`: `debug` true, `routePrefix` `/dboard`, `rbacClasses` `[Rbac::class]`, `addons` `[]`, `oauth2` (`issuer` `dboard`, `algorithm` `RS256`, `rsPublicKey`, `rsPrivateKey`, `hsKey`, `temporaryPath`), `adminTemplatesAlias` null, `webauthn` (`rpId` null, `rpName` `Blackcube Admin`, `timeout` 60000, `challengeLength` 32) (`:14-36`) |
| `params-web` | `config/web/params.php` | empty (`:11`) |
| `di` | `config/common/di/dboard.php` | `DboardConfig` (debug, templates alias, prefix, RBAC classes with the addon ones, addons), `ScopeProvider` and `AddonPopulations` with the pooled published scopes (`:36-58`) |
| `di` | `config/common/di/oauth2.php` | `CypherKey` from the `oauth2` params, `WebauthnConfig` from the `webauthn` params |
| `bootstrap` | `config/common/bootstrap.php` | `@dboard` → the package `src/` (`:15-19`) |
| `routes` | `config/routes.php` | one `Group` on `routePrefix` with `CsrfTokenMiddleware`, `JsonBodyParserMiddleware`, `LocaleMiddleware` (`:197-200`), one sub-group per module, the addon groups, the population OAuth group and the well-known route |
| console | `config/console/commands.php` | `dboard:refreshAaguid` → `Commands\RefreshAaguidCommand` |

`DboardConfig` (`src/DboardConfig.php:26-46`) extends `Blackcube\Handler\HandlerConfig`: `handlerNamespacePrefix` `Blackcube\Dboard\Handlers\`, `layoutAlias` `<viewsAlias>/Layouts/main.php`; its own type keeps the panel handlers apart from the `HandlerConfig` of the application. It also carries `viewsAlias` (`@dboard/Views`), `cypherKey`, `scopeProvider`, `webauthnConfig`, not set by the package DI.

## Left to the application

```php
// RBAC: direct permissions + per-request cache
ManagerInterface::class => static fn (ItemsStorageInterface $items, AssignmentsStorageInterface $assignments): ManagerInterface
    => new CachedRbacManager(new Manager(itemsStorage: $items, assignmentsStorage: $assignments, enableDirectPermissions: true)),

// Translations from the database
MessageFormatterInterface::class => IntlMessageFormatter::class,
...Dboard::getI18nSources(),

// Migrations
'yiisoft/db-migration' => ['sourceNamespaces' => ['Blackcube\Dboard\Migrations']],
```

`ViewInjection` is listed by the application in the `injections` of its `yiisoft/yii-view-renderer` params (`Reference::to(\Blackcube\Dboard\Services\ViewInjection::class)`); `LocaleMiddleware` must precede `JwtAuthMiddleware` (both are placed by the routes file). `SessionMiddleware` of the application must wrap the dboard group (forms, previews, list modes and WebAuthn challenges use the session).

## Migrations (`src/Migrations/`)

`administrators`, `passkeyDevices` + `passkeys`, `yii_rbac_item` + `yii_rbac_item_child` + `yii_rbac_assignment`, `refreshTokens`, `yii_source_message` + `yii_message` seeded from `src/i18n/en/<category>.php` (sources) and the other locales (`M000000000005CreateTranslations.php:47-104`), `oauthClients`, `authorizationCodes`. The onboarding wizard (`<prefix>/onboarding/step1..3`, `finish`) creates the first administrator and initializes RBAC.

## Assets

`AppAsset` publishes `@dboard/Assets/App/dist-webpack` through `blackcube/assets` `WebpackAssetBundle` (`src/Assets/AppAsset.php:22-26`); the sources are `src/Assets/App/src` (Aurelia 2 + Tailwind, built with `npm run dist-clean`, `package.json`), both `dist-webpack` and `dist-vite` are committed. `FaviconAsset`, `StaticAsset`, `PasskeysAsset` are plain bundles.

## Translations

Five categories (`dboard-common`, `dboard-content`, `dboard-modules`, `dboard-onboarding`, `dboard-builtin`) served by `yiisoft/translator-message-db` through `Dboard::getI18nSources()` (`src/Dboard.php:26-50`); the PHP files under `src/i18n/<locale>/` (de, en, es, fr, it, pt) are the seed, not the runtime source. Form labels go through `Models\Forms\BridgeFormModel::$translateCategory`.

## Tests

One Codeception suite, `Integration` (`tests/Integration.suite.yml`): `ExportImportRoundTripCest`, `FormsIntegrationCest`, on a real MySQL database through `tests/Support/DatabaseCestTrait.php` and `MysqlHelper.php`; `vendor/bin/codecept run`.
