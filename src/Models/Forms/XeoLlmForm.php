<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeFormModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

/**
 * XEO LLM menu form model.
 * Manages link/unlink LLM menu operations.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class XeoLlmForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';

    protected ?int $parentId = null;

    #[Bridge]
    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    #[Bridge]
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function scenarios(): array
    {
        return [
            'link' => [
                'parentId',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'parentId' => [
                new Required(),
                new Integer(min: 1),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'parentId' => 'LLM category',
        ];
    }
}
