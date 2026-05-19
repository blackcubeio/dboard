# WebAuthn — Passkey Authentication

FIDO2 passwordless authentication using `web-auth/webauthn-lib` v5.

## Configuration

WebAuthn settings are defined in the `webauthn` block of `blackcube/dboard` params:

```php
'webauthn' => [
    'rpId' => null,                   // relying party ID (null = auto-derived from request host)
    'rpName' => 'Blackcube Admin',    // display name
    'timeout' => 60000,               // challenge timeout in ms
    'challengeLength' => 32,          // challenge length in bytes
],
```

### WebauthnConfig

| Method | Description |
|---|---|
| `getRpId(?ServerRequestInterface $request)` | Return `rpId` or extract from request host |
| `getRpName()` | Return `rpName` or `'Blackcube Admin'` |
| `getTimeout()` | Challenge timeout in ms |
| `getChallengeLength()` | Challenge length in bytes |

## Registration flow (attestation)

1. **Client** requests registration options from `GET /account/{id}/passkeys`
2. **Server** generates `PublicKeyCredentialCreationOptions` (challenge stored in session)
3. **Client** calls `navigator.credentials.create()` with the options
4. **Client** sends the attestation response to `POST /account/{id}/passkeys`
5. **Server** validates via `WebauthnHelper::getAttestationValidator()` and saves the credential

Saving a passkey:

```php
WebauthnHelper::savePasskey(
    PublicKeyCredentialSource $source,
    string $name,
    int $administratorId,
): Passkey
```

## Authentication flow (assertion)

1. **Client** requests challenge from `POST /auth/challenges`
2. **Server** generates `PublicKeyCredentialRequestOptions` (challenge stored in session)
3. **Client** calls `navigator.credentials.get()` with the options
4. **Client** sends the assertion response to `POST /auth/token` with grant type `passkey`
5. **Server** validates via `PasskeyGrant` (OAuth2 grant type) and issues JWT tokens

### PasskeyGrant

Custom OAuth2 grant type (`GrantTypeInterface`).

| Method | Description |
|---|---|
| `getQueryStringIdentifier()` | Returns `'passkey'` |
| `validateRequest(RequestInterface, ResponseInterface)` | Validate passkey assertion against stored credential |
| `getUserId()` | Administrator ID after validation |

## WebauthnHelper

Static utility class for WebAuthn operations.

| Method | Description |
|---|---|
| `base64UrlEncode(string)` | URL-safe base64 encoding |
| `base64UrlDecode(string)` | URL-safe base64 decoding |
| `getSerializer()` | Symfony Serializer configured for WebAuthn (NONE attestation) |
| `toArray(mixed $data)` | Serialize WebAuthn object to array |
| `toObject(array\|string $data, string $class)` | Deserialize to WebAuthn object |
| `getAttestationValidator()` | Create attestation response validator |
| `getAssertionValidator()` | Create assertion response validator |
| `savePasskey(PublicKeyCredentialSource, string, int)` | Save credential to database |

## Models

### Passkey

| Property | Type | Description |
|---|---|---|
| `id` | `string` | Credential ID |
| `name` | `string` | User-defined name |
| `administratorId` | `int` | FK to Administrator |
| `type` | `string` | Credential type |
| `attestationType` | `string` | Attestation type |
| `aaguid` | `?string` | Authenticator model GUID |
| `credentialPublicKey` | `string` | Public key (base64) |
| `userHandle` | `string` | User handle |
| `counter` | `int` | Signature counter |
| `jsonData` | `?string` | Full credential source JSON |
| `active` | `bool` | Active flag |

Relations:

```php
$passkey->getAdministratorQuery()   // associated Administrator
$passkey->getPasskeyDeviceQuery()   // device info via aaguid
```

### PasskeyDevice

AAGUID device registry. Populated by the `dboard:refreshAaguid` command.

| Property | Type | Description |
|---|---|---|
| `aaguid` | `string` | Primary key (GUID) |
| `name` | `string` | Device model name |
| `iconLight` | `bool` | Light SVG icon available |
| `iconDark` | `bool` | Dark SVG icon available |

## AAGUID registry

The `dboard:refreshAaguid` console command imports the combined AAGUID registry and extracts SVG device icons.

```bash
./yii dboard:refreshAaguid
```

Uses `PasskeyService`:

| Method | Description |
|---|---|
| `importAaguid()` | Import registry JSON, extract SVG icons, save PasskeyDevice records |
| `cleanIcons()` | Delete existing SVG icon files |
