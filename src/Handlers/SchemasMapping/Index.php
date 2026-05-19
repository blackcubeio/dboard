<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\SchemasMapping;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * SchemaSchema index action.
 * Lists all schema↔schema mappings.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return SchemaSchema::class; }
    protected function getViewPrefix(): string { return 'SchemasMapping'; }
    protected function getOrderBy(): array { return []; }

    protected function setupAction(): void
    {
        $modelClass = $this->getModelClass();
        $this->query = $modelClass::query()
            ->joinWith(['regularElasticSchema AS reg', 'xeoElasticSchema AS xeo'])
            ->orderBy([
                'reg.kind' => SORT_ASC,
                'reg.order' => SORT_ASC,
                'xeo.kind' => SORT_ASC,
                'xeo.order' => SORT_ASC,
            ]);

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }
}
