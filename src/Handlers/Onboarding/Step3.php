<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Onboarding;

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ElasticSchema;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Onboarding step 3 action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step3 extends Onboarding
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
                $existingAdmin = Administrator::query()->andWhere([
                    'email' => $sessionData['email'],
                ])->one();

                if ($existingAdmin === null) {
                    $this->rbacInitializer->initialize();

                    $administrator = new Administrator();
                    $administrator->setEmail($sessionData['email']);
                    $administrator->setPassword($sessionData['password']);
                    $administrator->setFirstname($sessionData['firstname']);
                    $administrator->setLastname($sessionData['lastname']);
                    $administrator->setActive(true);
                    $administrator->save();

                    $this->rbacManager->assign(Rbac::ROLE_ADMIN, (string) $administrator->getId());

                    $this->session->set(self::SESSION_ADMIN_ID, (int) $administrator->getId());
                }

                $stepResponse = $this->render('Onboarding/step3', [
                    'urlGenerator' => $this->urlGenerator,
                ]);
            }
        }

        return $stepResponse;
    }
}
