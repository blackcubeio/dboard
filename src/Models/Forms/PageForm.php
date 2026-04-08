<?php

declare(strict_types=1);

/**
 * PageForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Integer;

/**
 * Page form model for handling pagination parameters.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class PageForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';
    protected ?string $translateElasticCategory = 'dboard-content';

    /**
     * @var int Current page number
     */
    protected int $page = 1;

    /**
     * Sets the current page number.
     *
     * @param int $page The page number
     * @return void
     */
    #[Bridge]
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * Returns the current page number.
     *
     * @return int The page number
     */
    #[Bridge]
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'default' => ['page'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'page' => [
                new Integer(min: 1),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawLabels(): array
    {
        return [
            'page' => 'Page',
        ];
    }
}
