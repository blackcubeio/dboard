# blackcube/dboard — LLM index

**Role:** the administration panel of a Blackcube site, as a Yii3 package mounted under one route prefix. It gives every `blackcube/dcore` entity its screens (list, create, edit, delete, toggle, tree moves, blocs, slugs, SEO, translations, markdown export and import), authenticates administrators with JWT cookies issued by `blackcube/oauth2` (password grant or passkey), guards each route with RBAC permissions, and lets addons plug their own routes, sidebar entries, permissions and OAuth2 populations. The UI is `blackcube/bleet` widgets with the `@blackcube/aurelia-kit` behaviours; the dialogs answer the Aurelia side through `AureliaCommunication` arrays.

## Package card

| | |
|---|---|
| Namespace | `Blackcube\Dboard` |
| Entry points | `Dboard::getI18nSources()` (translation categories, `src/Dboard.php:34`), `DboardConfig` (settings object, `src/DboardConfig.php:24`), `Widgets` (facade: `sidebar()`, `preview()`, `elastic()`, `pagination()`, `src/Widgets/Widgets.php:31`) |
| Handlers | `Handlers\Commons\Abstract*` (the pipeline and one abstract per screen kind), one directory per entity (`Contents`, `Tags`, `Menus`, `LlmMenus`, `Types`, `ElasticSchemas`, `Languages`, `Hosts`, `Slugs`, `Authors`, `Parameters`, `Administrators`, `SchemasMapping`, `Xeo` global kinds), plus `Authentication`, `Account`, `Passkeys`, `Oauth`, `Onboarding`, `Import`, `Dashboard`, `Search`, `Preview`, `Rbac`, `Addons` |
| Middlewares | `JwtAuthMiddleware` (`::withRbac()`, `::optional()`), `LocaleMiddleware`, `JsonBodyParserMiddleware`, `MultiVerbParserMiddleware` |
| Services | `CachedRbacManager`, `RbacInitializer`, `ViewInjection`, `HazeltreeElasticService`, `ScopeProvider`, `AddonPopulations` / `AddonPopulation`, `AccessTokenValidator`, `PasskeyGrant`, `PasskeyService`, `WebauthnConfig`, `Client`, `RefreshToken` |
| Models | `Administrator`, `Passkey`, `PasskeyDevice`, `OauthClient`, `AuthorizationCode`, `ScopedQuery`; forms in `Models\Forms\*` on `Models\Forms\BridgeFormModel` (translated labels) |
| Components | `Rbac` (permission and role constants, helpers), `ActionModel`, `WebauthnHelper` |
| Helpers / enums | `ElasticFieldRenderer`, `RouteHelper`, `LocaleHelper`, `RelativeTimeHelper`; `ListMode`, `ReorderMode`, `TreePosition` (output types: `Blackcube\Handler\OutputType`) |
| Assets | `AppAsset` (webpack bundle, `@dboard/Assets/App/dist-webpack`, `src/Assets/AppAsset.php:22-26`), `FaviconAsset`, `StaticAsset`, `PasskeysAsset` (authenticator icons) |
| Requires | PHP ^8.2, `blackcube/bleet`, `dcore`, `fileprovider`, `handler`, `oauth2`, `resumable`, `assets`, `bridge-form-model` ^1.0, `web-auth/webauthn-lib` ^5.3, `yiisoft/active-record` ^1.1, `rbac` ^2.1, `router` + `router-fastroute` ^4.0, `session` ^3.0, `cookies` ^1.2, `translator` ^3.2 + `translator-message-db` ^1.1, `db` ^2.1 + `db-mysql` ^2.0 + `db-migration` ^2.2, `yii-view-renderer` ^7.4, `yii-console` ^2.4, `aliases`, `strings`, `vlucas/phpdotenv` (`composer.json`). Suggests `blackcube/ssr` for the SSR route options of the Type form. |
| Yii config | config-plugin (`config/configuration.php:12-20`): `config/common/params.php` (`blackcube/dboard`), `config/common/di/dboard.php` (`DboardConfig`, `ScopeProvider`, `AddonPopulations`), `config/common/di/oauth2.php` (`CypherKey`, `WebauthnConfig`), `config/common/bootstrap.php` (`@dboard` alias), `config/routes.php`, `config/console/commands.php` (`dboard:refreshAaguid`) |
| Migrations | `src/Migrations/M000000000000` … `007`: administrators, passkeys (+ devices), RBAC items / children / assignments, refresh tokens, translations (seeded from `src/i18n/<locale>/<category>.php`, `M000000000005CreateTranslations.php:47-104`), OAuth clients, authorization codes |
| License | BSD-3-Clause |

