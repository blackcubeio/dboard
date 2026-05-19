<?php

declare(strict_types=1);

/**
 * AbstractBlocDelete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\Dcore\Services\FileService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract action for deleting a bloc from an entity.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 * Uses common views from Views/Commons/ for the confirmation modal.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractBlocDelete extends AbstractAjaxHandler
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
     * Returns the model class name (parent entity).
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the pivot class name (e.g., ContentBloc).
     *
     * @return string Fully qualified class name of the pivot model
     */
    abstract protected function getPivotClass(): string;

    /**
     * Returns the entity name for display messages.
     *
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the pivot foreign key column name (e.g., 'contentId').
     *
     * @return string The column name
     */
    abstract protected function getPivotFkColumn(): string;

    /**
     * Returns the route name for the edit page (refresh blocs list).
     *
     * @return string The route name
     */
    abstract protected function getEditRoute(): string;

    /**
     * Returns the DOM element ID for the blocs list container.
     *
     * @return string The DOM element ID
     */
    abstract protected function getBlocsListId(): string;

    /**
     * @var ActiveRecord|null The pivot model linking entity and bloc
     */
    protected ?ActiveRecord $pivot = null;

    /**
     * @var Bloc|null The bloc to delete
     */
    protected ?Bloc $bloc = null;

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
     * Sets up the action by loading the bloc and verifying the pivot relationship.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $blocId = $this->currentRoute->getArgument('blocId');
        $this->bloc = Bloc::query()
            ->andWhere(['id' => $blocId])
            ->one();

        if ($this->bloc === null) {
            throw new \RuntimeException('Block not found.');
        }

        // Check pivot relationship
        $model = $this->models['main'];
        $pivotClass = $this->getPivotClass();
        $this->pivot = $pivotClass::query()
            ->andWhere([
                $this->getPivotFkColumn() => $model->getId(),
                'blocId' => $this->bloc->getId()
            ])
            ->one();

        if ($this->pivot === null) {
            throw new \RuntimeException('This block does not belong to this element.');
        }

        return null;
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
        if ($this->request->getMethod() !== Method::DELETE) {
            return;
        }

        $model = $this->models['main'];
        $entityPath = FileService::buildEntityPath($model);
        $blocPath = FileService::buildEntityPath($this->bloc);

        $this->beforeDelete(false);
        $transaction = $model->db()->beginTransaction();
        try {
            $this->beforeDelete(true);
            $model->detachBloc($this->bloc);
            $this->afterDelete(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->fileService->deleteDirectory(FileService::FS_PREFIX . $entityPath . '/' . $blocPath);
        $this->afterDelete(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];

        // DELETE: toast + close + ajaxify
        if ($this->request->getMethod() === Method::DELETE) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::ajaxify(
                        $this->getBlocsListId(),
                        $this->urlGenerator->generate($this->getEditRoute(), $this->extractPrimaryKeysFromModel()),
                        AjaxifyAction::Run,
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Block deleted.', category: 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        // GET: modal confirmation (common views)
        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Block deletion', category: 'dboard-common'),
            'uiColor' => UiColor::Danger,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_delete-bloc-content', [
            'bloc' => $this->bloc,
            'urlGenerator' => $this->urlGenerator,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                ['id' => $model->getId(), 'blocId' => $this->bloc->getId()]
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