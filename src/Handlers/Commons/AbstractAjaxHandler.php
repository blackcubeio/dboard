<?php

declare(strict_types=1);

/**
 * AbstractAjaxHandler.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for AJAX requests.
 * Implements the complete pipeline execution in handle() with error handling.
 * Exceptions thrown in handleMethod() are caught and returned as JSON error responses.
 *
 * Pipeline: setRequest() -> setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractAjaxHandler extends AbstractModelHandler
{
    /**
     * Invokes the action and executes the complete pipeline.
     * Returns early if setupAction() returns a response (e.g., 404 Not Found).
     * Exceptions in handleMethod() are caught and returned as JSON error toasts.
     *
     * @param ServerRequestInterface $request The server request
     * @return ResponseInterface The response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $response = $this->setupAction();
        if ($response !== null) {
            return $response;
        }

        $this->setupMethod();

        try {
            $this->handleMethod();
        } catch (\Throwable $e) {
            return $this->renderJson($this->prepareErrorOutput($e));
        }

        $outputData = $this->prepareOutputData();
        return $this->output($outputData);
    }

    /**
     * Prepares the error output in case of an exception.
     * Returns an error toast by default.
     * In debug mode, displays the actual exception message.
     * In production mode, displays a generic error message.
     * Can be overridden to add additional actions (e.g., close drawer/modal).
     *
     * @param \Throwable $e The exception that was thrown
     * @return array<string, mixed> The error response data (toast format)
     */
    protected function prepareErrorOutput(\Throwable $e): array
    {
        $this->logger->error($e->getMessage(), ['exception' => $e]);
        $message = $this->debug ? $e->getMessage() : 'Internal server error';

        return [
            ...AureliaCommunication::toast(
                $this->translator->translate('Error', category: 'dboard-common'),
                $message,
                UiColor::Danger
            )
        ];
    }
}
