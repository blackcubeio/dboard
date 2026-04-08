# dboard — Administration Panel

Back-office for the Blackcube CMS. Provides CRUD for every dcore entity, tree management, RBAC, JWT authentication with passkey support.

- [Installation](installation.md)

## Managed entities

| Module | Capabilities |
|---|---|
| Contents | CRUD, tree (move, reorder), blocs, tags, translations, slugs, SEO (xeo), authors, markdown export/import |
| Tags | CRUD, tree, blocs, slugs, SEO, authors, markdown export/import |
| Menus | CRUD, tree |
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
| `AbstractBaseHandler` | Foundation: render, redirect, JSON, AJAX detection |
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
| `CypherKey` | JWT signing keys (RS256/HS256) |
| `ScopeProvider` | OAuth2 scope provider |
| `ExportService` | Content/Tag export to array via `#[Exportable]` |
| `ImportService` | Multi-step JSON import with file processing |
| `HazeltreeElasticService` | Bloc loading, validation, file tracking |
| `RbacInitializer` | RBAC items sync from code to database |
| `PasskeyService` | AAGUID device registry import |
| `PasskeyGrant` | WebAuthn OAuth2 grant type |
| `ViewInjection` | Inject RBAC and SSR routes into views |
| `WebauthnConfig` | WebAuthn relying party configuration |

### Facades

| Class | Purpose |
|---|---|
| `Widgets` | Static factory for `Sidebar`, `Preview`, `Popover` — uses `Injector::get()` with lazy loading |
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
| `Popover` | Hover tooltip for button bars |
| `SchemaEditor` | JSON Schema editor (Aurelia custom element) |

### Enums

| Enum | Values |
|---|---|
| `OutputType` | `Render`, `Partial`, `Json`, `Redirect` |
| `PanelType` | `Modal`, `Drawer` |
| `ReorderMode` | `MoveUp`, `MoveDown`, `Dnd` |
| `TreePosition` | `Before`, `Into`, `After` |

### Helpers

| Helper | Purpose |
|---|---|
| `ElasticFieldRenderer` | Render elastic schema fields with admin view resolution |
| `MenuRouteHelper` | Grouped route options for menu forms |

## Cross-cutting concepts

- [RBAC](rbac.md) — Roles, permissions, hierarchy, assignment
- [WebAuthn](webauthn.md) — Passkey registration and authentication
