<?php

declare(strict_types=1);

/**
 * Preview.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PreviewForm;
use Blackcube\Dcore\Services\PreviewManager;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Widget\Widget;

/**
 * Preview toggle widget for the admin header.
 *
 * Usage:
 *   Widgets::preview()
 *       ->user($administrator)
 *       ->render()
 */
final class Preview extends Widget
{
    private ?Administrator $user = null;
    private bool $active = false;
    private ?string $simulateDate = null;
    private ?TranslatorInterface $translator = null;

    public function __construct(
        private readonly ManagerInterface $rbacManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SessionInterface $session,
    ) {
        $data = $this->session->get(PreviewManager::SESSION_KEY);
        if (is_array($data)) {
            $this->active = (bool) ($data['active'] ?? false);
            $this->simulateDate = $data['simulateDate'] ?? null;
        }
    }

    public function user(Administrator $user): self
    {
        $new = clone $this;
        $new->user = $user;
        return $new;
    }

    public function translator(TranslatorInterface $translator): self
    {
        $new = clone $this;
        $new->translator = $translator;
        return $new;
    }

    public function render(): string
    {
        if ($this->user === null) {
            return '';
        }

        $userId = (string) $this->user->getId();
        if (!$this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_SITE_PREVIEW)) {
            return '';
        }

        $formModel = new PreviewForm();
        $formModel->setSimulateDate($this->simulateDate);

        $params = [
            'active' => $this->active,
            'formModel' => $formModel,
            'toggleUrl' => $this->urlGenerator->generate('dboard.preview.toggle'),
            'translator' => $this->translator,
        ];

        extract($params, EXTR_OVERWRITE);
        ob_start();
        include __DIR__ . '/Views/preview.php';
        return ob_get_clean();
    }
}
