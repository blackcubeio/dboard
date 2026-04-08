<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Preview;

use Blackcube\Dboard\Handlers\Commons\AbstractSessionAjaxHandler;
use Blackcube\Dcore\Services\PreviewManager;

/**
 * Toggle preview mode on/off.
 * Writes preview state to session.
 */
final class Toggle extends AbstractSessionAjaxHandler
{
    private bool $active = false;
    private ?string $simulateDate = null;

    protected function handleMethod(): void
    {
        $body = $this->request->getParsedBody();
        $this->active = (bool) ($body['active'] ?? false);
        $this->simulateDate = $body['simulateDate'] ?? null;

        if ($this->simulateDate !== null && trim($this->simulateDate) === '') {
            $this->simulateDate = null;
        }

        if (!$this->active) {
            $this->session->remove(PreviewManager::SESSION_KEY);
            return;
        }

        $this->session->set(PreviewManager::SESSION_KEY, [
            'active' => true,
            'simulateDate' => $this->simulateDate,
        ]);
    }

    protected function prepareOutputData(): array
    {
        return [
            'type' => 'json',
            'data' => [
                'success' => true,
                'active' => $this->active,
                'simulateDate' => $this->simulateDate,
            ],
        ];
    }
}
