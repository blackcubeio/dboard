<?php

declare(strict_types=1);

/**
 * Onboarding.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Onboarding;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractSessionHandler;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\RbacInitializer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Onboarding abstract action.
 */
abstract class Onboarding extends AbstractSessionHandler
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
        CurrentRoute $currentRoute,
        protected ConnectionInterface $db,
        protected RbacInitializer $rbacInitializer,
        protected ManagerInterface $rbacManager,
    )
    {
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
            currentRoute: $currentRoute
        );
    }
    protected const SESSION_KEY = 'blap_onboarding';
    protected const SESSION_ONBOARDING = 'blap_onboarding_active';
    protected const SESSION_ADMIN_ID = 'blap_onboarding_admin_id';

    protected function getLayout(): string
    {
        return $this->aliases->get($this->dboardConfig->viewsAlias . '/Layouts/onboarding.php');
    }

    protected function isOnboardingRequired(): bool
    {
        return Administrator::query()->count() === 0;
    }
}
