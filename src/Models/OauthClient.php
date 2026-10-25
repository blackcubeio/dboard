<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models;

use Blackcube\Oauth2\Interfaces\ClientInterface;
use DateTimeImmutable;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\Event\Handler\DefaultDateTimeOnInsert;
use Yiisoft\ActiveRecord\Trait\EventsTrait;

/**
 * OAuth2 client of an addon population, registered dynamically (RFC 7591).
 *
 * Clients are public: they hold no secret and prove themselves with PKCE. The
 * population binds the client to the resource it was registered for.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
#[DefaultDateTimeOnInsert(null, 'dateCreate')]
class OauthClient extends ActiveRecord implements ClientInterface
{
    use EventsTrait;

    protected string $id = '';
    protected string $population = '';
    protected string $name = '';
    protected ?array $redirectUris = null;
    protected ?array $grants = null;
    protected ?DateTimeImmutable $dateCreate = null;

    public function events(): array
    {
        return [];
    }

    public function tableName(): string
    {
        return '{{%oauthClients}}';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getPopulation(): string
    {
        return $this->population;
    }

    public function setPopulation(string $population): void
    {
        $this->population = $population;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSecret(): ?string
    {
        return null;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUris ?? [];
    }

    /**
     * @param string[] $redirectUris
     */
    public function setRedirectUris(array $redirectUris): void
    {
        $this->redirectUris = $redirectUris;
    }

    public function getAllowedGrants(): array
    {
        return $this->grants ?? [];
    }

    /**
     * @param string[] $grants
     */
    public function setAllowedGrants(array $grants): void
    {
        $this->grants = $grants;
    }

    /**
     * A public client has no secret to check: PKCE is its proof.
     */
    public function validateSecret(string $secret): bool
    {
        return true;
    }

    public function getDateCreate(): ?DateTimeImmutable
    {
        return $this->dateCreate;
    }

    public static function queryById(string $clientId): ?static
    {
        return static::query()->andWhere([
            'id' => $clientId,
        ])->one();
    }
}
