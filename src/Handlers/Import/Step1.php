<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Import;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dcore\Services\ImportService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Import step 1 — Upload JSON file and parse.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step1 extends Import
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
            session: $session,
            currentRoute: $currentRoute,
            importService: $importService,
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $stepResponse = null;

        if ($request->getMethod() === Method::GET) {
            $this->session->remove(self::SESSION_IMPORT);
        }

        $error = null;

        if ($request->getMethod() === Method::POST) {
            $bodyParams = $request->getParsedBody() ?? [];
            $filePath = $bodyParams['importFile'] ?? null;

            if ($filePath !== null && $filePath !== '' && $this->fileService->isTempPath($filePath) === true) {
                if ($this->fileService->fileExists($filePath) === true) {
                    $content = $this->fileService->read($filePath);

                    if (str_ends_with(strtolower($filePath), '.zip') === true) {
                        $content = $this->extractZip($content);
                        if ($content === null) {
                            $error = 'Cannot read ZIP file';
                        }
                    }

                    if ($error === null) {
                        $parsedImport = $this->importService->parseJson($content);

                        if ($parsedImport['valid'] === true) {
                            $this->session->set(self::SESSION_IMPORT, [
                                'data' => $parsedImport['data'],
                                'elementType' => $parsedImport['elementType'],
                            ]);

                            $stepResponse = $this->redirect('dboard.import.step2');
                        } else {
                            $error = $parsedImport['error'];
                        }
                    }

                    $this->fileService->delete($filePath);
                } else {
                    $error = 'Temporary file does not exist or has expired.';
                }
            } else {
                $error = 'Please select a JSON or ZIP file';
            }
        }

        if ($stepResponse === null) {
            $fileEndpoints = [
                'upload' => $this->urlGenerator->generate('dboard.import.files.upload'),
                'preview' => $this->urlGenerator->generate('dboard.import.files.preview'),
                'delete' => $this->urlGenerator->generate('dboard.import.files.delete'),
            ];

            $stepResponse = $this->render('Import/step1', [
                'urlGenerator' => $this->urlGenerator,
                'fileEndpoints' => $fileEndpoints,
                'error' => $error,
            ]);
        }

        return $stepResponse;
    }

    private function extractZip(string $content): ?string
    {
        $jsonContent = null;
        $tempFile = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($tempFile, $content);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) === true) {
            $jsonFound = false;
            for ($i = 0; $i < $zip->numFiles && $jsonFound === false; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($name), '.json') === true) {
                    $jsonContent = $zip->getFromIndex($i);
                    $jsonFound = true;
                }
            }

            $zip->close();
        }

        unlink($tempFile);

        return $jsonContent !== false ? $jsonContent : null;
    }
}
