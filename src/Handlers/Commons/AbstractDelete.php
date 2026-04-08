<?php

declare(strict_types=1);

/**
 * AbstractDelete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\Dcore\Services\FileService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract delete action for permanently removing models.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 * Uses common views from Views/Commons/ for the confirmation modal.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractDelete extends AbstractAjaxHandler
{
    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        CurrentRoute $currentRoute,
        protected FileService $fileService,
    ) {
        parent::__construct(
            logger: $logger,
            dboardConfig: $dboardConfig,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            jsonResponseFactory: $jsonResponseFactory,
            urlGenerator: $urlGenerator,
            aliases: $aliases,
            translator: $translator,
            currentRoute: $currentRoute,
        );
    }

    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display messages.
     *
     * @return string The entity name (e.g., 'contenu', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the list container ID for ajaxify refresh.
     *
     * @return string The DOM element ID
     */
    abstract protected function getListId(): string;

    /**
     * Returns the route name for the list refresh.
     *
     * @return string The route name
     */
    abstract protected function getListRoute(): string;

    /**
     * Returns the model name for display messages.
     * Override this method if the model uses a different method than getName().
     *
     * @return string The model name
     */
    protected function getModelName(): string
    {
        return $this->models['main']->getName();
    }

    /**
     * Returns the entity label for display in views.
     * Override this method for custom labels.
     *
     * @return string The entity label
     */
    protected function getEntityLabel(): string
    {
        return $this->getEntityName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null,
                isMain: true
            ),
        ];
    }

    /**
     * Hook called before delete.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeDelete(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called after delete.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterDelete(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::DELETE) {
            $model = $this->models['main'];
            $entityPath = FileService::buildEntityPath($model);
            $this->beforeDelete(false);
            $transaction = $model->db()->beginTransaction();
            try {
                $this->beforeDelete(true);
                $model->delete();
                $this->afterDelete(true);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $this->fileService->deleteDirectory(FileService::FS_PREFIX . $entityPath);
            $this->afterDelete(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $entityName = $this->getEntityName();

        // DELETE: toast + close + ajaxify
        if ($this->request->getMethod() === Method::DELETE) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::ajaxify(
                        $this->getListId(),
                        $this->urlGenerator->generate($this->getListRoute())
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('{entity} "{name}" deleted.', ['entity' => ucfirst($entityName), 'name' => $this->getModelName()], 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        // GET: modal confirmation (common views)
        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Deletion', category: 'dboard-common'),
            'uiColor' => UiColor::Danger,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_delete-content', [
            'modelName' => $this->getModelName(),
            'entityLabel' => $this->getEntityLabel(),
            'urlGenerator' => $this->urlGenerator,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
            ],
        ];
    }
}