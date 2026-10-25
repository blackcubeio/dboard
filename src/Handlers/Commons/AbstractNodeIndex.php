<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\ActiveRecord\Hazeltree\HazeltreeInterface;
use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Enums\ListMode;
use Blackcube\Dboard\Models\Forms\FilterNodeForm;
use Yiisoft\Http\Method;

/**
 * Abstract index action for Hazeltree models (Content, Tag) with node filtering.
 * Extends AbstractIndex with FilterNodeForm support, session persistence,
 * and descendant filtering.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractNodeIndex extends AbstractIndex
{
    public const SESSION_LIST_MODE = 'dboard_list_mode_';
    public const SESSION_NODE_FILTER = 'dboard_node_filter_';

    protected ?FilterNodeForm $filterNodeForm = null;

    protected int $levelOffset = 0;

    protected function getFilterNodeForm(): FilterNodeForm
    {
        if ($this->filterNodeForm === null) {
            $this->filterNodeForm = new FilterNodeForm(translator: $this->translator);

            $modeKey = self::SESSION_LIST_MODE . $this->getViewPrefix();
            $sessionMode = $this->session->get($modeKey);
            $this->filterNodeForm->setModeFlat($sessionMode === ListMode::Flat->value);

            $nodeKey = self::SESSION_NODE_FILTER . $this->getViewPrefix();
            $sessionNodeId = $this->session->get($nodeKey);
            if ($sessionNodeId !== null) {
                $this->filterNodeForm->setNodeId((int) $sessionNodeId);
            }

            if ($this->request->getMethod() === Method::POST) {
                $bodyParams = $this->getBodyParams();
                if ($bodyParams !== null) {
                    $this->filterNodeForm->load($bodyParams);
                }
            }
        }
        return $this->filterNodeForm;
    }

    protected function getLevelOffset(): int
    {
        return $this->levelOffset;
    }

    protected function getOrderBy(): array
    {
        return $this->getMode() === ListMode::Flat ? [
            'dateUpdate' => SORT_DESC,
        ] : [
            'left' => SORT_ASC,
        ];
    }

    protected function getMode(): ListMode
    {
        return $this->getFilterNodeForm()->isModeFlat() === true ? ListMode::Flat : ListMode::Tree;
    }

    protected function setupAction(): void
    {
        $form = $this->getFilterNodeForm();
        $modelClass = $this->getModelClass();

        if ($form->validate() === true) {
            $modeKey = self::SESSION_LIST_MODE . $this->getViewPrefix();
            $mode = $form->isModeFlat() === true ? ListMode::Flat : ListMode::Tree;
            $this->session->set($modeKey, $mode->value);

            $nodeKey = self::SESSION_NODE_FILTER . $this->getViewPrefix();
            $nodeId = $form->getNodeId();
            if ($nodeId !== null) {
                $this->session->set($nodeKey, $nodeId);
            } else {
                $this->session->remove($nodeKey);
            }
        }

        $this->query = $modelClass::query()
            ->orderBy($this->getOrderBy());

        $nodeId = $form->getNodeId();
        if ($nodeId !== null) {
            /** @var HazeltreeInterface|null $node */
            $node = $modelClass::query()->andWhere([
                'id' => $nodeId,
            ])->one();
            if ($node !== null) {
                $this->query->forNode($node)->children()->includeDescendants();
                $this->levelOffset = $node->getLevel();
            }
        }

        if ($this->getSearchForm()->getSearch() !== '') {
            $this->query = $this->query->andWhere([
                'like',
                $this->getSearchColumn(),
                $this->getSearchForm()->getSearch(),
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    protected function getListView(): string
    {
        return $this->getMode() === ListMode::Flat ? '_list_flat' : '_list';
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        $outputData['data']['filterNodeForm'] = $this->getFilterNodeForm();
        $outputData['data']['nodeId'] = $this->getFilterNodeForm()->getNodeId();
        $outputData['data']['mode'] = $this->getMode();
        return $outputData;
    }

    /**
     * Eager-loads slug (with its host) and type for the current page,
     * removing the per-row N+1 of the node list views. Shared by all
     * routable node listings (contents, tags).
     *
     * @return array<int, mixed> The current page items, relations preloaded
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $slugIds = [];
        $typeIds = [];
        foreach ($this->paginator->read() as $node) {
            $items[] = $node;
            if ($node->getSlugId() !== null) {
                $slugIds[$node->getSlugId()] = $node->getSlugId();
            }
            if ($node->getTypeId() !== null) {
                $typeIds[$node->getTypeId()] = $node->getTypeId();
            }
        }

        $slugs = $this->getIndexedRecords(Slug::class, $slugIds);
        $hostIds = [];
        foreach ($slugs as $slug) {
            $hostIds[$slug->getHostId()] = $slug->getHostId();
        }
        $hosts = $this->getIndexedRecords(Host::class, $hostIds);
        foreach ($slugs as $slug) {
            $slug->populateRelation('host', $hosts[$slug->getHostId()] ?? null);
        }

        $types = $this->getIndexedRecords(Type::class, $typeIds);

        foreach ($items as $node) {
            $slugId = $node->getSlugId();
            $node->populateRelation('slug', $slugId !== null ? ($slugs[$slugId] ?? null) : null);
            $typeId = $node->getTypeId();
            $node->populateRelation('type', $typeId !== null ? ($types[$typeId] ?? null) : null);
        }

        return $items;
    }
}
