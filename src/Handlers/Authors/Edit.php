<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\AuthorForm;
use Blackcube\Dboard\Services\HazeltreeElasticService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Author edit action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Edit extends AbstractEdit
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
        HazeltreeElasticService $hazeltreeElasticService,
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
            hazeltreeElasticService: $hazeltreeElasticService,
        );
    }

    protected function getModelClass(): string { return Author::class; }
    protected function getFormModelClass(): string { return AuthorForm::class; }
    protected function getEntityName(): string { return 'author'; }
    protected function getViewPrefix(): string { return 'Authors'; }
    protected function getListRoute(): string { return 'dboard.authors'; }

    protected function afterSave(bool $inTransaction): void
    {
        if ($inTransaction === true) {
            $model = $this->models['main'];
            $this->fileService->processRegularFiles($model, [
                'image',
            ], FileService::buildEntityPath($model));
            $model->save();
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        if (($outputData['type'] ?? null) === OutputType::Render->value) {
            $fileRoutePrefix = $this->getFileRoutePrefix();
            $outputData['data']['fileEndpoints'] = [
                'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
                'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
                'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
            ];
        }
        return $outputData;
    }
}
