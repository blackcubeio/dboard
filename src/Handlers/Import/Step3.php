<?php

declare(strict_types=1);

/**
 * Step3.php
 *
 * @copyright 2010-2026 Blackcube
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
 */
final class Step3 extends Import
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $sessionData = $this->session->get(self::SESSION_IMPORT);
        if ($sessionData === null || !isset($sessionData['mode'])) {
            return $this->redirect('dboard.import.step1');
        }

        $data = $sessionData['data'];
        $mode = $sessionData['mode'];
        $hasSlug = $data['slug'] !== null;
        $hostId = $data['slug']['hostId'] ?? 1;
        $isCreate = $mode === 'create';
        $formModel = new ImportReferencesForm(translator: $this->translator);

        // Slug conflict only matters in create mode with a slug
        $slugConflict = false;
        if ($isCreate && $hasSlug) {
            $originalSlugPath = $data['slug']['path'] ?? '';
            $slugConflict = $this->importService->isSlugPathTaken($originalSlugPath, $hostId);
            if ($slugConflict) {
                $formModel->setSlugPath($originalSlugPath);
            }
        }

        if ($request->getMethod() === Method::POST) {
            $formModel->load($request->getParsedBody());
            $corrections = $this->extractCorrections($formModel);

            // In create mode with slug, validate the slug is free before proceeding
            if ($isCreate && $hasSlug) {
                $candidateSlug = $corrections['slugPath'] ?? $data['slug']['path'] ?? '';
                $slugConflict = $this->importService->isSlugPathTaken($candidateSlug, $hostId);

                if ($slugConflict) {
                    // Stay on step3 — re-render with the conflict
                    $formModel->setSlugPath($candidateSlug);
                    $validation = $this->importService->validateReferences($data, $corrections);
                    $options = $this->importService->getReferenceOptions();

                    return $this->render('Import/step3', [
                        'urlGenerator' => $this->urlGenerator,
                        'data' => $data,
                        'elementType' => $sessionData['elementType'],
                        'missing' => $validation['missing'],
                        'warnings' => $validation['warnings'],
                        'options' => $options,
                        'formModel' => $formModel,
                        'slugConflict' => true,
                    ]);
                }
            }

            $sessionData['corrections'] = $corrections;
            $this->session->set(self::SESSION_IMPORT, $sessionData);

            return $this->redirect('dboard.import.step4');
        }

        $validation = $this->importService->validateReferences($data, $sessionData['corrections'] ?? []);
        $options = $this->importService->getReferenceOptions();

        return $this->render('Import/step3', [
            'urlGenerator' => $this->urlGenerator,
            'data' => $data,
            'elementType' => $sessionData['elementType'],
            'missing' => $validation['missing'],
            'warnings' => $validation['warnings'],
            'options' => $options,
            'formModel' => $formModel,
            'slugConflict' => $slugConflict,
        ]);
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
            if (!empty($authorId)) {
                $corrections['authors'][$i] = (int) $authorId;
            }
        }
        foreach ($formModel->getBlocs() as $i => $schemaId) {
            if (!empty($schemaId)) {
                $corrections['blocs'][$i] = (int) $schemaId;
            }
        }
        foreach ($formModel->getXeoBlocs() as $i => $schemaId) {
            if (!empty($schemaId)) {
                $corrections['xeoBlocs'][$i] = (int) $schemaId;
            }
        }

        return $corrections;
    }
}
