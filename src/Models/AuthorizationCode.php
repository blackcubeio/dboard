<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models;

use DateTimeImmutable;
use DateTimeInterface;
use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * Authorization code issued by the consent screen of an addon population and
 * exchanged once on the token endpoint. It carries the PKCE challenge given at
 * authorization time so the token endpoint can check the verifier.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AuthorizationCode extends ActiveRecord
{
    protected string $code = '';
    protected int $administratorId = 0;
    protected string $clientId = '';
    protected string $redirectUri = '';
    protected ?string $scopes = null;
    protected DateTimeImmutable|string $expires = '';
    protected ?string $codeChallenge = null;
    protected ?string $codeChallengeMethod = null;
    protected DateTimeImmutable|string $dateCreate = '';

    public function tableName(): string
    {
        return '{{%authorizationCodes}}';
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getUserId(): string
    {
        return (string) $this->administratorId;
    }

    public function setUserId(string $userId): void
    {
        $this->administratorId = (int) $userId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        $scopes = [];
        if ($this->scopes !== null && $this->scopes !== '') {
            $scopes = explode(' ', $this->scopes);
        }
        return $scopes;
    }

    public function setScopesFromString(?string $scope): void
    {
        $this->scopes = $scope;
    }

    public function getExpires(): DateTimeInterface
    {
        $expires = $this->expires;
        if ($expires instanceof DateTimeImmutable === false) {
            $expires = new DateTimeImmutable($expires);
        }
        return $expires;
    }

    public function setExpires(DateTimeImmutable $expires): void
    {
        $this->expires = $expires->format('Y-m-d H:i:s');
    }

    public function getCodeChallenge(): ?string
    {
        return $this->codeChallenge;
    }

    public function setCodeChallenge(?string $codeChallenge): void
    {
        $this->codeChallenge = $codeChallenge;
    }

    public function getCodeChallengeMethod(): ?string
    {
        return $this->codeChallengeMethod;
    }

    public function setCodeChallengeMethod(?string $codeChallengeMethod): void
    {
        $this->codeChallengeMethod = $codeChallengeMethod;
    }

    public function save(?array $properties = null): void
    {
        if ($this->dateCreate === '') {
            $this->dateCreate = date('Y-m-d H:i:s');
        }
        parent::save($properties);
    }

    public static function queryByCode(string $code): ?static
    {
        return static::query()->andWhere([
            'code' => $code,
        ])->one();
    }
}
