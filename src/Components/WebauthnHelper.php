<?php

declare(strict_types=1);

/**
 * WebauthnHelper.php
 *
 * @copyright 2010-2026 Blackcube
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
use Webauthn\PublicKeyCredentialSource;

/**
 * WebAuthn helper utilities.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class WebauthnHelper
{
    private static ?\Symfony\Component\Serializer\Serializer $serializer = null;

    public static function base64UrlEncode(string $data): string
    {
        $base64 = base64_encode($data);
        $urlEncoded = strtr($base64, '+/', '-_');
        return rtrim($urlEncoded, '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        $base64 = strtr($data, '-_', '+/');
        $padLength = 4 - (strlen($base64) % 4);
        if ($padLength < 4) {
            $base64 .= str_repeat('=', $padLength);
        }
        return base64_decode($base64, true) ?: '';
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

    public static function toArray(mixed $data): array
    {
        $jsonString = self::getSerializer()->serialize($data, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
        ]);
        return json_decode($jsonString, true);
    }

    public static function toObject(array|string $data, string $class): mixed
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_THROW_ON_ERROR);
        }
        return self::getSerializer()->deserialize($data, $class, 'json');
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

    public static function savePasskey(PublicKeyCredentialSource $source, string $name, int $administratorId): Passkey
    {
        $data = self::toArray($source);

        $passkey = new Passkey();
        $passkey->setId($data['publicKeyCredentialId']);
        $passkey->setName($name);
        $passkey->setAdministratorId($administratorId);
        $passkey->setType($data['type']);
        $passkey->setAttestationType($data['attestationType']);
        $passkey->setAaguid($data['aaguid'] ?? null);
        $passkey->setCredentialPublicKey($data['credentialPublicKey']);
        $passkey->setUserHandle($data['userHandle']);
        $passkey->setCounter($data['counter']);
        $passkey->setJsonData(json_encode($data, JSON_THROW_ON_ERROR));
        $passkey->save();

        return $passkey;
    }
}
