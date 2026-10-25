# dboard — Authentication

## Flow

1. `GET|POST <prefix>/login` (`Authentication\Login`, `LoginForm`): the password is checked through `Administrator::queryByIdentifierAndPassword()` and the browser receives the two cookies (see below). `POST <prefix>/auth/token` (`Authentication\Token`) is the OAuth2 token endpoint of the `dboard` population: grants `password`, `refresh_token` and `passkey` (`config/routes.php:261-270`).
2. Every guarded route carries `JwtAuthMiddleware` (`config/routes.php`): `withRbac([...])` for a permission list, bare class for "authenticated", `optional()` for a route open to anonymous visitors where the administrator is attached when present (`src/Middlewares/JwtAuthMiddleware.php:82-109`).
3. `LocaleMiddleware` runs first and sets the translator locale from `Accept-Language` when the primary language is one of `LocaleHelper::getLocales()` (`src/Middlewares/LocaleMiddleware.php:36-64`); `JwtAuthMiddleware` then applies the administrator locale when set (`JwtAuthMiddleware.php:171-174`).

## `JwtAuthMiddleware` (`src/Middlewares/JwtAuthMiddleware.php`)

Extends `blackcube/oauth2` `AbstractJwtMiddleware`. Constructor: `CypherKey`, `ScopeProvider`, `ClockInterface`, `ResponseFactoryInterface`, `UrlGeneratorInterface`, `DboardConfig`, `LoggerInterface`, `ManagerInterface` (RBAC), `TranslatorInterface`, `WebViewRenderer`, `Aliases` (`:63-77`).

`process()` (`:128-221`):

- Tokens: cookies `access_token` and `refresh_token`, an `Authorization: Bearer` header replacing the access cookie (`:223-236`).
- The access token is parsed, its `iss` must equal `CypherKey::getId()` (the `oauth2.issuer` param), then validated with the key (`:241-260`). Invalid or missing with a refresh cookie → `refreshAccessToken()`: a `refresh_token` grant played on an in-process bshaffer server built from `PopulationConfig` `dboard` (`Administrator`, `Client`, `RefreshToken`, `CypherKey`, access TTL 1 h, refresh TTL 15 days, grants `password` + `refresh_token`) and `Oauth2Storage` (`:265-346`); the token bodies are masked in the logs (`HIDE_TOKENS`, `:387-406`).
- No claims: anonymous → the handler when `optional`, else a `302` to `dboard.login` with `redirect=<request target>` for a GET (`:153-158`, `:348-371`).
- Claims: `Administrator::query()->andWhere(['id' => sub])->active()->one()`, else the login redirect (`:160-169`). Required permissions: one of them must pass `userHasPermission()`, else the `Forbidden/index` view with the guest layout and `403` (`:176-187`, `:373-382`).
- Granted: `ViewInjection::setUserId()` / `setAdministrator()`, request attributes `userId` and `administrator`, then the handler (`:189-195`); fresh tokens are written back as cookies on the route prefix path, `SameSite=Lax`, `Secure`, access `HttpOnly=false` (read by the Aurelia side), refresh `HttpOnly=true` (`:197-215`).

## Passkeys (WebAuthn)

- Registration: `GET <prefix>/account/{id}/passkeys` (`Account\PasskeysInit`) returns the creation options, the challenge kept in session; `POST` (`Account\Passkeys`) validates the attestation with `WebauthnHelper::getAttestationValidator()` and saves through `WebauthnHelper::savePasskey(CredentialRecord, name, administratorId)`; `DELETE` removes one (`config/routes.php:250-259`, `src/Components/WebauthnHelper.php:84-98`).
- Authentication: `POST <prefix>/auth/challenges` (`Authentication\Challenges`, optional auth) returns the request options; the assertion goes to `POST <prefix>/auth/token` with `grant_type=passkey`, handled by `Services\PasskeyGrant` (`GrantTypeInterface`, identifier `passkey`, `validateRequest()` checks the assertion against the stored `Passkey`, `getUserId()` the administrator, `src/Services/PasskeyGrant.php:33-153`).
- `WebauthnConfig`: `getRpId(?request)` (param or request host), `getRpName()`, `getTimeout()`, `getChallengeLength()` (`src/Services/WebauthnConfig.php:31-53`), wired from the `webauthn` params (`config/common/di/oauth2.php:28-36`).
- Models: `Passkey` (credential id, name, administrator, type, attestation type, aaguid, public key, user handle, counter, JSON, active, `src/Models/Passkey.php:31-189`) and `PasskeyDevice` (aaguid registry); `PasskeyService::importAaguid()` fills the registry and extracts the SVG icons under `@dboard/Assets/Passkeys/Img`, run by `dboard:refreshAaguid` (`src/Services/PasskeyService.php:26-92`, `src/Commands/RefreshAaguidCommand.php:32`).

## Keys

`CypherKey` of `blackcube/oauth2` is bound from the `oauth2` params: `issuer` (the key id and JWT `iss`), `algorithm` (`RS256` default), `rsPublicKey` / `rsPrivateKey` paths (generated on first use when missing), `hsKey` for `HS*`, `temporaryPath` (`config/common/di/oauth2.php:17-27`).

## `ViewInjection` (`src/Services/ViewInjection.php`)

`CommonParametersInjectionInterface` of yii-view-renderer: every view receives `administrator`, `userCan` (closure on the RBAC manager), `ssrRoutes` (the `SsrRouteProviderInterface` routes as `route => label`, when `blackcube/ssr` is present) and `translator` (`:91-99`). State is static, filled by the middleware (`:49-57`).

## Administrator (`src/Models/Administrator.php`)

`UserInterface` of `blackcube/oauth2`: `getId()`, `getIdentifier()` (email), password hashed in `setPassword()`, `getLocale()`, `isActive()`, `getRestrictedScopes()`, `getPasskeysQuery()`; `query()` returns a `ScopedQuery` with `active()` (`src/Models/ScopedQuery.php:53-60`); `queryById()`, `queryByIdentifier()`, `queryByIdentifierAndPassword()` (`:161-187`). Account screen: `Account\Index` (`AccountForm`, own profile and locale).
