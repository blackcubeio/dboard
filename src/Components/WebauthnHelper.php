<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Components;

use Blackcube\Dboard\Models\Passkey;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\CredentialRecord;

/**
 * WebAuthn helper utilities.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class WebauthnHelper
{
    private static ?\Symfony\Component\Serializer\Serializer $serializer = null;

    public static function base64UrlEncode(string $binary): string
    {
        $base64 = base64_encode($binary);
        $urlEncoded = strtr($base64, '+/', '-_');
        return rtrim($urlEncoded, '=');
    }

    public static function base64UrlDecode(string $encoded): string
    {
        $base64 = strtr($encoded, '-_', '+/');
        $padLength = 4 - (strlen($base64) % 4);
        if ($padLength < 4) {
            $base64 .= str_repeat('=', $padLength);
        }
        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            $decoded = '';
        }
        return $decoded;
    }

    public static function getSerializer(): \Symfony\Component\Serializer\Serializer
    {
        if (self::$serializer === null) {
            $attestationStatementSupportManager = AttestationStatementSupportManager::create();
            $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
            $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
            self::$serializer = $factory->create();
        }
        return self::$serializer;
    }

    public static function toArray(mixed $serializable): array
    {
        $jsonString = self::getSerializer()->serialize($serializable, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
        ]);
        return json_decode($jsonString, true);
    }

    public static function toObject(array|string $serialized, string $class): mixed
    {
        if (is_array($serialized) === true) {
            $serialized = json_encode($serialized, JSON_THROW_ON_ERROR);
        }
        return self::getSerializer()->deserialize($serialized, $class, 'json');
    }

    public static function getAttestationValidator(): AuthenticatorAttestationResponseValidator
    {
        $csmFactory = new CeremonyStepManagerFactory();
        $creationCSM = $csmFactory->creationCeremony();
        return AuthenticatorAttestationResponseValidator::create($creationCSM);
    }

    public static function getAssertionValidator(): AuthenticatorAssertionResponseValidator
    {
        $csmFactory = new CeremonyStepManagerFactory();
        $assertionCSM = $csmFactory->requestCeremony();
        return AuthenticatorAssertionResponseValidator::create($assertionCSM);
    }

    public static function savePasskey(CredentialRecord $source, string $name, int $administratorId): Passkey
    {
        $credentialValues = self::toArray($source);

        $passkey = new Passkey();
        $passkey->setId($credentialValues['publicKeyCredentialId']);
        $passkey->setName($name);
        $passkey->setAdministratorId($administratorId);
        $passkey->setType($credentialValues['type']);
        $passkey->setAttestationType($credentialValues['attestationType']);
        $passkey->setAaguid($credentialValues['aaguid'] ?? null);
        $passkey->setCredentialPublicKey($credentialValues['credentialPublicKey']);
        $passkey->setUserHandle($credentialValues['userHandle']);
        $passkey->setCounter($credentialValues['counter']);
        $passkey->setJsonData(json_encode($credentialValues, JSON_THROW_ON_ERROR));
        $passkey->save();

        return $passkey;
    }
}
