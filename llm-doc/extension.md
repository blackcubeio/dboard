# dboard — Extension

## Extension points

| Point | Where | Use |
|---|---|---|
| A new screen for an entity | extend the matching `Handlers\Commons\Abstract*`, implement its getters, override the hooks (`beforeSave(bool $inTransaction)`, …) | the concrete handlers are 5 to 40 lines (`src/Handlers/Contents/Edit.php`) |
| A whole module | an addon: `routes`, `sidebar`, `rbac`, `population` + `scopes`, `configRoute` (`addons.md`) | a package plugged into the panel without touching dboard |
| Own views in the dboard layout | `ViewsAliasInterface::getViewsAlias()` on the handler (`src/Handlers/Commons/ViewsAliasInterface.php`) | addon views |
| Bloc admin templates | `adminTemplatesAlias` param, read by `ElasticFieldRenderer::getAdminView()` (`src/Helpers/ElasticFieldRenderer.php:136-165`) | a custom form per ElasticSchema |
| Permissions | a class with `PERMISSION_*` / `ROLE_*` constants in `rbacClasses` (or through an addon) | `RbacInitializer` writes them, `Rbac::getAllRoles()` lists them |
| Route guard | `JwtAuthMiddleware::withRbac()`, `::optional()` (`src/Middlewares/JwtAuthMiddleware.php:82-109`) | addon routes |
| Lists | `getOrderBy()`, `getSearchColumn()`, `getPageSize()`, `getView()` / `getListView()`, `eagerLoad()` with `getIndexedRecords()` (`src/Handlers/Commons/AbstractIndex.php:58-231`) | other sort, other relations preloaded |
| Form labels | `Models\Forms\BridgeFormModel::getRawLabels()` / `Hints` / `Placeholders` + `translateCategory` (`src/Models/Forms/BridgeFormModel.php:43-56`) | translated forms |
| Error output of an AJAX handler | `prepareErrorOutput(Throwable)` (`src/Handlers/Commons/AbstractAjaxHandler.php:75`) | close the dialog on error, other toast |
| Dialog colour / title / content | `AbstractPanel::getColor()`, `getTitle()`, `getContentView()`, `getViewData()` | custom confirmation panels |
| Token transport | `Authorization: Bearer` header accepted next to the cookies (`JwtAuthMiddleware.php:231-234`) | API calls from the Aurelia side or scripts |
| Resource of a population | `AccessTokenValidator::validate()` in the resource application | addon API guarded by dboard tokens |
| Widgets | `Widgets::sidebar()->color(...)`, `Sidebar` colour shortcuts; `Widgets::elastic()` options | theme |
| RBAC manager | any `ManagerInterface`; `CachedRbacManager` wraps it | other storage |

## Not extensible

| Element | Reason |
|---|---|
| The pipeline order | `handle()` of `AbstractPageHandler` / `AbstractAjaxHandler` is fixed; only the steps are overridable |
| Output types | `match` on the four `OutputType` values in `output()` (`src/Handlers/Commons/AbstractModelHandler.php:202-210`); an unknown type throws |
| Dialog protocol | `AureliaCommunication` arrays of `blackcube/bleet` (`dialog`, `dialogContent`, `ajaxify`, `toast`); the Aurelia side expects those keys |
| Cookie names and flags | `access_token` / `refresh_token`, path = route prefix, `Lax`, `Secure`, access cookie not `HttpOnly` (`JwtAuthMiddleware.php:52-53`, `:197-215`) |
| Token TTLs of the `dboard` population | 1 h access, 15 days refresh, in `refreshAccessToken()` (`:279-280`) and the `Authentication\Token` handler |
| Population grants | `authorization_code` + `refresh_token`, public clients, PKCE (`src/Services/AddonPopulation.php:70-73`, `src/Handlers/Oauth/Register.php:32-33`) |
| Permission naming | `PREFIX:ACTION` / `PREFIX:MANAGER` / `ADMIN`: `RbacInitializer` and `Rbac::getRolePermissions()` build the hierarchy by prefix |
| Primary keys of the route | `['id']` unless `primaryKeys()` is overridden (`Parameters` use `domain` + `name`, global Xeo `hostId` + `kind`) |
| Static view state | `ViewInjection` and `Widgets` keep static state for the request: one administrator, one sidebar per process |
| Locale list | `LocaleHelper::LOCALES` (`src/Helpers/LocaleHelper.php:24`); the seed files exist for de, en, es, fr, it, pt only |
