# dboard — Handlers

## Hierarchy (`src/Handlers/Commons/`)

| Class | Extends | Adds |
|---|---|---|
| `AbstractBaseHandler` (`AbstractBaseHandler.php:31`) | `Blackcube\Handler\AbstractHandler`, `ViewsAliasInterface` | `DboardConfig` (a `HandlerConfig`) and `TranslatorInterface` on top of the base services (`:33-52`); the base gives `render()` with the layout `@dboard/Views/Layouts/main.php`, `renderPartial()`, `renderJson()`, `redirect(route, params, status)`, `isAjax()`, `isAjaxify()`, `downloadContent()`, `downloadFile()`, `getBodyParams()` (`null` on GET), `$debug`; views are read under `getViewsAlias()` and named from its root, `extractViewPath()` returns the name as given (`:54-67`); `getPageSize()` 20, `getListSize()` 10 (`:69-77`) |
| `AbstractModelHandler` (`AbstractModelHandler.php:39`) | base + `CurrentRoute` | `getActionModels()` and `prepareOutputData()` abstract (`:80-88`); `primaryKeys()` (`['id']`), `extractPrimaryKeysFromRoute()` (`['main' => [...]]`), `extractPrimaryKeysFromModel()`; `setupAction()` loads models and form models, `404` when the main model is new although a key was given (`:136-168`); `setupMethod()` loads the body into every form model (`:176-184`); `handleMethod()` empty; `output()` by `OutputType` (`:202-210`) |
| `AbstractPageHandler` (`AbstractPageHandler.php:28`) | model handler | `handle()`: the pipeline, exceptions propagate (`:38-51`) |
| `AbstractAjaxHandler` (`AbstractAjaxHandler.php:30`) | model handler | `handle()` with `try/catch` around `handleMethod()`; an exception becomes `renderJson(prepareErrorOutput($e))`: logged, danger toast, message only when `debug` (`:40-89`) |
| `AbstractSessionHandler` (`AbstractSessionHandler.php:32`) | base + `SessionInterface`, `CurrentRoute` | nothing else: the base of the lists, onboarding and import |
| `AbstractSessionAjaxHandler` (`AbstractSessionAjaxHandler.php:28`) | session handler | AJAX pipeline without models: `handleMethod()` and `prepareOutputData()` abstract (`:30-56`) |
| `AbstractIndex` (`AbstractIndex.php:31`) | session handler | see Lists |
| `AbstractNodeIndex` (`AbstractNodeIndex.php:31`) | `AbstractIndex` | tree / flat mode and node filter kept in session (`SESSION_LIST_MODE`, `SESSION_NODE_FILTER`), `eagerLoad()` of slug + host + type (`:152-187`) |

`ActionModel` (`src/Components/ActionModel.php`): `getModel(?array $pkey)` queries `Model::query()->andWhere($pkey)->one()` or returns a new instance (`:82-100`); `getFormModel(?ActiveRecord)` builds the form with `createFromModel($model, $translator)` when the form extends `Models\Forms\BridgeFormModel`, then applies the scenario (`:111-133`).

## Specialized abstracts

All on `AbstractPageHandler` or `AbstractAjaxHandler`; every one asks `getModelClass()` and `getEntityName()`, then its own getters. Hooks come in pairs with the `bool $inTransaction` flag.

