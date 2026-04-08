<?php

declare(strict_types=1);

/**
 * MdExportForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Yiisoft\Validator\Rule\Required;

/**
 * MdExport form model.
 * Captures the LLM prompt for markdown export. No DB model behind.
 */
final class MdExportForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $prompt = '';

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['prompt'],
        ];
    }

    public function rules(): array
    {
        return [
            'prompt' => [
                new Required(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'prompt' => 'LLM prompt',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'prompt' => 'This prompt will be included in the markdown file for the LLM.',
        ];
    }
}
