# Performance

Patterns that keep dboard list and page rendering at a constant, low number of
SQL queries regardless of data volume.

## List eager-loading

List rows render related data (slug, host, type, ...). Fetching those relations
lazily inside the row loop is an N+1: one query per row per relation. List
handlers preload them in a constant number of queries instead.

`AbstractIndex::eagerLoad()` is a hook that returns the current page items with
their relations populated, or `null` to let the view read the paginator itself.
`AbstractIndex::loadByIds()` loads a set of records by id in a single `IN` query.

- `AbstractNodeIndex` (Contents, Tags) preloads `slug` (with its `host`) and
  `type` for every routable node — nothing to add per handler.
- Flat listings (Menus, Slugs, LlmMenus) override `eagerLoad()` for their own
  relations.

### Adding eager-loading to a new list

1. Override `eagerLoad()`: iterate `$this->paginator->read()`, collect the
   foreign-key ids, call `loadByIds()` per relation, then `populateRelation()`
   on each item. Return the populated items.
2. In the full-page `index.php`, forward the items to the `_list` partial:
   `'items' => $items ?? null`.
3. In the partial, read the populated relation instead of re-querying, and keep
   a paginator fallback so the view still works standalone:

```php
$items = $items ?? iterator_to_array($paginator->read());
// ...
$slug = $content->getSlugId() !== null ? $content->relation('slug') : null;
```

```php
protected function eagerLoad(): array
{
    $items = [];
    $hostIds = [];
    foreach ($this->paginator->read() as $menu) {
        $items[] = $menu;
        if ($menu->getHostId() !== null) {
            $hostIds[$menu->getHostId()] = $menu->getHostId();
        }
    }
    $hosts = $this->loadByIds(Host::class, $hostIds);
    foreach ($items as $menu) {
        $hostId = $menu->getHostId();
        $menu->populateRelation('host', $hostId !== null ? ($hosts[$hostId] ?? null) : null);
    }
    return $items;
}
```

### Caveat

`ActiveQueryPaginator::read()` streams via `each()`, which does **not** honour
`with()` eager-loading. Preload through `eagerLoad()` / `loadByIds()`, not
`->with()`.

## Per-request RBAC cache

`ManagerInterface::userHasPermission()` hits the database (~3 queries) on every
call with no memoization; the sidebar alone checks ~20 permissions per page.
`CachedRbacManager` decorates the RBAC manager and loads the user permission set
once per request via `getPermissionsByUserId()`, answering further checks from
memory.

### Prerequisite

Wire the decorator in the consumer app DI, wrapping the configured manager:

```php
use Blackcube\Dboard\Services\CachedRbacManager;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\ManagerInterface;

ManagerInterface::class => static fn (Manager $manager): ManagerInterface
    => new CachedRbacManager($manager),
```

It is transparent: `Sidebar`, `ViewInjection` and `JwtAuthMiddleware` benefit
without code changes.

### Caveats

- Parameterised checks (`userHasPermission($id, $perm, $params)`) and guest
  checks (`$userId === null`) bypass the cache and are delegated as-is, so RBAC
  rules keep working.
- Mutating calls (`assign`, `revoke`, `addChild`, ...) invalidate the cache.

## Administrator loaded once

`JwtAuthMiddleware` already loads the authenticated `Administrator`. It pushes it
into `ViewInjection::setAdministrator()`, so views read it without a second
query.

## Route option caching

`RouteHelper` builds grouped route options for select dropdowns. Its `getRoutes`,
`getCmsRoutes` and `get{Content,Tag,Menu,LlmMenu}Routes` results are cached on the
(singleton) instance, so repeated calls within a request (e.g. from
`ElasticFieldRenderer`) hit the cache. Calls passing a custom `$query` bypass the
cache — they are request-specific.

## Cache and the database

`SchemaCache` (schema introspection) and the i18n message source are cached
through the app `CacheInterface`. When caching is disabled (`CACHE_ENABLED=false`,
the dev default), the schema is re-introspected and translations re-queried on
every request (~25 extra queries per page). Enable caching in production; the
per-page query count is then the genuine work only.
