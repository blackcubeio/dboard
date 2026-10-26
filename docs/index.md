# dboard — Administration Panel

Back-office for the Blackcube CMS. Provides CRUD for every dcore entity, tree management, RBAC, JWT authentication with passkey support.

- [Installation](installation.md)

## Managed entities

| Module | Capabilities |
|---|---|
| Contents | CRUD, tree (move, reorder), blocs, tags, translations, slugs, SEO (xeo), authors, markdown export/import |
| Tags | CRUD, tree, blocs, slugs, SEO, authors, markdown export/import |
| Menus | CRUD, tree |
| LlmMenus | CRUD, tree (llms.txt navigation) |
| Types | CRUD, associate elastic schemas |
| Elastic Schemas | CRUD, JSON Schema editor |
| Languages | CRUD |
| Hosts | CRUD |
| Slugs | CRUD, sitemap configuration |
| SEO (Xeo) | Per-slug SEO metadata, authors, blocs |
| Global Xeo | Organization, WebSite, Robots, Sitemap per host |
| Schema Mappings | Regular-to-xeo schema associations |
| Authors | CRUD |
| Parameters | CRUD (domain/name key-value) |
| Administrators | CRUD, activation, role/permission assignment |
| Passkeys | WebAuthn registration and management |
| RBAC | Roles and permissions management |
| Dashboard | Overview |
| Search | Global search across entities |
| Preview | Content preview with simulated dates |
| Import | Multi-step JSON import wizard |

## Architecture

### Handler hierarchy

All request handlers extend a base hierarchy:

| Base class | Purpose |
|---|---|
| `AbstractBaseHandler` | Foundation on `Blackcube\Handler\AbstractHandler` (`blackcube/handler`): render, redirect, JSON, download, AJAX detection; adds `DboardConfig` and the translator |
| `AbstractModelHandler` | Model loading via `ActionModel`, form data binding |
| `AbstractPageHandler` | Full-page pipeline: `setupAction → setupMethod → handleMethod → prepareOutputData → output` |
| `AbstractAjaxHandler` | AJAX pipeline with exception catching (error → toast) |
| `AbstractSessionHandler` | Session support mixin |
| `AbstractSessionAjaxHandler` | AJAX with session, no model pipeline |
| `AbstractIndex` | List pages with search + pagination |

Specialized abstract handlers:

| Base class | Purpose |
|---|---|
| `AbstractCreate` | Entity creation with Hazeltree/Elastic awareness |
| `AbstractEdit` | Entity editing with blocs, files, Hazeltree move |
| `AbstractDelete` | Modal confirmation + cascade deletion |
| `AbstractToggle` | Active/inactive toggle |
| `AbstractElastic` | Drawer for elastic properties |
| `AbstractXeo` | Drawer for SEO metadata, xeo blocs, authors |
| `AbstractBlocAdd` / `AbstractBlocDelete` / `AbstractBlocReorder` | Bloc management |
| `AbstractSlugGenerator` / `AbstractSlugSitemap` | Slug and sitemap management |
| `AbstractTags` | Tag association |
| `AbstractTranslations` | Translation group management |
| `AbstractExport` / `AbstractMdExport` / `AbstractMdImport` | Export/import |
| `AbstractPanel` | Generic panel (modal/drawer) rendering |
| `AbstractHazeltreeReorder` | Tree node reordering |
| `AbstractXeoAuthors` / `AbstractXeoRefresh` | Xeo author and bloc refresh |

### Middleware stack

| Middleware | Purpose |
|---|---|
| `JwtAuthMiddleware` | JWT validation from cookies, refresh token flow, RBAC check |
| `JsonBodyParserMiddleware` | Parse `application/json` request bodies |
| `MultiVerbParserMiddleware` | Parse PUT/PATCH/DELETE multipart bodies |

### Services

| Service | Purpose |
|---|---|
| `CypherKey` (`oauth2`) | JWT signing keys: `RS*` read from files and generated when missing, `HS*` taken from the configured secret |
| `ScopeProvider` | OAuth2 scope provider |
| `ImportService` (dcore) | Multi-step JSON import with file processing — provided by `Blackcube\Dcore\Services` |
| `HazeltreeElasticService` | Bloc loading, validation, file tracking |
| `RbacInitializer` | RBAC items sync from code to database |
| `PasskeyService` | AAGUID device registry import |
| `PasskeyGrant` | WebAuthn OAuth2 grant type |
| `ViewInjection` | Inject RBAC, SSR routes and the current administrator into views |
| `CachedRbacManager` | Per-request RBAC permission cache (decorates `ManagerInterface`) |
| `WebauthnConfig` | WebAuthn relying party configuration |

### Facades

| Class | Purpose |
|---|---|
| `Widgets` | Static factory — `Sidebar` and `Preview` via `Injector::get()` with lazy loading, `Elastic` instantiated directly, `pagination()` the Bleet pager with the dboard texts |
| `Dboard` | Package-level facade — `getI18nSources()` returns DI definitions for all dboard i18n categories (`dboard-common`, `dboard-content`, `dboard-modules`, `dboard-onboarding`, `dboard-builtin`) |

Usage in consumer app DI config:

```php
use Blackcube\Dboard\Dboard;

return array_merge([
    MessageFormatterInterface::class => IntlMessageFormatter::class,
], Dboard::getI18nSources());
```

### Widgets

| Widget | Purpose |
|---|---|
| `Sidebar` | RBAC-aware navigation sidebar |
| `Preview` | Preview toggle button with date simulation |
| `Elastic` | Bleet elastic field with the dboard texts (upload dropzone, browse and error texts) |

The popovers, the dialog frames and the JSON Schema editor come from `blackcube/bleet` (`Bleet::popover()`,
`Bleet::dialogFrame()`, `Bleet::jsonEditor()`).

### Enums

| Enum | Values |
|---|---|
| `ReorderMode` | `MoveUp`, `MoveDown`, `Dnd` |
| `TreePosition` | `Before`, `Into`, `After` |

The output types come from `blackcube/handler` (`Blackcube\Handler\OutputType`); the dboard pipeline handles `Render`, `Partial`, `Json` and `Redirect`.

### Helpers

| Helper | Purpose |
|---|---|
| `ElasticFieldRenderer` | Render elastic schema fields with admin view resolution |
| `RouteHelper` | Grouped route options (contents, tags, menus, llmMenus, regular routes) for form dropdowns |

## Cross-cutting concepts

- [RBAC](rbac.md) — Roles, permissions, hierarchy, assignment
- [WebAuthn](webauthn.md) — Passkey registration and authentication
- [Performance](performance.md) — N+1 avoidance, RBAC and route caching
