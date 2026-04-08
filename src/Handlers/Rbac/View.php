<?php

declare(strict_types=1);

/**
 * View.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Rbac;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Services\RbacInitializer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * RBAC view action — displays detailed diff between code and DB.
 */
final class View extends AbstractBaseHandler
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
        protected CurrentRoute $currentRoute,
        protected RbacInitializer $rbacInitializer,
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

        $diff = $this->rbacInitializer->getDiff();

        return $this->render('Rbac/view', [
            'diff' => $diff,
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
        ]);
    }
}
