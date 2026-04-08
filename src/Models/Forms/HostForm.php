<?php

declare(strict_types=1);

/**
 * HostForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Result;

/**
 * Host form model.
 */
final class HostForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $name = '';
    protected ?string $siteName = null;
    protected ?string $siteAlternateName = null;
    protected ?string $siteDescription = null;
    protected bool $active = false;

    #[Bridge]
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    #[Bridge]
    public function getName(): string
    {
        return $this->name;
    }
    #[Bridge]
    public function setSiteName(?string $siteName): void
    {
        $this->siteName = $siteName;
    }
    #[Bridge]
    public function getSiteName(): ?string
    {
        return $this->siteName;
    }
    #[Bridge]
    public function setSiteAlternateName(?string $siteAlternateName): void
    {
        $this->siteAlternateName = $siteAlternateName;
    }
    #[Bridge]
    public function getSiteAlternateName(): ?string
    {
        return $this->siteAlternateName;
    }
    #[Bridge]
    public function setSiteDescription(?string $siteDescription): void
    {
        $this->siteDescription = $siteDescription;
    }
    #[Bridge]
    public function getSiteDescription(): ?string
    {
        return $this->siteDescription;
    }
    #[Bridge]
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
    #[Bridge]
    public function isActive(): bool
    {
        return $this->active;
    }

    public function load(mixed $data, ?string $scope = null): bool
    {
        if ($this->getScenario() !== 'edit-protected') {
            $this->active = false;
        }
        return parent::load($data, $scope);
    }

    public function scenarios(): array
    {
        return [
            'create' => ['name', 'siteName', 'siteAlternateName', 'siteDescription', 'active'],
            'edit' => ['name', 'siteName', 'siteAlternateName', 'siteDescription', 'active'],
            'edit-protected' => ['siteName', 'siteAlternateName', 'siteDescription'],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new Required(),
                new Length(max: 255),
                new Callback(
                    callback: static function (mixed $value): Result {
                        $result = new Result();
                        if (str_contains($value, '/')) {
                            $result->addError('Hostname must not contain "/" (e.g. localhost, blackcube.io)');
                        }
                        return $result;
                    }
                ),
            ],
            'siteName' => [
                new Length(max: 255),
            ],
            'siteAlternateName' => [
                new Length(max: 255),
            ],
            'siteDescription' => [
                new Length(max: 65535),
            ],
            'active' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'name' => 'Hostname',
            'siteName' => 'Site name',
            'siteAlternateName' => 'Alternate name',
            'siteDescription' => 'Site description',
            'active' => 'Active',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'name' => 'Domain name without protocol or path (e.g. localhost, blackcube.io)',
            'siteName' => 'Displayed site name for this domain',
            'siteAlternateName' => 'Alternate site name (optional)',
            'siteDescription' => 'Site description for this domain',
            'active' => 'Host status',
        ];
    }
}