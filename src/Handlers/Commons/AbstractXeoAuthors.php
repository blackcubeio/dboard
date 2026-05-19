<?php

declare(strict_types=1);

/**
 * AbstractXeoAuthors.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\XeoAuthorForm;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for XEO authors AJAX refresh.
 * POST-only. Receives posted author forms + addAuthorId, returns HTML partial.
 * Zero DB persistence.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractXeoAuthors extends AbstractAjaxHandler
{
    protected array $formModels = [];
    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display messages.
     *
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the author pivot class name.
     *
     * @return string Fully qualified class name (e.g., ContentAuthor::class, TagAuthor::class)
     */
    abstract protected function getAuthorPivotClass(): string;

    /**
     * Returns the FK column name in the author pivot table.
     *
     * @return string The FK column (e.g., 'contentId', 'tagId')
     */
    abstract protected function getAuthorPivotFkColumn(): string;

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        // Nothing — we process body params directly in prepareOutputData
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        // Nothing — no DB persistence
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $bodyParams = $this->getBodyParams();
        $formModel = new XeoAuthorForm(translator: $this->translator);
        $formModel->setScenario('xeo');
        $cnt = count($bodyParams[$formModel->getFormName()] ?? []);
        for($i = 0; $i < $cnt; $i++) {
            $form = new XeoAuthorForm(translator: $this->translator);
            $form->setScenario('xeo');
            $this->formModels[] = $form;
        }
        XeoAuthorForm::loadMultiple($this->formModels, $bodyParams);
        foreach ($this->formModels as $form) {
            $author = Author::query()
                ->andWhere(['id' => $form->getAuthorId()])
                ->one();
            if ($author !== null) {
                $form->setAuthorFirstname($author->getFirstname());
                $form->setAuthorLastname($author->getLastname());
                $form->setAuthorActive($author->isActive());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {

        $model = $this->models['main'];
        $pivotClass = $this->getAuthorPivotClass();

        // Available = active authors not in current list
        $query = Author::query()
            ->active()
            ->orderBy(['lastname' => SORT_ASC, 'firstname' => SORT_ASC]);
        $authorIds = array_map(fn(XeoAuthorForm $form) => $form->getAuthorId(), $this->formModels);
        if (!empty($authorIds)) {
            $query->andWhere(['not in', 'id', $authorIds]);
        }
        $availableAuthors = $query->all();

        // Authors refresh URL (same route)
        $authorsRefreshUrl = $this->urlGenerator->generate(
            $this->currentRoute->getName(),
            $this->extractPrimaryKeysFromModel()
        );

        return [
            'type' => OutputType::Partial->value,
            'view' => 'Commons/_xeo-authors-content',
            'data' => [
                'xeoAuthorForms' => $this->formModels,
                'availableAuthors' => $availableAuthors,
                'authorsRefreshUrl' => $authorsRefreshUrl,
            ],
        ];
    }
}
