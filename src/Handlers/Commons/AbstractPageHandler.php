<?php

declare(strict_types=1);

/**
 * AbstractPageHandler.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for page rendering.
 * Implements the complete pipeline execution in handle().
 * Exceptions are not caught and bubble up for standard error handling.
 *
 * Pipeline: setRequest() -> setupAction() -> setupMethod() -> handleMethod() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractPageHandler extends AbstractModelHandler
{
    /**
     * Invokes the action and executes the complete pipeline.
     * Returns early if setupAction() returns a response (e.g., 404 Not Found).
     * Exceptions are not caught and will propagate up the call stack.
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
        $this->handleMethod();

        $outputData = $this->prepareOutputData();
        return $this->output($outputData);
    }
}
