<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Lists the addons declared in the dboard configuration, with a link to each
 * addon's own configuration page when it exposes one (configRoute).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        return $this->render('Addons/index', [
            'addons' => $this->dboardConfig->addons,
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
