<?php

declare(strict_types=1);

/**
 * TranslationForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

/**
 * Translation form model.
 * Manages link/unlink translation operations.
 */
final class TranslationForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';

    protected ?int $targetId = null;
    protected string $targetName = '';
    protected string $targetLanguageId = '';

    #[Bridge]
    public function setTargetId(?int $targetId): void
    {
        $this->targetId = $targetId;
    }

    #[Bridge]
    public function getTargetId(): ?int
    {
        return $this->targetId;
    }

    #[Bridge]
    public function setTargetName(string $targetName): void
    {
        $this->targetName = $targetName;
    }

    #[Bridge]
    public function getTargetName(): string
    {
        return $this->targetName;
    }

    #[Bridge]
    public function setTargetLanguageId(string $targetLanguageId): void
    {
        $this->targetLanguageId = $targetLanguageId;
    }

    #[Bridge]
    public function getTargetLanguageId(): string
    {
        return $this->targetLanguageId;
    }

    public function scenarios(): array
    {
        return [
            'link' => ['targetId'],
            'unlink' => ['targetId'],
        ];
    }

    public function rules(): array
    {
        return [
            'targetId' => [
                new Required(),
                new Integer(min: 1),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'targetId' => 'Content to link',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'targetId' => 'Select a content to link as translation',
        ];
    }
}
