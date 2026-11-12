<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
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
 * Confirmation modal (GET) of an action on an add-on. The confirm form POSTs to the same
 * path, handled by the matching action handler. 404 when the action is not open on the add-on.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractActionInit extends AbstractBaseHandler
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

    abstract protected function getTitle(): string;

    abstract protected function getMessage(string $name): string;

    abstract protected function getSubmitLabel(): string;

    /**
     * Whether the action is open on the add-on: 404 otherwise.
     */
    abstract protected function isOpen(string $id): bool;

    protected function getColor(): UiColor
    {
        return UiColor::Warning;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $id = (string) $this->currentRoute->getArgument('id');

        $response = null;
        if ($this->isOpen($id) === false) {
            $response = $this->responseFactory->createResponse(Status::NOT_FOUND);
        } else {
            $content = (string) $this->renderPartial('Addons/_action-content', [
                'message' => $this->getMessage($this->addonService->getName($id)),
                'formAction' => $this->urlGenerator->generate($this->currentRoute->getName(), [
                    'id' => $id,
                ]),
                'submitLabel' => $this->getSubmitLabel(),
                'danger' => $this->getColor() === UiColor::Danger,
            ])->getBody();

            $response = $this->renderJson([
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Modal)
                    ->title($this->getTitle())
                    ->color($this->getColor()->value)
                    ->content($content)
                    ->render()),
            ]);
        }
        return $response;
    }
}