| Abstract | Base | Own getters | Hooks | Behaviour |
|---|---|---|---|---|
| `AbstractCreate` (`AbstractCreate.php:36`) | page | `getFormModelClass()`, `getViewPrefix()`, `getListRoute()`, `getSuccessRoute()`; `getView()` `create`, `getFormScenario()` `create`, `getMaxLevel()` (throws unless overridden on a Hazeltree model), `getDefaultMoveTarget()` | `beforeValidate`, `afterValidate`, `beforeSave`, `afterSave` | POST: validate, `populateModel()`, then `saveBefore` / `saveInto` / `saveAfter` on the target by `TreePosition` with the level check, or `save()`, in a transaction (`:257-326`); success → redirect to `getSuccessRoute()` with the model keys (`:331-340`). Elastic model: `elasticSchemaOptions` from the `Common` and `Page` schemas not hidden (`:189-205`) |
| `AbstractEdit` (`AbstractEdit.php:49`) | page | the create getters minus success route, plus `getBlocsView()`, `stayOnPageAfterSave()`, `getBlocsListId()`, `getBlocReorderRoute()`, `getBlocAddRoute()`, `getBlocDeleteRoute()`, `getRouteIdParam()`, `getFileStoragePrefix()`, `getFileRoutePrefix()` (`:97-223`) | same four | POST from Ajaxify with blocs → `handleBlocAction()` (`:472`); otherwise validate the form and every `BlocForm`, move with the subtree depth check, save, `HazeltreeElasticService::saveBlocs()` in the same transaction, `reloadBlocForms()` (`:551-677`); AJAX GET with blocs renders the blocs partial (`:682-`) |
| `AbstractDelete` (`AbstractDelete.php:41`) | ajax + `FileService` | `getListId()`, `getListRoute()`; `getModelName()`, `getEntityLabel()`, `getSuccessMessage()` | `beforeDelete`, `afterDelete` | GET → confirmation modal `Commons/_delete-content`; DELETE → `delete()` in a transaction, then the entity file directory removed (`:153-172`), answer close + ajaxify + success toast |
| `AbstractToggle` (`AbstractToggle.php:34`) | ajax | `getListId()`, `getListRoute()`; `getModelName()`, `getSuccessMessage()` | `beforeToggle`, `afterToggle` | GET → confirmation; POST → flips `active` |
| `AbstractPanel` (`AbstractPanel.php:33`) | ajax | `getType(): DialogType`, `getTitle()`, `getContentView()`, `getMessage()`; `getColor()` | — | GET only: a modal or drawer whose form posts to the same route name with the model keys (`:86-122`) |
| `AbstractElastic` (`AbstractElastic.php:46`) | ajax | `getFormModelClass()`, `getFileRoutePrefix()` | validate / save pairs | drawer editing the elastic properties of a Content / Tag |
| `AbstractXeo` (`AbstractXeo.php:57`) | ajax | `getFileRoutePrefix()`, `getArticleBlocPivotClass()`, `getArticleBlocFkColumn()`, `getAuthorPivotClass()`, `getAuthorPivotFkColumn()`, `getLlmMenuFkColumn()` | `beforeSave`, `afterSave` | drawer of the slug SEO: `XeoForm`, xeo blocs (`loadXeoBlocs()`), authors (`loadXeoAuthors()`), llm menu entry |
| `AbstractXeoAuthors`, `AbstractXeoRefresh`, `AbstractXeoSuggest`, `AbstractXeoLlm` | ajax | pivot / fk column getters | — | author list, bloc refresh, suggestions, llm-menu link of a slug |
| `AbstractBlocAdd`, `AbstractBlocDelete`, `AbstractBlocReorder` | ajax | `getEditRoute()`, `getBlocsListId()`, `getPivotClass()`, `getPivotFkColumn()` | save / delete / reorder pairs | the bloc pivot rows of a node; reorder in `ReorderMode` `moveUp` / `moveDown` / `dnd` (`AbstractBlocReorder.php:158-197`) |
| `AbstractHazeltreeReorder` (`AbstractHazeltreeReorder.php:34`) | ajax | `getListId()`, `getListRoute()`, `getMaxLevel()` | `beforeReorder`, `afterReorder` | `Move` routes: `HazeltreeReorderForm`, `validateMove()` (`:190`), then the tree move |
| `AbstractSlugGenerator`, `AbstractSlugSitemap` | ajax | `getSlugGeneratorRoute()` | save pair | slug path proposal; slug + `Sitemap` row of a node |
| `AbstractTags`, `AbstractTranslations` | ajax | `getPivotClass()`, `getPivotFormClass()`, `getPivotFkColumn()`; `getTranslationsListId()` | save pair | tag pivots of a content; translation group (GET list, POST link, DELETE unlink) |
| `AbstractExport`, `AbstractMdExport`, `AbstractMdImport` | ajax | `getName()`, `isCompressed()`; `getEntityLabel()`, `getImportRouteName()` | — | JSON export download (dcore `ExportService`), markdown export dialog and download, markdown import |

Global Xeo kinds (`src/Handlers/Xeo/`): `AbstractGlobalXeoIndex` / `Edit` on the base handler with `getKind()`, `getKindLabel()`, `getViewName()`, `getIndexRoute()`, `getSchemaName()` (`AbstractGlobalXeoEdit.php:45-66`); `AbstractGlobalXeoDelete` / `Toggle` on the delete and toggle abstracts with `GlobalXeo` as model and `['hostId', 'kind']` keys (`AbstractGlobalXeoDelete.php:37-69`). Concrete kinds: `Organization`, `WebSite`, `Robots` (schema `RawData`), `Sitemap` (`RobotsEdit.php:22-26`).

