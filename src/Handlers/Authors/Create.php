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
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\AuthorForm;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Author create action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Create extends AbstractCreate
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

    protected function getModelClass(): string { return Author::class; }
    protected function getFormModelClass(): string { return AuthorForm::class; }
    protected function getEntityName(): string { return 'author'; }
    protected function getViewPrefix(): string { return 'Authors'; }
    protected function getListRoute(): string { return 'dboard.authors'; }
    protected function getSuccessRoute(): string { return 'dboard.authors.edit'; }

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
            $outputData['data']['fileEndpoints'] = [
                'upload' => $this->urlGenerator->generate('dboard.authors.files.upload'),
                'preview' => $this->urlGenerator->generate('dboard.authors.files.preview'),
                'delete' => $this->urlGenerator->generate('dboard.authors.files.delete'),
            ];
        }
        return $outputData;
    }
}
