<?php

declare(strict_types=1);

/**
 * AbstractSessionAjaxHandler.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract AJAX handler with session support but without the model pipeline.
 * Provides try/catch error handling like AbstractAjaxHandler.
 *
 * Pipeline: setRequest() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractSessionAjaxHandler extends AbstractSessionHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        try {
            $this->handleMethod();
        } catch (\Throwable $e) {
            return $this->renderJson($this->prepareErrorOutput($e));
        }

        $outputData = $this->prepareOutputData();
        return $this->output($outputData);
    }

    abstract protected function handleMethod(): void;

    /**
     * @return array{type: string, data: array<string, mixed>}
     */
    abstract protected function prepareOutputData(): array;

    protected function prepareErrorOutput(\Throwable $e): array
    {
        $this->logger->error($e->getMessage(), ['exception' => $e]);
        $message = $this->debug ? $e->getMessage() : 'Internal server error';

        return [
            ...AureliaCommunication::toast(
                $this->translator->translate('Error', category: 'dboard-common'),
                $message,
                UiColor::Danger
            )
        ];
    }

    protected function output(array $outputData): ResponseInterface
    {
        return match ($outputData['type']) {
            'render' => $this->render($outputData['view'], $outputData['data']),
            'partial' => $this->renderPartial($outputData['view'], $outputData['data']),
            'json' => $this->renderJson($outputData['data']),
            'redirect' => $this->redirect($outputData['data']['route'], $outputData['data']['params'] ?? []),
        };
    }
}
