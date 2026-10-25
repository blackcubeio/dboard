<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract AJAX handler with session support but without the model pipeline.
 * Provides try/catch error handling like AbstractAjaxHandler.
 *
 * Pipeline: setRequest() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractSessionAjaxHandler extends AbstractSessionHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $handleMethodException = null;
        try {
            $this->handleMethod();
        } catch (\Throwable $e) {
            $handleMethodException = $e;
        }

        if ($handleMethodException !== null) {
            $ajaxResponse = $this->renderJson($this->prepareErrorOutput($handleMethodException));
        } else {
            $outputData = $this->prepareOutputData();
            $ajaxResponse = $this->output($outputData);
        }

        return $ajaxResponse;
    }

    abstract protected function handleMethod(): void;

    /**
     * @return array{type: string, data: array<string, mixed>}
     */
    abstract protected function prepareOutputData(): array;

    protected function prepareErrorOutput(\Throwable $e): array
    {
        $this->logger->error($e->getMessage(), [
            'exception' => $e,
        ]);
        $message = $this->debug ? $e->getMessage() : 'Internal server error';

        return [
            ...AureliaCommunication::toast(
                $this->translator->translate('Error', category: 'dboard-common'),
                $message,
                UiLevel::Danger
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
