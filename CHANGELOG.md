# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added

- The Addons screen activates, updates, deactivates and forgets the add-ons declared to dcore (`'blackcube/dcore' => 'addons'`), each action behind a confirmation modal and the new `ADDON:UPDATE` permission; a row shows the name, the id, the installed version and a badge (active, inactive, update available, package missing), the Configure button when the add-on carries `Interfaces\AddonConfigureInterface`. `Services\AddonActivation` plays the whole of an action: the migrations and the registry through dcore, the roles and permissions through `RbacInitializer`, the OAuth2 clients and codes of the population at deactivation.
- The console commands `dboard:addonList`, `dboard:addonActivate <id>`, `dboard:addonUpdate <id>` and `dboard:addonDeactivate <id>` (the last one after a confirmation), through the same service; the `params-console` group now declares the commands of the package, `dboard:refreshAaguid` included.
- The modules: a package that brings RBAC items, sidebar entries, routes or an OAuth2 population to the panel without being an add-on declares its class in `'blackcube/dboard' => 'modules'`, in its own params, and is always on. `Services\Modules` reads on each call what the modules bring, the declared ones plus the active add-ons of the dcore registry: the RBAC classes, the permissions each OAuth2 population publishes, the sidebar items. The routes of a module are mounted as they come; `Middlewares\AddonRoutingMiddleware::of($id)` gates the routes of an add-on: 404 while it is inactive.
- `Upgrades\RenameMigrations`, declared in `'blackcube/dcore' => 'upgrades'`: the `upgrade` command of dcore rewrites the migration history of a base migrated under the former names.

### Changed

- The migrations of the package are named `M1` + 11 digits (`M100000000000CreateAdministrators` … `M100000000009AddAddonTranslations`): the type digit 1 of dboard, after the 0 of dcore. An existing base takes `upgrade` before `migrate:up`.
- The interfaces a package implements to extend the panel sit in `Interfaces\` and lose their `Addon` prefix, a module carrying them without being an add-on: `RbacInterface`, `SidebarInterface`, `RoutesInterface`, `Oauth2Interface`. `Services\AddonPopulations` and `AddonPopulation` are `Populations` and `Population`, a population being published by a module.
- `RbacInitializer`, `Rbac::getAllRoles()` and `Rbac::getAllPermissions()` read the RBAC classes from `Modules`: the panel's plus the modules'. `ScopeProvider` and `Populations` read the published scopes from it too, so an inactive add-on has no population and no grantable scope. `Sidebar` takes `Modules` as fourth argument.
- The sign-in panel carries again the mountain at dusk of `login.jpg`, in grey, screened on the ink at a third, under the aurora and a veil of ink that keeps the brand and the tagline readable (`.bg-veil`); the grid is gone from it. A phone, where the panel is hidden, does not fetch the picture.

### Removed

- `DboardConfig::$addons` and the `'blackcube/dboard' => 'addons'` block of callables of the host: an add-on declares itself to dcore with its class, a module to the panel, dboard reads what they bring through the interfaces of `Interfaces\`.

## [1.0.3] - 2026-10-28

### Changed

- The Organization and WebSite screens of the *EO edit the label of the entry, the name the JSON-LD nodes carry; it was set once at creation and could not be changed.

## [1.0.2] - 2026-10-28

### Changed

- The sign-in screen is split in two: the ink panel of the brand on the left, the form alone on the right, the passkey offered before the password. The theme takes the Geist faces, bundled, and the ink, zinc and iris palette.
- The `dboard-login-device` attribute removes its whole `[data-login-device="block"]` block, separator included, when the browser cannot do WebAuthn.
- The connected frame is a bar of ink, dark in both modes, rendered by the package's own `Widgets/Views/sidebar.php`: the brand, the navigation, and at its foot the preview toggle, the light / dark switch (`dboard-dark-toggle`), the account menu and the installed version. The header and the footer of the layout are gone; on a phone a thin bar carries the burger and the brand. The workbench is bounded to `max-w-7xl`.
- The root route of the panel is named `dboard.index`, so the dashboard entry is marked on it too.
- The dashboard opens on a sober heading (`Widgets::pageHeading()`, the heading of every page from now on), three counters in a description list, and two cards listing the latest contents and tags as rows.
- The language screens take the page pattern of the panel: the heading carries the create action, the search stands alone under it, the list and the form sit on a `dboard-surface`, the way back is a link above the title, and the main action is in the accent colour.
- The night mode reaches the Bleet components: `main.css` dresses again, by the classes Bleet emits, the fields, the dropdowns, the checkboxes and toggles, the description lists, the badges, the pager, the button groups, the dialogs, the popovers, the alerts, the schema editor and the rich text editor. Bleet itself is untouched. The body of both layouts carries `dboard-canvas`.
- Every screen of the panel takes the page pattern: hosts, administrators, authors, parameters, types, elastic schemas, mappings, redirections, menus, LLM menus, contents, tags, RBAC, search, account, addons, import and the *EO screens. The edit screens are titled by the record, with its kind and number as subtitle; the drawer actions of a record (permissions, passkeys, slug, *EO, properties, tags, translations, AI export, public URL) sit in the heading; the blocks of a content or a tag stand under their own section title.
- The onboarding sits on the ink with the aurora, its steps on a `dboard-surface`; the forbidden screen and the OAuth consent screen follow the same palette. The static background pictures are no longer used by these screens.
- The dashboard tells more, every number a `WHERE` and a `COUNT` in `Services\DashboardService`: the contents to come and the expired ones apart, the updates of the week, the drafts in progress beside the latest contents and tags, the health of the site (unreachable contents, pages without *EO or out of the sitemap, redirections, inactive URLs, missing translations when several languages are active) and its shape, the active contents by language and by type. A health counter unfolds, on a click, the records behind it with the link that puts them right, read once from `GET /dashboard/health/{kind}` by the `dboard-unfold` element.
- The actions of a form (`dboard-actions`) stick to the foot of the screen while the page scrolls, so that Save is always at hand. The band that heads a block is ink with the accent stroke on its left (`dboard-band`), its controls dressed for the night whatever the hour; the move and delete controls of a block are a button group, the add control another, each trigger a `Widgets::ajaxifyButton()` that carries the hidden inputs of its own payload; on the band the buttons and the block type dropdown are inverse, white on the ink, a button turning to its shade on hover; the block type dropdown is wider.

### Added

- `yiisoft/rbac-db` as a development dependency: the test suite drives the sidebar against the RBAC tables the migrations create.
- `AddMissingTranslations`, a migration that adds to the translation tables the keys of the i18n files a base installed earlier does not hold yet, with their translations; a base installed from now on gets them from `CreateTranslations` and the migration adds nothing.

### Fixed

- The passkeys drawer of the account answered a 500: the device of a passkey was read as a magic property the model does not expose, it is read by its relation query.

## [1.0.1] - 2026-10-27

### Changed

- The handlers are built on `blackcube/handler`: `Handlers\Commons\AbstractBaseHandler` extends `Blackcube\Handler\AbstractHandler`, `DboardConfig` extends `Blackcube\Handler\HandlerConfig`.

### Removed

- `Blackcube\Dboard\Enums\OutputType`: use `Blackcube\Handler\OutputType`.

## [1.0.0] - 2026-10-25

Initial release.
