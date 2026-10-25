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
 * Onboarding step 2 action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step2 extends Onboarding
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        if ($this->session->get(self::SESSION_ONBOARDING) !== true) {
            $stepResponse = $this->redirect('dboard.onboarding.step1');
        } else {
            $sessionData = $this->session->get(self::SESSION_KEY, []);
            if (empty($sessionData) === true) {
                $stepResponse = $this->redirect('dboard.onboarding.step1');
            } else {
                $stepResponse = $this->render('Onboarding/step2', [
                    'administratorValues' => $sessionData,
                    'urlGenerator' => $this->urlGenerator,
                ]);
            }
        }

        return $stepResponse;
    }
}
