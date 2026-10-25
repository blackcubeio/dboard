<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Import;

use Blackcube\Dboard\Models\Forms\ImportReferencesForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;

/**
 * Import step 3 — Validate references, show dropdowns for missing.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step3 extends Import
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
            $mode = $sessionData['mode'];
            $hasSlug = $importedElement['slug'] !== null;
            $hostId = $importedElement['slug']['hostId'] ?? 1;
            $isCreate = $mode === 'create';
            $formModel = new ImportReferencesForm(translator: $this->translator);

            $slugConflict = false;
            if ($isCreate === true && $hasSlug === true) {
                $originalSlugPath = $importedElement['slug']['path'] ?? '';
                $slugConflict = $this->importService->isSlugPathTaken($originalSlugPath, $hostId);
                if ($slugConflict === true) {
                    $formModel->setSlugPath($originalSlugPath);
                }
            }

            if ($request->getMethod() === Method::POST) {
                $formModel->load($request->getParsedBody());
                $corrections = $this->extractCorrections($formModel);

                if ($isCreate === true && $hasSlug === true) {
                    $candidateSlug = $corrections['slugPath'] ?? $importedElement['slug']['path'] ?? '';
                    $slugConflict = $this->importService->isSlugPathTaken($candidateSlug, $hostId);

                    if ($slugConflict === true) {
                        $formModel->setSlugPath($candidateSlug);
                        $validation = $this->importService->validateReferences($importedElement, $corrections);
                        $options = $this->importService->getReferenceOptions();

                        $stepResponse = $this->render('Import/step3', [
                            'urlGenerator' => $this->urlGenerator,
                            'importedElement' => $importedElement,
                            'elementType' => $sessionData['elementType'],
                            'missing' => $validation['missing'],
                            'warnings' => $validation['warnings'],
                            'options' => $options,
                            'formModel' => $formModel,
                            'slugConflict' => true,
                        ]);
                    }
                }

                if ($stepResponse === null) {
                    $sessionData['corrections'] = $corrections;
                    $this->session->set(self::SESSION_IMPORT, $sessionData);

                    $stepResponse = $this->redirect('dboard.import.step4');
                }
            } else {
                $validation = $this->importService->validateReferences($importedElement, $sessionData['corrections'] ?? []);
                $options = $this->importService->getReferenceOptions();

                $stepResponse = $this->render('Import/step3', [
                    'urlGenerator' => $this->urlGenerator,
                    'importedElement' => $importedElement,
                    'elementType' => $sessionData['elementType'],
                    'missing' => $validation['missing'],
                    'warnings' => $validation['warnings'],
                    'options' => $options,
                    'formModel' => $formModel,
                    'slugConflict' => $slugConflict,
                ]);
            }
        }

        return $stepResponse;
    }

    private function extractCorrections(ImportReferencesForm $formModel): array
    {
        $corrections = [];

        if ($formModel->getSlugPath() !== null && $formModel->getSlugPath() !== '') {
            $corrections['slugPath'] = $formModel->getSlugPath();
        }
        if ($formModel->getLanguageId() !== null && $formModel->getLanguageId() !== '') {
            $corrections['languageId'] = $formModel->getLanguageId();
        }
        if ($formModel->getTypeId() !== null) {
            $corrections['typeId'] = $formModel->getTypeId();
        }
        if ($formModel->getHostId() !== null) {
            $corrections['hostId'] = $formModel->getHostId();
        }
        foreach ($formModel->getAuthors() as $i => $authorId) {
            if (empty($authorId) === false) {
                $corrections['authors'][$i] = (int) $authorId;
            }
        }
        foreach ($formModel->getBlocs() as $i => $schemaId) {
            if (empty($schemaId) === false) {
                $corrections['blocs'][$i] = (int) $schemaId;
            }
        }
        foreach ($formModel->getXeoBlocs() as $i => $schemaId) {
            if (empty($schemaId) === false) {
                $corrections['xeoBlocs'][$i] = (int) $schemaId;
            }
        }

        return $corrections;
    }
}
