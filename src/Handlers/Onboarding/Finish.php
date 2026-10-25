<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Onboarding;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Onboarding finish action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Finish extends Onboarding
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        if ($this->session->get(self::SESSION_ONBOARDING) !== true) {
            $stepResponse = $this->redirect('dboard.onboarding.step1');
        } else {
            $this->session->remove(self::SESSION_KEY);
            $this->session->remove(self::SESSION_ADMIN_ID);
            $this->session->remove(self::SESSION_ONBOARDING);

            $stepResponse = $this->render('Onboarding/finish', [
                'urlGenerator' => $this->urlGenerator,
            ]);
        }

        return $stepResponse;
    }
}
