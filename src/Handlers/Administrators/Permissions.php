<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
use Blackcube\Dboard\Models\Forms\PermissionForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Permissions extends AbstractAjaxHandler
{
    private bool $canAssign = false;
    private array $roles = [];
    private array $userRoleNames = [];
    private array $userPermissionNames = [];
    private array $userAssignmentNames = [];
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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $administrator = $this->models['main'];
            $userId = (string) $administrator->getId();

            $currentUserId = $this->request->getAttribute('userId');
            $this->canAssign = $currentUserId !== null && $currentUserId !== $administrator->getId();

            $userRoles = $this->rbacManager->getRolesByUserId($userId);
            $userRoleNames = [];
            foreach ($userRoles as $roleName => $role) {
                $userRoleNames[$roleName] = $role->getName();
            }
            $this->userRoleNames = $userRoleNames;

            $userPermissions = $this->rbacManager->getPermissionsByUserId($userId);
            $userPermissionNames = [];
            foreach ($userPermissions as $permissionName => $permission) {
                $userPermissionNames[$permissionName] = $permission->getName();
            }
            $this->userPermissionNames = $userPermissionNames;

            $assignments = $this->assignmentsStorage->getByUserId($userId);
            $userAssignmentNames = [];
            foreach ($assignments as $itemName => $assignment) {
                $userAssignmentNames[$itemName] = $assignment->getItemName();
            }
            $this->userAssignmentNames = $userAssignmentNames;

            $this->roles = Rbac::getAllRoles();
        }

        return $setupResponse;
    }

    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            if ($this->canAssign === false) {
                throw new \RuntimeException('You cannot modify your own permissions.');
            }

            $body = $this->getBodyParams();

            if (is_array($body) === false) {
                throw new \RuntimeException('Invalid parameters.');
            }

            $form = new PermissionForm(translator: $this->translator);
            $form->load($body);
            if ($form->validate() === false) {
                throw new \RuntimeException('Invalid parameters.');
            }

            $userId = (string) $this->models['main']->getId();

            $previousAssignments = $this->assignmentsStorage->getByUserId($userId);
            $previousAssignmentNames = [];
            foreach ($previousAssignments as $itemName => $previousAssignment) {
                $previousAssignmentNames[$itemName] = $previousAssignment->getItemName();
            }

            $newAssignments = Rbac::rebuildAssignments($form->getRoles(), $form->getPermissions(), $previousAssignmentNames);

            $currentAssignments = $this->assignmentsStorage->getByUserId($userId);
            foreach ($currentAssignments as $assignment) {
                $this->rbacManager->revoke($assignment->getItemName(), $userId);
            }

            foreach ($newAssignments as $itemName) {
                $this->rbacManager->assign($itemName, $userId);
            }

            $this->saved = true;
        }
    }

    protected function prepareOutputData(): array
    {
        if ($this->saved === true) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast($this->translator->translate('Success', category: 'dboard-modules'), $this->translator->translate('Permissions updated.', category: 'dboard-modules'), UiLevel::Success),
                ],
            ];
        } else {
            $administrator = $this->models['main'];

            $form = new PermissionForm(translator: $this->translator);
            $form->setRoles($this->userRoleNames);
            $form->setPermissions($this->userPermissionNames);

            $content = (string) $this->renderPartial('Administrators/_permissions-content', [
                'administrator' => $administrator,
                'form' => $form,
                'roles' => $this->roles,
                'userRoleNames' => $this->userRoleNames,
                'userPermissionNames' => $this->userPermissionNames,
                'userAssignmentNames' => $this->userAssignmentNames,
                'canAssign' => $this->canAssign,
                'formAction' => $this->urlGenerator->generate(
                    $this->currentRoute->getName(),
                    $this->extractPrimaryKeysFromModel()
                ),
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                        ->title('Permissions')
                        ->primary()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}
