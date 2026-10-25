<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Passkeys;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;

/**
 * Passkeys index action.
 * User can only see their own passkeys.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Passkey::class; }
    protected function getViewPrefix(): string { return 'Passkeys'; }
    protected function getOrderBy(): array { return [
        'dateCreate' => SORT_DESC,
    ]; }
    protected function getSearchColumn(): string { return 'name'; }

    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = null;
        /** @var Administrator|null $administrator */
        $administrator = $this->request->getAttribute('administrator');

        if ($administrator === null) {
            $setupResponse = $this->responseFactory->createResponse(Status::UNAUTHORIZED);
        } else {
            $modelClass = $this->getModelClass();
            $this->query = $modelClass::query()
                ->active()
                ->andWhere([
                    'administratorId' => (int) $administrator->getId(),
                ])
                ->orderBy($this->getOrderBy());

            $search = $this->getSearchForm()->getSearch();
            if ($search !== '') {
                $this->query = $this->query->andWhere([
                    'like',
                    $this->getSearchColumn(),
                    $search,
                ]);
            }

            $this->paginator = (new ActiveQueryPaginator($this->query))
                ->withPageSize($this->getPageSize())
                ->withCurrentPage((int) $this->getPageForm()->getPage());
        }

        return $setupResponse;
    }
}
