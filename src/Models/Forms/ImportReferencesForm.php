<?php

declare(strict_types=1);

/**
 * ImportReferencesForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;

/**
 * Import step 3 — references correction form.
 */
final class ImportReferencesForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected ?string $slugPath = null;
    protected ?string $languageId = null;
    protected ?int $typeId = null;
    protected ?int $hostId = null;
    protected array $authors = [];
    protected array $blocs = [];
    protected array $xeoBlocs = [];

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['slugPath', 'languageId', 'typeId', 'hostId', 'authors', 'blocs', 'xeoBlocs'],
        ];
    }

    public function rules(): array
    {
        return [
            'slugPath' => [
                new Length(max: 255),
            ],
            'languageId' => [
                new Length(max: 10),
            ],
            'typeId' => [
                new Integer(min: 1),
            ],
            'hostId' => [
                new Integer(min: 1),
            ],
            'authors' => [
                new Each([new Integer(min: 1)]),
            ],
            'blocs' => [
                new Each([new Integer(min: 1)]),
            ],
            'xeoBlocs' => [
                new Each([new Integer(min: 1)]),
            ],
        ];
    }

    public function setSlugPath(?string $slugPath): void
    {
        $this->slugPath = $slugPath;
    }

    public function getSlugPath(): ?string
    {
        return $this->slugPath;
    }

    public function setLanguageId(?string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getLanguageId(): ?string
    {
        return $this->languageId;
    }

    public function setTypeId(?int $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setHostId(?int $hostId): void
    {
        $this->hostId = $hostId;
    }

    public function getHostId(): ?int
    {
        return $this->hostId;
    }

    public function setAuthors(array $authors): void
    {
        $this->authors = $authors;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setBlocs(array $blocs): void
    {
        $this->blocs = $blocs;
    }

    public function getBlocs(): array
    {
        return $this->blocs;
    }

    public function setXeoBlocs(array $xeoBlocs): void
    {
        $this->xeoBlocs = $xeoBlocs;
    }

    public function getXeoBlocs(): array
    {
        return $this->xeoBlocs;
    }

    protected function getRawLabels(): array
    {
        return [
            'slugPath' => 'Slug',
            'languageId' => 'Language',
            'typeId' => 'Type',
            'hostId' => 'Host',
            'authors' => 'Authors',
            'blocs' => 'Block schemas',
            'xeoBlocs' => 'XEO block schemas',
        ];
    }
}
