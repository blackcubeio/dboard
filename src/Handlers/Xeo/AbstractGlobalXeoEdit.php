<?php

declare(strict_types=1);

/**
 * AbstractGlobalXeoEdit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Models\Forms\GlobalXeoForm;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract edit action for GlobalXeo kinds.
 * Auto-creates the GlobalXeo if not found, uses BridgeFormModel + ElasticFieldRenderer.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractGlobalXeoEdit extends AbstractBaseHandler
{
    /**
     * Returns the kind value (e.g., 'Organization', 'WebSite').
     */
    abstract protected function getKind(): string;

    /**
     * Returns the default name for auto-create (e.g., 'Organisation', 'Site Web').
     */
    abstract protected function getDefaultName(): string;

    /**
     * Returns the view name (e.g., 'Xeo/organization', 'Xeo/website').
     */
    abstract protected function getViewName(): string;

    /**
     * Returns the index route for redirect after save (e.g., 'dboard.xeo.organization').
     */
    abstract protected function getIndexRoute(): string;

    /**
     * Returns the builtin schema name for auto-create.
     * Default: same as getKind(). Override for shared schemas (e.g., RawData).
     */
    protected function getSchemaName(): string
    {
        return $this->getKind();
    }

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        protected CurrentRoute $currentRoute,
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
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $hostId = (int) $this->currentRoute->getArgument('hostId');

        $host = Host::query()->andWhere(['id' => $hostId])->one();
        if ($host === null) {
            return $this->redirect($this->getIndexRoute());
        }

        $globalXeo = GlobalXeo::query()
            ->andWhere(['hostId' => $hostId, 'kind' => $this->getKind()])
            ->one();

        if ($globalXeo === null) {
            $globalXeo = $this->autoCreate($hostId);
        }

        // BridgeFormModel from ElasticTrait-powered model (pattern: AbstractElastic line 149)
        $formModel = GlobalXeoForm::createFromModel($globalXeo, $this->translator);
        $formModel->setScenario('elastic');

        if ($request->getMethod() === Method::POST) {
            $formModel->load($this->getBodyParams());
            if ($formModel->validate()) {
                $formModel->populateModel($globalXeo);
                $transaction = $globalXeo->db()->beginTransaction();
                try {
                    $globalXeo->save();
                    // Process files: move @bltmp/ -> @blfs/globalxeos/hosts/{hostId}/kinds/{kind}/
                    $this->fileService->processEntityFiles($globalXeo);
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                return $this->redirect($this->getIndexRoute());
            }
        }

        $fileEndpoints = [
            'upload' => $this->urlGenerator->generate('dboard.xeo.files.upload'),
            'preview' => $this->urlGenerator->generate('dboard.xeo.files.preview'),
            'delete' => $this->urlGenerator->generate('dboard.xeo.files.delete'),
        ];

        return $this->render($this->getViewName(), [
            'host' => $host,
            'globalXeo' => $globalXeo,
            'formModel' => $formModel,
            'fileEndpoints' => $fileEndpoints,
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
        ]);
    }

    /**
     * Auto-create a GlobalXeo for the given host with the builtin elastic schema.
     */
    private function autoCreate(int $hostId): GlobalXeo
    {
        $schema = ElasticSchema::query()
            ->andWhere(['name' => $this->getSchemaName(), 'kind' => 'xeo', 'builtin' => true])
            ->one();
        if ($schema === null) {
            throw new \RuntimeException('Builtin schema not found for ' . $this->getSchemaName());
        }

        $globalXeo = new GlobalXeo();
        $globalXeo->setHostId($hostId);
        $globalXeo->setKind($this->getKind());
        $globalXeo->setName($this->getDefaultName());
        $globalXeo->setElasticSchemaId($schema->getId());
        $transaction = $globalXeo->db()->beginTransaction();
        try {
            $globalXeo->save();
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $globalXeo;
    }
}
