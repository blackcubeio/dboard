# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed

- The sign-in screen is split in two: the ink panel of the brand on the left, the form alone on the right, the passkey offered before the password. The theme takes the Geist faces, bundled, and the ink, zinc and iris palette.
- The `dboard-login-device` attribute removes its whole `[data-login-device="block"]` block, separator included, when the browser cannot do WebAuthn.
- The connected frame is a bar of ink, dark in both modes, rendered by the package's own `Widgets/Views/sidebar.php`: the brand, the navigation, and at its foot the preview toggle, the light / dark switch (`dboard-dark-toggle`), the account menu and the installed version. The header and the footer of the layout are gone; on a phone a thin bar carries the burger and the brand. The workbench is bounded to `max-w-7xl`.
- The root route of the panel is named `dboard.index`, so the dashboard entry is marked on it too.
- The dashboard opens on a sober heading (`Widgets::pageHeading()`, the heading of every page from now on), three counters in a description list, and two cards listing the latest contents and tags as rows.

### Added

- `yiisoft/rbac-db` as a development dependency: the test suite drives the sidebar against the RBAC tables the migrations create.

## [1.0.1] - 2026-10-27

### Changed

- The handlers are built on `blackcube/handler`: `Handlers\Commons\AbstractBaseHandler` extends `Blackcube\Handler\AbstractHandler`, `DboardConfig` extends `Blackcube\Handler\HandlerConfig`.

### Removed

- `Blackcube\Dboard\Enums\OutputType`: use `Blackcube\Handler\OutputType`.

## [1.0.0] - 2026-10-25

Initial release.
