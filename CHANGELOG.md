# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed

- The sign-in screen is split in two: the ink panel of the brand on the left, the form alone on the right, the passkey offered before the password. The theme takes the Geist faces, bundled, and the ink, zinc and iris palette.
- The `dboard-login-device` attribute removes its whole `[data-login-device="block"]` block, separator included, when the browser cannot do WebAuthn.

## [1.0.1] - 2026-10-27

### Changed

- The handlers are built on `blackcube/handler`: `Handlers\Commons\AbstractBaseHandler` extends `Blackcube\Handler\AbstractHandler`, `DboardConfig` extends `Blackcube\Handler\HandlerConfig`.

### Removed

- `Blackcube\Dboard\Enums\OutputType`: use `Blackcube\Handler\OutputType`.

## [1.0.0] - 2026-10-25

Initial release.
