<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

use Blackcube\Dboard\Interfaces\AddonConfigureInterface;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dcore\Services\AddonService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Lists the declared add-ons with their state (active, inactive, update available) and the
 * actions open on each, then the orphan rows of the registry whose package is gone.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractBaseHandler
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
        protected AddonService $addonService,
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

        $addons = [];
        foreach ($this->addonService->getDeclared() as $id => $class) {
            if ($this->addonService->isDeclared($id) === true) {
                $active = $this->addonService->isActive($id);
                $addons[] = [
                    'id' => $id,
                    'name' => $class::getName(),
                    'version' => $this->addonService->getInstalledVersion($id),
                    'active' => $active,
                    'pending' => $active === true && $this->addonService->hasPendingMigrations($id) === true,
                    'configureRoute' => is_subclass_of($class, AddonConfigureInterface::class) === true ? $class::getConfigureRoute() : null,
                    'orphan' => false,
                ];
            }
        }
        foreach ($this->addonService->getOrphans() as $id) {
            $addons[] = [
                'id' => $id,
                'name' => $id,
                'version' => null,
                'active' => false,
                'pending' => false,
                'configureRoute' => null,
                'orphan' => true,
            ];
        }

        $viewData = [
            'addons' => $addons,
            'urlGenerator' => $this->urlGenerator,
        ];

        return $this->isAjax() === true ? $this->renderPartial('Addons/_list', $viewData) : $this->render('Addons/index', $viewData);
    }
}
