<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Services\AddonActivation;
use Blackcube\Dcore\Services\AddonService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Action (POST) on an add-on, confirmed by the matching modal: plays it through
 * AddonActivation, closes the dialog, reloads the list and toasts. 404 when the action is not
 * open on the add-on.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractAction extends AbstractBaseHandler
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
        protected AddonService $addonService,
        protected AddonActivation $addonActivation,
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

    abstract protected function act(string $id): void;

    abstract protected function getSuccessMessage(string $name): string;

    /**
     * Whether the action is open on the add-on: 404 otherwise.
     */
    abstract protected function isOpen(string $id): bool;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $id = (string) $this->currentRoute->getArgument('id');

        $response = null;
        if ($this->isOpen($id) === false) {
            $response = $this->responseFactory->createResponse(Status::NOT_FOUND);
        } else {
            $name = $this->addonService->getName($id);
            $this->act($id);

            $response = $this->renderJson([
                ...AureliaCommunication::dialog(DialogAction::Close),
                ...AureliaCommunication::ajaxify(
                    'addons-list',
                    $this->urlGenerator->generate('dboard.addons')
                ),
                ...AureliaCommunication::toast(
                    $this->translator->translate('Success', category: 'dboard-modules'),
                    $this->getSuccessMessage($name),
                    UiLevel::Success
                ),
            ]);
        }
        return $response;
    }
}
