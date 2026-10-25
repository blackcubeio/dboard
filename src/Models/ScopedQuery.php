<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models;

use Blackcube\ActiveRecord\BatchedEachQueryTrait;
use Yiisoft\ActiveRecord\ActiveQuery;

/**
 * Generic scoped query with automatic capability detection.
 *
 * Detects model capabilities and adapts available scopes:
 * - hasActive: model has 'active' property
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ScopedQuery extends ActiveQuery
{
    use BatchedEachQueryTrait;

    private ?string $tableName = null;
    private ?bool $hasActive = null;

    /**
     * Lazy initialization of capabilities.
     */
    private function ensureCapabilities(): void
    {
        if ($this->tableName === null) {
            $model = $this->getModel();
            $modelClass = $model::class;
            $this->tableName = $model->tableName();

            $this->hasActive = property_exists($modelClass, 'active');
        }
    }

    /**
     * Filter by active status.
     * Available if model has 'active' property.
     *
     * @param bool $active true for active records, false for inactive
     */
    public function active(bool $active = true): static
    {
        $this->ensureCapabilities();

        return $this->hasActive === false ? $this : $this->andWhere([
            $this->tableName . '.[[active]]' => $active,
        ]);
    }
}
