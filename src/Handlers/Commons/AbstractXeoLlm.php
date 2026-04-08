<?php

declare(strict_types=1);

/**
 * AbstractXeoLlm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\XeoLlmForm;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;

/**
 * Abstract action for XEO LLM menu AJAX management.
 * GET: returns partial HTML for the LLM section.
 * POST: creates a new LlmMenu level 3 node linked to the entity.
 * DELETE: removes the LlmMenu level 3 node linked to the entity.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractXeoLlm extends AbstractAjaxHandler
{
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
     * Returns the FK column name in LlmMenu that points to this entity.
     *
     * @return string The FK column (e.g., 'contentId', 'tagId')
     */
    abstract protected function getLlmMenuFkColumn(): string;

    /**
     * @var LlmMenu|null The currently associated LlmMenu node
     */
    protected ?LlmMenu $llmMenu = null;

    /**
     * @var XeoLlmForm The form model for linking
     */
    protected ?XeoLlmForm $linkFormModel = null;

    /**
     * @var bool Whether a link was created
     */
    protected bool $linked = false;

    /**
     * @var bool Whether a link was removed
     */
    protected bool $unlinked = false;

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
    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $model = $this->models['main'];
        $fkColumn = $this->getLlmMenuFkColumn();

        $this->llmMenu = LlmMenu::query()
            ->andWhere([$fkColumn => $model->getId()])
            ->one();

        $this->linkFormModel = new XeoLlmForm(translator: $this->translator);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $this->linkFormModel->setScenario('link');
            $this->linkFormModel->load($this->getBodyParams());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        $method = $this->request->getMethod();
        $model = $this->models['main'];
        $fkColumn = $this->getLlmMenuFkColumn();
        $fkSetter = 'set' . ucfirst($fkColumn);

        if ($method === Method::POST) {
            if ($this->llmMenu !== null) {
                throw new \RuntimeException('Already linked to an LLM menu.');
            }

            if (!$this->linkFormModel->validate()) {
                return;
            }

            $parentId = $this->linkFormModel->getParentId();

            $parent = LlmMenu::query()
                ->andWhere(['id' => $parentId])
                ->andWhere(['level' => 2])
                ->one();

            if ($parent === null) {
                throw new \RuntimeException('Invalid parent category.');
            }

            $child = new LlmMenu();
            $child->setName($model->getName());
            $child->{$fkSetter}($model->getId());
            $child->saveInto($parent);

            $this->llmMenu = $child;
            $this->linked = true;
        } elseif ($method === Method::DELETE) {
            if ($this->llmMenu === null) {
                throw new \RuntimeException('No LLM menu association found.');
            }

            $this->llmMenu->delete();
            $this->llmMenu = null;
            $this->unlinked = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $routeParams = $this->extractPrimaryKeysFromModel();
        $llmRefreshUrl = $this->urlGenerator->generate(
            $this->currentRoute->getName(),
            $routeParams
        );

        if ($this->linked) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::ajaxify('xeo-llm', $llmRefreshUrl, AjaxifyAction::Refresh),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('LLM menu linked.', category: 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        if ($this->unlinked) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::ajaxify('xeo-llm', $llmRefreshUrl, AjaxifyAction::Refresh),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('LLM menu unlinked.', category: 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        // GET — return partial
        $categories = LlmMenu::query()
            ->andWhere(['level' => 2])
            ->orderBy(['left' => SORT_ASC])
            ->all();

        return [
            'type' => OutputType::Partial->value,
            'view' => 'Commons/_xeo-llm-content',
            'data' => [
                'llmMenu' => $this->llmMenu,
                'categories' => $categories,
                'linkFormModel' => $this->linkFormModel,
                'llmRefreshUrl' => $llmRefreshUrl,
            ],
        ];
    }
}
