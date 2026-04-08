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
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\RbacInitializer;
use Blackcube\Dcore\Models\Content;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Onboarding step 3 action.
 */
final class Step3 extends Onboarding
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
        SessionInterface $session,
        protected ConnectionInterface $db,
        protected RbacInitializer $rbacInitializer,
        protected ManagerInterface $rbacManager,
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
            session: $session,
        );
    }

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
            // DB not ready, prepare it
            $transaction = $this->db->beginTransaction();
            try {
                // Initialize RBAC permissions and roles
                $this->rbacInitializer->initialize();

                // Create Content Root (Hazeltree assigns path '1' on empty table)
                $root = new Content();
                $root->setName('Root');
                $root->setActive(true);
                $root->save();

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

                $transaction->commit();

                // Store admin ID in session
                $this->session->set(self::SESSION_ADMIN_ID, (int) $administrator->getId());
            } catch (\Throwable $throwable) {
                $transaction->rollBack();
                throw $throwable;
            }
        }

        return $this->render('Onboarding/step3', [
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