Other bases: `Onboarding\Onboarding` and `Import\Import` on the session handler (own layout, multi-step wizards), `Oauth\*` and `Authentication\*` on the base handler (see `auth.md`, `addons.md`), `Search\Index` (contents, tags, menus, slugs), `Preview\Toggle` (session preview state for dcore `PreviewManager`).

## Lists (`AbstractIndex`)

`getModelClass()`, `getViewPrefix()` abstract; `getOrderBy()` (`['left' => SORT_ASC]`), `getView()` `index`, `getListView()` `_list`, `getSearchColumn()` `name`, `getLevelOffset()` (`AbstractIndex.php:58-119`). `setupAction()` builds `Model::query()->orderBy(...)`, adds `['like', column, search]` when the `SearchForm` has a value, wraps it in `ActiveQueryPaginator` with `getPageSize()` and the `PageForm` page (`:176-193`). `prepareOutputData()` renders `index` (full page) or `_list` (AJAX), passing `paginator`, `searchForm`, `pageForm`, `urlGenerator`, `currentRoute`, `levelOffset` and `items` when `eagerLoad()` returned them (`:238-262`). `getIndexedRecords($class, $ids)` is the `IN` loader for `eagerLoad()` overrides (`:215-231`).

`AbstractNodeIndex`: `FilterNodeForm` (flat / tree, node id) persisted in session per view prefix (`AbstractNodeIndex.php:40-63`, `:84-101`); flat mode orders by `dateUpdate DESC` and renders `_list_flat` (`:70-77`, `:131-134`); a node filter restricts to `forNode($node)->children()->includeDescendants()` and sets the level offset (`:106-116`).

## Form models

`Models\Forms\BridgeFormModel` extends `blackcube/bridge-form-model`'s with a translator: `createFromModel($model, $translator)`, `getRawLabels()` / `getRawHints()` / `getRawPlaceholders()` translated once through `translateCategory` and `translateElasticCategory` (`src/Models/Forms/BridgeFormModel.php:22-113`). One form per screen in `Models\Forms\*` (`ContentForm`, `TagForm`, `BlocForm`, `XeoForm`, `SlugForm`, `HazeltreeReorderForm`, `FilterNodeForm`, `SearchForm`, `PageForm`, `LoginForm`, `OnboardingForm`, `ImportExistenceForm`, …).

## Widgets and helpers

- `Widgets::sidebar()` and `preview()` are singletons from `Blackcube\Injector\Injector::get()`; `elastic()` builds the dboard `Elastic` field (Bleet elastic with translated upload texts); `pagination()` is `Bleet::pagination()` with the `dboard-common` texts (`src/Widgets/Widgets.php:36-74`).
- `Sidebar` (`src/Widgets/Sidebar.php:37`): colour shortcuts, `user(Administrator)`, `currentRoute()`, `render()`; entries filtered by RBAC, addon entries appended from `$addon['sidebar']` (`:309-319`), an `Addons` entry when addons exist (`:326`).
- `Preview` (`src/Widgets/Preview.php:35`): the preview toggle with simulated date, bound to dcore `PreviewManager` and `PERMISSION_SITE_PREVIEW`.
- `ElasticFieldRenderer::renderAll($form, $prefix, $elasticOptions)` renders the elastic properties of a form, with the admin view of the schema when one exists (`getAdminView()`, `getAdminViewAlias()`, `src/Helpers/ElasticFieldRenderer.php:76-165`); `RouteHelper` gives the grouped route options (`getRoutes()`, `getCmsRoutes()`, `getContentOptions()`, `getTagOptions()`, `getMenuOptions()`, `getLlmMenuOptions()`, `getRegularRoutes()`, cached on the instance, `src/Helpers/RouteHelper.php:66-275`); `LocaleHelper::getLocales()` / `getOptions()` list the panel locales; `RelativeTimeHelper::format()` the "x minutes ago" strings.
- `HazeltreeElasticService`: `captureInitialFileValues($blocs)` before the POST, `saveBlocs($blocForms, $blocs, $parent, $initialFileValues)` populates, saves, processes the bloc files through dcore `FileService` and deletes the replaced files (`src/Services/HazeltreeElasticService.php:39-77`).
