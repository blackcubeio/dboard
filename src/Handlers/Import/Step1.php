<?php

declare(strict_types=1);

/**
 * Step1.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Import;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Services\ImportService;
use Blackcube\Dcore\Services\FileService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Import step 1 — Upload JSON file and parse.
 */
final class Step1 extends Import
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
        SessionInterface $session,
        ImportService $importService,
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
            session: $session,
            importService: $importService,
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        if ($request->getMethod() === Method::GET) {
            $this->session->remove(self::SESSION_IMPORT);
        }

        $error = null;

        if ($request->getMethod() === Method::POST) {
            $bodyParams = $request->getParsedBody() ?? [];
            $filePath = $bodyParams['importFile'] ?? null;

            if ($filePath !== null && $filePath !== '' && $this->fileService->isTmpPath($filePath)) {
                if ($this->fileService->fileExists($filePath)) {
                    $content = $this->fileService->read($filePath);

                    // Handle ZIP
                    if (str_ends_with(strtolower($filePath), '.zip')) {
                        $content = $this->extractZip($content);
                        if ($content === null) {
                            $error = 'Cannot read ZIP file';
                        }
                    }

                    if ($error === null) {
                        $result = $this->importService->parseJson($content);

                        if ($result['valid']) {
                            $this->session->set(self::SESSION_IMPORT, [
                                'data' => $result['data'],
                                'elementType' => $result['elementType'],
                            ]);

                            $this->fileService->delete($filePath);
                            return $this->redirect('dboard.import.step2');
                        }

                        $error = $result['error'];
                    }

                    $this->fileService->delete($filePath);
                } else {
                    $error = 'Temporary file does not exist or has expired.';
                }
            } else {
                $error = 'Please select a JSON or ZIP file';
            }
        }

        $fileEndpoints = [
            'upload' => $this->urlGenerator->generate('dboard.import.files.upload'),
            'preview' => $this->urlGenerator->generate('dboard.import.files.preview'),
            'delete' => $this->urlGenerator->generate('dboard.import.files.delete'),
        ];

        return $this->render('Import/step1', [
            'urlGenerator' => $this->urlGenerator,
            'fileEndpoints' => $fileEndpoints,
            'error' => $error,
        ]);
    }

    private function extractZip(string $content): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($tempFile, $content);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) !== true) {
            unlink($tempFile);
            return null;
        }

        $jsonContent = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with(strtolower($name), '.json')) {
                $jsonContent = $zip->getFromIndex($i);
                break;
            }
        }

        $zip->close();
        unlink($tempFile);

        return $jsonContent !== false ? $jsonContent : null;
    }
}
