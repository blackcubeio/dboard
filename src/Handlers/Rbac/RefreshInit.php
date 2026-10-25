<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Rbac;

use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * RBAC refresh init action (GET) — displays confirmation modal.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RefreshInit extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $content = (string) $this->renderPartial('Rbac/_refresh-content', [
            'urlGenerator' => $this->urlGenerator,
            'formAction' => $this->urlGenerator->generate('dboard.rbac.refresh'),
        ])->getBody();

        return $this->renderJson([
            ...AureliaCommunication::dialog(DialogAction::Keep),
            ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Modal)
                ->title($this->translator->translate('Refresh RBAC', category: 'dboard-modules'))
                ->warning()
                ->content($content)
                ->render()),
        ]);
    }
}
