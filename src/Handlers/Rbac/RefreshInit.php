<?php

declare(strict_types=1);

/**
 * RefreshInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Rbac;

use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * RBAC refresh init action (GET) — displays confirmation modal.
 */
final class RefreshInit extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Refresh RBAC', category: 'dboard-modules'),
            'uiColor' => UiColor::Warning,
        ])->getBody();

        $content = (string) $this->renderPartial('Rbac/_refresh-content', [
            'urlGenerator' => $this->urlGenerator,
            'formAction' => $this->urlGenerator->generate('dboard.rbac.refresh'),
        ])->getBody();

        return $this->renderJson([
            ...AureliaCommunication::dialog(DialogAction::Keep),
            ...AureliaCommunication::dialogContent($header, $content, UiColor::Warning),
        ]);
    }
}
