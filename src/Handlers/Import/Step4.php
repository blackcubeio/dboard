<?php

declare(strict_types=1);

/**
 * Step4.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Import;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;

/**
 * Import step 4 — Summary and execution.
 */
final class Step4 extends Import
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $sessionData = $this->session->get(self::SESSION_IMPORT);
        if ($sessionData === null || !isset($sessionData['mode'])) {
            return $this->redirect('dboard.import.step1');
        }

        $data = $sessionData['data'];
        $elementType = $sessionData['elementType'];
        $mode = $sessionData['mode'];
        $targetPath = $sessionData['targetPath'] ?? null;
        $corrections = $sessionData['corrections'] ?? [];
        $error = null;

        if ($request->getMethod() === Method::POST) {
            $result = $this->importService->execute($data, $mode, $targetPath, $corrections);

            if ($result['success'] && $result['model'] !== null) {
                $this->session->remove(self::SESSION_IMPORT);

                $route = $elementType === 'content' ? 'dboard.contents.edit' : 'dboard.tags.edit';
                return $this->redirect($route, ['id' => $result['model']->getId()]);
            }

            $error = $result['error'] ?? 'Unknown error during import';
        }

        return $this->render('Import/step4', [
            'urlGenerator' => $this->urlGenerator,
            'data' => $data,
            'elementType' => $elementType,
            'mode' => $mode,
            'targetPath' => $targetPath,
            'corrections' => $corrections,
            'error' => $error,
        ]);
    }
}
