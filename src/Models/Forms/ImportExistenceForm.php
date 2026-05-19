<?php

declare(strict_types=1);

/**
 * ImportExistenceForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * Import step 2 — existence check form.
 */
final class ImportExistenceForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $mode = 'create';
    protected ?string $targetPath = null;

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['mode', 'targetPath'],
        ];
    }

    public function rules(): array
    {
        return [
            'mode' => [
                new Required(),
                new In(['create', 'overwrite']),
            ],
            'targetPath' => [
                new Length(max: 255),
            ],
        ];
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setTargetPath(?string $targetPath): void
    {
        $this->targetPath = $targetPath;
    }

    public function getTargetPath(): ?string
    {
        return $this->targetPath;
    }

    protected function getRawLabels(): array
    {
        return [
            'mode' => 'Mode',
            'targetPath' => 'Insert under',
        ];
    }
}
