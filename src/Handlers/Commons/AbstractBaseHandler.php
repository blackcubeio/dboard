<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Handler\AbstractHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Base of the admin panel handlers: the blackcube/handler base with the panel settings and the translator.
 * Views are named from the root of the views directory, whatever the handler namespace.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractBaseHandler extends AbstractHandler implements ViewsAliasInterface
{
    public function __construct(
        LoggerInterface $logger,
        protected DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        protected TranslatorInterface $translator,
    ) {
        parent::__construct(
            logger: $logger,
            config: $dboardConfig,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            jsonResponseFactory: $jsonResponseFactory,
            urlGenerator: $urlGenerator,
            aliases: $aliases,
        );
    }

    public function getViewsAlias(): string
    {
        return $this->dboardConfig->viewsAlias;
    }

    protected function getViewPath(): string
    {
        return $this->aliases->get($this->getViewsAlias());
    }

    protected function extractViewPath(string $view): string
    {
        return $view;
    }

    protected function getPageSize(): int
    {
        return 20;
    }

    protected function getListSize(): int
    {
        return 10;
    }
}