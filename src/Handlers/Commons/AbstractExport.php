<?php

declare(strict_types=1);

/**
 * AbstractExport.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Services\ExportService;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract export action for downloading JSON exports.
 * Extends AbstractAjaxHandler but overrides output() to return a file download.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractExport extends AbstractAjaxHandler
{
    /**
     * Returns the model class to export.
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the export file name prefix.
     *
     * @return string
     */
    abstract protected function getName(): string;

    /**
     * Whether to compress the export as ZIP.
     *
     * @return bool
     */
    protected function isCompressed(): bool
    {
        return false;
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
        CurrentRoute $currentRoute,
        protected ExportService $exportService,
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
     * Generates the export file data.
     *
     * @return array{content: string, filename: string, mimeType: string}
     */
    protected function generateExportFile(): array
    {
        $data = $this->exportService->export($this->models['main']);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $id = $this->models['main']->getId();

        if ($this->isCompressed()) {
            $content = $this->compress($json);
            $filename = $this->getName() . '-' . $id . '.zip';
            $mimeType = 'application/zip';
        } else {
            $content = $json;
            $filename = $this->getName() . '-' . $id . '.json';
            $mimeType = 'application/json';
        }

        return [
            'content' => $content,
            'filename' => $filename,
            'mimeType' => $mimeType,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $file = $this->generateExportFile();

        return [
            'type' => 'download',
            'content' => $file['content'],
            'filename' => $file['filename'],
            'mimeType' => $file['mimeType'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function output(array $outputData): ResponseInterface
    {
        if (($outputData['type'] ?? null) === 'download') {
            return $this->downloadContent(
                $outputData['content'],
                $outputData['filename'],
                ['mimeType' => $outputData['mimeType']]
            );
        }

        return parent::output($outputData);
    }

    /**
     * Compresses content to ZIP format.
     *
     * @param string $content The content to compress
     * @return string The compressed content
     */
    protected function compress(string $content): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $zip = new \ZipArchive();
        $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('export.json', $content);
        $zip->close();

        $compressed = file_get_contents($tempFile);
        unlink($tempFile);

        return $compressed;
    }
}
