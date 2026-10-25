<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * Service for Hazeltree (Content/Tag) + Elastic (Blocs) orchestration.
 * Handles load, validate and save with file processing.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class HazeltreeElasticService
{
    public function __construct(
        private FileService $fileService,
    ) {}

    /**
     * Capture initial file values from blocs before POST handling.
     * Call this BEFORE loading POST data.
     *
     * @param array<Bloc> $blocs
     * @return array<int, array<string, string|null>> blocId => [property => value]
     */
    public function captureInitialFileValues(array $blocs): array
    {
        $initial = [];
        foreach ($blocs as $bloc) {
            $initial[$bloc->getId()] = $this->fileService->extractFileValues($bloc);
        }
        return $initial;
    }

    /**
     * Save blocs after populating from forms, then process files.
     *
     * @param array<int, BlocForm> $blocForms indexed by bloc ID
     * @param array<Bloc> $blocs
     * @param ActiveRecord $parentEntity The parent entity (Content, Tag, etc.)
     * @param array<int, array<string, string|null>> $initialFileValues from captureInitialFileValues()
     */
    public function saveBlocs(
        array $blocForms,
        array $blocs,
        ActiveRecord $parentEntity,
        array $initialFileValues = []
    ): void {
        $filesToDelete = [];

        foreach ($blocs as $bloc) {
            $blocId = $bloc->getId();
            if (isset($blocForms[$blocId]) === true) {
                $blocForms[$blocId]->populateModel($bloc);
                if ($bloc->isActive() === false) {
                    $bloc->setActive(true);
                }
                $bloc->save();

                $this->fileService->processBlocFiles($bloc, $parentEntity);

                if (isset($initialFileValues[$blocId]) === true) {
                    $finalValues = $this->fileService->extractFileValues($bloc);
                    $filesToDelete[] = [
                        'initial' => $initialFileValues[$blocId],
                        'final' => $finalValues,
                    ];
                }
            }
        }

        foreach ($filesToDelete as $diff) {
            $this->fileService->deleteRemovedFiles($diff['initial'], $diff['final']);
        }
    }
}
