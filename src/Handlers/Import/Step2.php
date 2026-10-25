<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Import;

use Blackcube\Dboard\Models\Forms\ImportExistenceForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;

/**
 * Import step 2 — Check existence, choose overwrite or create.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step2 extends Import
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $sessionData = $this->session->get(self::SESSION_IMPORT);
        if ($sessionData === null) {
            $stepResponse = $this->redirect('dboard.import.step1');
        } else {
            $importedElement = $sessionData['data'];
            $elementType = $sessionData['elementType'];
            $existence = $this->importService->checkExistence($importedElement);
            $treeTargets = $this->importService->getTreeTargets($elementType);
            $exists = $existence['existsById'] || $existence['existsBySlug'];

            $formModel = new ImportExistenceForm(translator: $this->translator);
            $formModel->setTargetPath($importedElement['path'] ?? null);

            if ($request->getMethod() === Method::POST) {
                $formModel->load($request->getParsedBody());

                $sessionData['mode'] = $formModel->getMode();
                $sessionData['targetPath'] = $formModel->getTargetPath();
                $this->session->set(self::SESSION_IMPORT, $sessionData);

                $stepResponse = $this->redirect('dboard.import.step3');
            } else {
                $stepResponse = $this->render('Import/step2', [
                    'urlGenerator' => $this->urlGenerator,
                    'importedElement' => $importedElement,
                    'elementType' => $elementType,
                    'existence' => $existence,
                    'exists' => $exists,
                    'treeTargets' => $treeTargets,
                    'formModel' => $formModel,
                ]);
            }
        }

        return $stepResponse;
    }
}
