<?php

declare(strict_types=1);

/**
 * Administrator.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models;

use Blackcube\Oauth2\Interfaces\UserInterface;
use DateTimeImmutable;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\ActiveRecord\Event\Handler\DefaultDateTimeOnInsert;
use Yiisoft\ActiveRecord\Event\Handler\SetDateTimeOnUpdate;
use Yiisoft\ActiveRecord\Trait\EventsTrait;
use Yiisoft\ActiveRecord\Trait\MagicRelationsTrait;

/**
 * Administrator model.
 */
#[DefaultDateTimeOnInsert(null, 'dateCreate')]
#[SetDateTimeOnUpdate(null, 'dateUpdate')]
final class Administrator extends ActiveRecord implements UserInterface
{
    use EventsTrait;
    use MagicRelationsTrait;

    public function events(): array
    {
        return [];
    }

    protected int $id;
    protected string $firstname = '';
    protected string $lastname = '';
    protected string $email = '';
    protected string $password = '';
    protected ?string $locale = null;
    protected bool $active = true;
    protected ?DateTimeImmutable $dateCreate = null;
    protected ?DateTimeImmutable $dateUpdate = null;
    protected ?array $restrictedScopes = null;

    public function tableName(): string
    {
        return '{{%administrators}}';
    }

    public function getId(): string
    {
        return (string) ($this->id ?? 0);
    }

    public function getIdentifier(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        if ($password === '') {
            return;
        }
        $info = password_get_info($password);
        if ($info['algoName'] === 'unknown') {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
        $this->password = $password;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getDateCreate(): ?DateTimeImmutable
    {
        return $this->dateCreate;
    }

    public function getDateUpdate(): ?DateTimeImmutable
    {
        return $this->dateUpdate;
    }

    public function getPasskeysQuery(): ActiveQueryInterface
    {
        return $this->hasMany(Passkey::class, ['administratorId' => 'id']);
    }

    public function getRestrictedScopes(): ?array
    {
        return $this->restrictedScopes;
    }

    public function setRestrictedScopes(?array $scopes): void
    {
        $this->restrictedScopes = $scopes;
    }

    public static function query(ActiveRecordInterface|string|null $modelClass = null): ScopedQuery
    {
        return new ScopedQuery($modelClass ?? static::class);
    }

    public static function queryById(string $id): ?static
    {
        return static::query()->active()->andWhere(['id' => (int) $id])->one();
    }

    public static function queryByIdentifier(string $identifier): ?static
    {
        return static::query()->active()->andWhere(['email' => $identifier])->one();
    }

    public static function queryByIdentifierAndPassword(string $identifier, string $password): ?static
    {
        $user = static::queryByIdentifier($identifier);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user->getPassword())) {
            return null;
        }

        return $user;
    }

    public function getName(): string
    {
        return trim($this->getFirstname() . ' ' . $this->getLastname());
    }
}
