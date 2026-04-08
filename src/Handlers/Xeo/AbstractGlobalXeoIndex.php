<?php

declare(strict_types=1);

/**
 * AbstractGlobalXeoIndex.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
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
 * Abstract index action for GlobalXeo kinds.
 * Lists all hosts with their GlobalXeo status for a specific kind.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractGlobalXeoIndex extends AbstractBaseHandler
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

    /**
     * Returns the kind value (e.g., 'Organization', 'WebSite').
     */
    abstract protected function getKind(): string;

    /**
     * Returns the route name for editing a GlobalXeo of this kind.
     */
    abstract protected function getEditRoute(): string;

    /**
     * Returns the view name (e.g., 'Xeo/organization-index').
     */
    abstract protected function getViewName(): string;

    /**
     * Returns the label for the kind (e.g., 'Organisation', 'Site Web').
     */
    abstract protected function getKindLabel(): string;

    /**
     * Returns the route name for toggling a GlobalXeo of this kind.
     */
    abstract protected function getToggleRoute(): string;

    /**
     * Returns the route name for deleting a GlobalXeo of this kind.
     */
    abstract protected function getDeleteRoute(): string;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $hostData = [];
        foreach (Host::query()->orderBy(['name' => SORT_ASC])->each() as $host) {
            $hostData[] = [
                'host' => $host,
                'globalXeo' => GlobalXeo::query()
                    ->andWhere(['hostId' => $host->getId(), 'kind' => $this->getKind()])
                    ->one(),
            ];
        }

        $viewData = [
            'hostData' => $hostData,
            'editRoute' => $this->getEditRoute(),
            'toggleRoute' => $this->getToggleRoute(),
            'deleteRoute' => $this->getDeleteRoute(),
            'kindLabel' => $this->getKindLabel(),
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
        ];

        if ($this->isAjax()) {
            return $this->renderPartial('Xeo/_list', $viewData);
        }

        return $this->render($this->getViewName(), $viewData);
    }
}
