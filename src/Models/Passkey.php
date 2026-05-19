<?php

declare(strict_types=1);

/**
 * Passkey.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models;

use DateTimeImmutable;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\ActiveRecord\Event\Handler\DefaultDateTimeOnInsert;
use Yiisoft\ActiveRecord\Event\Handler\SetDateTimeOnUpdate;
use Yiisoft\ActiveRecord\Trait\EventsTrait;
use Yiisoft\ActiveRecord\Trait\MagicRelationsTrait;

/**
 * Passkey model.
 */
#[DefaultDateTimeOnInsert(null, 'dateCreate')]
#[SetDateTimeOnUpdate(null, 'dateUpdate')]
final class Passkey extends ActiveRecord
{
    use EventsTrait;
    use MagicRelationsTrait;

    protected string $id;
    protected string $name = '';
    protected int $administratorId;
    protected string $type = '';
    protected string $attestationType = '';
    protected ?string $aaguid = null;
    protected string $credentialPublicKey = '';
    protected string $userHandle = '';
    protected int $counter = 0;
    protected ?string $jsonData = null;
    protected bool $active = true;
    protected ?DateTimeImmutable $dateCreate = null;
    protected ?DateTimeImmutable $dateUpdate = null;

    public function tableName(): string
    {
        return '{{%passkeys}}';
    }

    public function getId(): ?string
    {
        return $this->id ?? null;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAdministratorId(): ?int
    {
        return $this->administratorId ?? null;
    }

    public function setAdministratorId(int $administratorId): void
    {
        $this->administratorId = $administratorId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAttestationType(): string
    {
        return $this->attestationType;
    }

    public function setAttestationType(string $attestationType): void
    {
        $this->attestationType = $attestationType;
    }

    public function getAaguid(): ?string
    {
        return $this->aaguid;
    }

    public function setAaguid(?string $aaguid): void
    {
        $this->aaguid = $aaguid;
    }

    public function getCredentialPublicKey(): string
    {
        return $this->credentialPublicKey;
    }

    public function setCredentialPublicKey(string $credentialPublicKey): void
    {
        $this->credentialPublicKey = $credentialPublicKey;
    }

    public function getUserHandle(): string
    {
        return $this->userHandle;
    }

    public function setUserHandle(string $userHandle): void
    {
        $this->userHandle = $userHandle;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public function getJsonData(): ?string
    {
        return $this->jsonData;
    }

    public function setJsonData(?string $jsonData): void
    {
        $this->jsonData = $jsonData;
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

    public function getAdministratorQuery(): ActiveQueryInterface
    {
        return $this->hasOne(Administrator::class, ['id' => 'administratorId']);
    }

    public function getPasskeyDeviceQuery(): ActiveQueryInterface
    {
        return $this->hasOne(PasskeyDevice::class, ['aaguid' => 'aaguid']);
    }

    public static function query(ActiveRecordInterface|string|null $modelClass = null): ScopedQuery
    {
        return new ScopedQuery($modelClass ?? static::class);
    }
}
