<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Import;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;

/**
 * Import step 4 — Summary and execution.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step4 extends Import
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $stepResponse = null;

        $sessionData = $this->session->get(self::SESSION_IMPORT);
        if ($sessionData === null || isset($sessionData['mode']) === false) {
            $stepResponse = $this->redirect('dboard.import.step1');
        } else {
            $importedElement = $sessionData['data'];
            $elementType = $sessionData['elementType'];
            $mode = $sessionData['mode'];
            $targetPath = $sessionData['targetPath'] ?? null;
            $corrections = $sessionData['corrections'] ?? [];
            $error = null;

            if ($request->getMethod() === Method::POST) {
                $importReport = $this->importService->execute($importedElement, $mode, $targetPath, $corrections);

                if ($importReport['success'] === true && $importReport['model'] !== null) {
                    $this->session->remove(self::SESSION_IMPORT);

                    $route = $elementType === 'content' ? 'dboard.contents.edit' : 'dboard.tags.edit';
                    $stepResponse = $this->redirect($route, [
                        'id' => $importReport['model']->getId(),
                    ]);
                } else {
                    $error = $importReport['error'] ?? 'Unknown error during import';
                }
            }

            if ($stepResponse === null) {
                $stepResponse = $this->render('Import/step4', [
                    'urlGenerator' => $this->urlGenerator,
                    'importedElement' => $importedElement,
                    'elementType' => $elementType,
                    'mode' => $mode,
                    'targetPath' => $targetPath,
                    'corrections' => $corrections,
                    'error' => $error,
                ]);
            }
        }

        return $stepResponse;
    }
}
