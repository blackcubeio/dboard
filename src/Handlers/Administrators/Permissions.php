<?php

declare(strict_types=1);

/**
 * Permissions.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Administrator permissions drawer action.
 * Handles GET (display) and POST (save).
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Permissions extends AbstractAjaxHandler
{
    private bool $canAssign = false;
    private array $roles = [];
    private array $userRolesById = [];
    private array $userPermissionsById = [];
    private array $userAssignmentsById = [];
    private bool $saved = false;

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        CurrentRoute $currentRoute,
        protected ManagerInterface $rbacManager,
        protected AssignmentsStorageInterface $assignmentsStorage,
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
            currentRoute: $currentRoute,
        );
    }

    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: Administrator::class,
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $administrator = $this->models['main'];
        $userId = (string) $administrator->getId();

        // Self-protection
        $currentUserId = $this->request->getAttribute('userId');
        $this->canAssign = $currentUserId !== null && $currentUserId !== $administrator->getId();

        // Load RBAC data
        $userRoles = $this->rbacManager->getRolesByUserId($userId);
        $this->userRolesById = array_map(fn($role) => $role->getName(), $userRoles);

        $userPermissions = $this->rbacManager->getPermissionsByUserId($userId);
        $this->userPermissionsById = array_map(fn($perm) => $perm->getName(), $userPermissions);

        $assignments = $this->assignmentsStorage->getByUserId($userId);
        $this->userAssignmentsById = array_map(fn($assignment) => $assignment->getItemName(), $assignments);

        $this->roles = Rbac::getAllRoles();

        return null;
    }

    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        if (!$this->canAssign) {
            throw new \RuntimeException('You cannot modify your own permissions.');
        }

        $body = $this->getBodyParams();

        if (!is_array($body)) {
            throw new \RuntimeException('Invalid parameters.');
        }

        // Parse sélections
        $selectedRoles = [];
        $selectedPermissions = [];

        foreach ($body['roles'] ?? [] as $roleName => $value) {
            if ($value === '1') {
                $selectedRoles[] = $roleName;
            }
        }
        foreach ($body['permissions'] ?? [] as $permName => $value) {
            if ($value === '1') {
                $selectedPermissions[] = $permName;
            }
        }

        $userId = (string) $this->models['main']->getId();

        // Récupérer les assignments précédents (état AVANT)
        $previousAssignments = $this->assignmentsStorage->getByUserId($userId);
        $previousAssignmentNames = array_map(fn($a) => $a->getItemName(), $previousAssignments);

        // Calcul via Rbac statique avec état précédent
        $newAssignments = Rbac::rebuildAssignments($selectedRoles, $selectedPermissions, $previousAssignmentNames);

        // Revoke tout
        $currentAssignments = $this->assignmentsStorage->getByUserId($userId);
        foreach ($currentAssignments as $assignment) {
            $this->rbacManager->revoke($assignment->getItemName(), $userId);
        }

        // Assign nouveau
        foreach ($newAssignments as $itemName) {
            $this->rbacManager->assign($itemName, $userId);
        }

        $this->saved = true;
    }

    protected function prepareOutputData(): array
    {
        // POST success
        if ($this->saved) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast($this->translator->translate('Success', category: 'dboard-modules'), $this->translator->translate('Permissions updated.', category: 'dboard-modules'), UiColor::Success),
                ],
            ];
        }

        // GET - display drawer
        $administrator = $this->models['main'];

        $header = (string) $this->renderPartial('Commons/_drawer-header', [
            'title' => 'Permissions',
            'uiColor' => UiColor::Primary,
        ])->getBody();

        $content = (string) $this->renderPartial('Administrators/_permissions-content', [
            'administrator' => $administrator,
            'roles' => $this->roles,
            'userRolesById' => $this->userRolesById,
            'userPermissionsById' => $this->userPermissionsById,
            'userAssignmentsById' => $this->userAssignmentsById,
            'canAssign' => $this->canAssign,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Primary),
            ],
        ];
    }
}
