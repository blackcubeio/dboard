<?php

declare(strict_types=1);

/**
 * ElasticSchemaForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * ElasticSchema form model.
 */
final class ElasticSchemaForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $name = '';
    protected ?string $schema = '{"type": "object","properties": {},"required": []}';
    protected ?string $view = null;
    protected ?string $mdMapping = null;
    protected string $kind = 'common';
    protected bool $builtin = false;
    protected bool $active = true;

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
    public function setSchema(?string $schema): void
    {
        $this->schema = $schema;
    }
    #[Bridge]
    public function getSchema(): ?string
    {
        return $this->schema;
    }
    #[Bridge]
    public function setView(?string $view): void
    {
        $this->view = $view;
    }
    #[Bridge]
    public function getView(): ?string
    {
        return $this->view;
    }
    #[Bridge]
    public function setMdMapping(?string $mdMapping): void
    {
        $this->mdMapping = $mdMapping;
    }
    #[Bridge]
    public function getMdMapping(): ?string
    {
        return $this->mdMapping;
    }
    #[Bridge]
    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }
    #[Bridge]
    public function getKind(): string
    {
        return $this->kind;
    }
    #[Bridge]
    public function setBuiltin(bool $builtin): void
    {
        $this->builtin = $builtin;
    }
    #[Bridge]
    public function isBuiltin(): bool
    {
        return $this->builtin;
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

    public function load(mixed $data, ?string $formName = null): bool
    {
        $this->active = false;
        return parent::load($data, $formName);
    }

    public function scenarios(): array
    {
        return [
            'create' => ['name', 'schema', 'view', 'mdMapping', 'kind', 'active'],
            'edit' => ['name', 'schema', 'view', 'mdMapping', 'kind', 'active'],
            'builtin' => [],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new Required(),
                new Length(max: 255),
            ],
            'schema' => [
                new Length(max: 65535),
            ],
            'view' => [
                new Length(max: 255),
            ],
            'mdMapping' => [
                new Length(max: 65535),
            ],
            'kind' => [
                new Required(),
                new In(array_map(fn($c) => $c->value, ElasticSchemaKind::cases())),
            ],
            'builtin' => [
                new BooleanValue(),
            ],
            'active' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'name' => 'Name',
            'schema' => 'Schema',
            'view' => 'View',
            'mdMapping' => 'MD Mapping',
            'kind' => 'Kind',
            'builtin' => 'Built-in',
            'active' => 'Active',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'name' => 'Elastic schema name',
            'schema' => 'JSON schema definition',
            'view' => 'View path (optional)',
            'mdMapping' => 'Markdown mapping (optional)',
            'kind' => 'Schema type',
            'builtin' => 'System built-in schema',
            'active' => 'Active schema',
        ];
    }

    public static function getKindOptions(): array
    {
        return array_combine(
            array_map(fn($c) => $c->value, ElasticSchemaKind::cases()),
            array_map(fn($c) => $c->name, ElasticSchemaKind::cases()),
        );
    }
}