## Rules

- One pipeline for every screen: `setupAction()` → `setupMethod()` → `handleMethod()` → `prepareOutputData()` → `output()` (`src/Handlers/Commons/AbstractModelHandler.php:29-33`). A page handler lets exceptions bubble (`AbstractPageHandler.php:38-51`); an AJAX handler catches them and answers a danger toast, the message only in debug (`AbstractAjaxHandler.php:40-89`).
- A screen declares its models through `ActionModel` (`modelClass`, `formModelClass`, `formModelScenario`, `isMain`, `src/Components/ActionModel.php:34-41`); the main model with a route primary key that is not found gives a `404` (`AbstractModelHandler.php:151-153`). Non-GET bodies are loaded into every form model (`:176-184`).
- A concrete handler is a list of getters: `getModelClass()`, `getFormModelClass()`, `getEntityName()`, `getViewPrefix()`, `getListRoute()`… (`src/Handlers/Contents/Edit.php:29-39`), plus `beforeValidate()` / `afterValidate()` / `beforeSave(bool $inTransaction)` / `afterSave(bool $inTransaction)` hooks, each save hook called twice, outside then inside the transaction (`AbstractCreate.php:291-307`).
- Output is one of four `Blackcube\Handler\OutputType` values: `render` (layout), `partial`, `json`, `redirect` (`AbstractModelHandler.php:202-210`); `download` is not handled by this pipeline. AJAX requests are detected by `X-Requested-With: XMLHttpRequest`, Ajaxify refreshes by `X-Requested-For: Ajaxify` (`isAjax()`, `isAjaxify()` of `Blackcube\Handler\AbstractHandler`).
- Dialogs never render a page: the GET of a `Delete`, `Toggle`, `Elastic`, `Xeo`… route answers JSON with `AureliaCommunication::dialog(DialogAction::Keep)` + `dialogContent(Bleet::dialogFrame(...)->render())`; the POST / DELETE answers `dialog(Close)` + `ajaxify(listId, listUrl)` + `toast(...)` (`AbstractDelete.php:190-233`, `AbstractPanel.php:104-122`).
- Every route is guarded in `config/routes.php` with `JwtAuthMiddleware::withRbac([Rbac::PERMISSION_*])`; the login, logout, onboarding, token and OAuth population routes are the exceptions (`config/routes.php:197-270`). Permissions are `PREFIX:ACTION` constants of `Components\Rbac`, roles `PREFIX:MANAGER` plus `ADMIN` (`src/Components/Rbac.php:29-127`).
- Views live under `@dboard/Views` (`DboardConfig::$viewsAlias`); a handler may render from another root through `ViewsAliasInterface::getViewsAlias()` while keeping the dboard layout (`src/Handlers/Commons/ViewsAliasInterface.php:22-25`, `AbstractBaseHandler.php:54-62`). Bloc admin templates come from `adminTemplatesAlias`.
- Lists are `ActiveQueryPaginator` on `Model::query()` with the `SearchForm` (`like` on `getSearchColumn()`) and `PageForm`; a list handler preloads its relations in `eagerLoad()` with `getIndexedRecords()` (one `IN` query per relation), never `with()` (`AbstractIndex.php:176-231`, `AbstractNodeIndex.php:152-187`).

## Parts

| File | Content | Read when |
|---|---|---|
| `handlers.md` | The handler hierarchy, the pipeline, `ActionModel`, the specialized abstracts and their getters, lists, trees, blocs, global Xeo, the form models, widgets and helpers. | before adding or changing a screen |
| `auth.md` | Login and token issuance, `JwtAuthMiddleware` (cookies, refresh, RBAC), passkeys (WebAuthn), locale, `ViewInjection`. | before touching authentication or a guarded route |
| `rbac.md` | Permissions and roles, `RbacInitializer`, `CachedRbacManager`, assignment screens. | before adding a permission |
| `addons.md` | The addon contract (routes, sidebar, RBAC, OAuth2 population), the population authorization server, `AccessTokenValidator`. | before plugging a module into the panel |
| `integration.md` | config-plugin, parameters, DI wiring left to the application, migrations, assets, i18n, console, tests. | before plugging the package in |
| `extension.md` | What extends and what does not. | before extending |
