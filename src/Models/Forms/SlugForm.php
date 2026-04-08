<?php

declare(strict_types=1);

/**
 * SlugForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Slug;
use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Url;

/**
 * Slug form model.
 */
final class SlugForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';

    protected ?int $id = null;
    protected int $hostId = 1;
    protected string $path = '';
    protected ?string $targetUrl = null;
    protected ?int $httpCode = null;
    protected bool $active = false;

    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->active = false;
        return parent::load($data, $scope);
    }

    #[Bridge]
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    #[Bridge]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Bridge]
    public function setHostId(int $hostId): void
    {
        $this->hostId = $hostId;
    }

    #[Bridge]
    public function getHostId(): int
    {
        return $this->hostId;
    }

    #[Bridge]
    public function setPath(string $path): void
    {
        $this->path = ltrim($path, '/');
    }

    #[Bridge]
    public function getPath(): string
    {
        return $this->path;
    }

    #[Bridge]
    public function setTargetUrl(?string $targetUrl): void
    {
        $this->targetUrl = $targetUrl;
    }

    #[Bridge]
    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    #[Bridge]
    public function setHttpCode(?int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    #[Bridge]
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
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

    public function scenarios(): array
    {
        return [
            'create' => [
                'hostId',
                'path',
                'active',
            ],
            'edit' => [
                'hostId',
                'path',
                'active',
            ],
            'redirect' => [
                'hostId',
                'path',
                'targetUrl',
                'httpCode',
                'active',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'hostId' => [
                new Required(),
                new Integer(min: 1),
            ],
            'path' => [
                new Length(max: 255),
                new Callback(
                    callback: function (mixed $value): Result {
                        $result = new Result();
                        $normalizedPath = ltrim((string) $value, '/');

                        $query = Slug::query()
                            ->andWhere(['hostId' => $this->getHostId()])
                            ->andWhere(['path' => $normalizedPath]);

                        if ($this->getId() !== null) {
                            $query->andWhere(['!=', 'id', $this->getId()]);
                        }

                        if ($query->exists()) {
                            $result->addError('This path already exists for this host.');
                        }

                        return $result;
                    }
                ),
            ],
            'active' => [
                new BooleanValue(),
            ],
            'targetUrl' => [
                new Url(skipOnEmpty: true),
                new Length(max: 512),
            ],
            'httpCode' => [
                new In([null, 301, 302, 307, 308], skipOnEmpty: true),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'hostId' => 'Host',
            'path' => 'Path',
            'targetUrl' => 'Redirect URL',
            'httpCode' => 'HTTP code',
            'active' => 'Active',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'path' => 'Relative path without leading slash (e.g. my-content)',
            'targetUrl' => 'External redirect URL (optional)',
            'httpCode' => 'HTTP redirect code (301, 302...)',
            'active' => 'Makes the slug accessible',
        ];
    }

    public static function getHostOptions(): array
    {
        $hosts = Host::query()->all();
        $options = [];
        foreach ($hosts as $host) {
            $options[$host->getId()] = $host->getName().($host->isActive() ? '' : ' (inactive)');
        }
        return $options;
    }

    public static function getHttpCodeOptions(): array
    {
        return [
            '301' => '301 - Permanent redirect',
            '302' => '302 - Temporary redirect',
            '307' => '307 - Temporary redirect (preserves method)',
            '308' => '308 - Permanent redirect (preserves method)',
        ];
    }
}