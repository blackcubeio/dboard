<?php

declare(strict_types=1);

/**
 * ParameterForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dcore\Models\Parameter;
use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Result;

/**
 * Parameter form model.
 */
final class ParameterForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $domain = '';
    protected string $name = '';
    protected ?string $value = null;

    #[Bridge]
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }
    #[Bridge]
    public function getDomain(): string
    {
        return $this->domain;
    }
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
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
    #[Bridge]
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function scenarios(): array
    {
        return [
            'create' => ['domain', 'name', 'value'],
            'edit' => ['domain', 'name', 'value'],
        ];
    }

    public function rules(): array
    {
        $rules = [
            'domain' => [
                new Required(),
                new Length(max: 255),
            ],
            'name' => [
                new Required(),
                new Length(max: 255),
            ],
            'value' => [
                new Length(max: 65535),
            ],
        ];

        // Domain+name uniqueness validation only on creation
        if ($this->getScenario() === 'create') {
            $rules['name'][] = new Callback(
                callback: function (mixed $value): Result {
                    $result = new Result();
                    $exists = Parameter::query()
                        ->andWhere(['domain' => $this->domain, 'name' => $this->name])
                        ->exists();
                    if ($exists) {
                        $result->addError('A parameter with this domain and name already exists.');
                    }
                    return $result;
                },
            );
        }

        return $rules;
    }

    protected function getRawLabels(): array
    {
        return [
            'domain' => 'Domain',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'domain' => 'Parameter domain (e.g. app, site, ...)',
            'name' => 'Parameter name',
            'value' => 'Parameter value',
        ];
    }
}
