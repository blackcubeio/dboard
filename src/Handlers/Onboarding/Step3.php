<?php

declare(strict_types=1);

/**
 * Step3.php
 *
 * @copyright 2010-2026 Blackcube
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
 */
final class Step3 extends Onboarding
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        if ($this->session->get(self::SESSION_ONBOARDING) !== true) {
            return $this->redirect('dboard.onboarding.step1');
        }

        $sessionData = $this->session->get(self::SESSION_KEY, []);
        if (empty($sessionData)) {
            return $this->redirect('dboard.onboarding.step1');
        }

        // Check if admin already exists (DB ready)
        $existingAdmin = Administrator::query()->andWhere(['email' => $sessionData['email']])->one();

        if ($existingAdmin === null) {
            try {
                // Initialize RBAC permissions and roles
                $this->rbacInitializer->initialize();

                // Create administrator
                $administrator = new Administrator();
                $administrator->setEmail($sessionData['email']);
                $administrator->setPassword($sessionData['password']);
                $administrator->setFirstname($sessionData['firstname']);
                $administrator->setLastname($sessionData['lastname']);
                $administrator->setActive(true);
                $administrator->save();

                // Assign ADMIN role to the new administrator
                $this->rbacManager->assign(Rbac::ROLE_ADMIN, (string) $administrator->getId());

                // Store admin ID in session
                $this->session->set(self::SESSION_ADMIN_ID, (int) $administrator->getId());
            } catch (\Throwable $throwable) {
                throw $throwable;
            }
        }

        return $this->render('Onboarding/step3', [
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
