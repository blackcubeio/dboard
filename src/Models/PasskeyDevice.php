<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models;

use DateTimeImmutable;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\ActiveRecord\Event\Handler\DefaultDateTimeOnInsert;
use Yiisoft\ActiveRecord\Event\Handler\SetDateTimeOnUpdate;
use Yiisoft\ActiveRecord\Trait\EventsTrait;

/**
 * PasskeyDevice model.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
#[DefaultDateTimeOnInsert(null, 'dateCreate')]
#[SetDateTimeOnUpdate(null, 'dateUpdate')]
class PasskeyDevice extends ActiveRecord
{
    use EventsTrait;

    protected string $aaguid;
    protected string $name = '';
    protected bool $iconLight = false;
    protected bool $iconDark = false;
    protected ?DateTimeImmutable $dateCreate = null;
    protected ?DateTimeImmutable $dateUpdate = null;

    public function tableName(): string
    {
        return '{{%passkeyDevices}}';
    }

    public function getAaguid(): ?string
    {
        return $this->aaguid ?? null;
    }

    public function setAaguid(string $aaguid): void
    {
        $this->aaguid = $aaguid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isIconLight(): bool
    {
        return $this->iconLight;
    }

    public function setIconLight(bool $iconLight): void
    {
        $this->iconLight = $iconLight;
    }

    public function isIconDark(): bool
    {
        return $this->iconDark;
    }

    public function setIconDark(bool $iconDark): void
    {
        $this->iconDark = $iconDark;
    }

    public function getDateCreate(): ?DateTimeImmutable
    {
        return $this->dateCreate;
    }

    public function getDateUpdate(): ?DateTimeImmutable
    {
        return $this->dateUpdate;
    }

    public static function query(ActiveRecordInterface|string|null $modelClass = null): ScopedQuery
    {
        return new ScopedQuery($modelClass ?? static::class);
    }
}
