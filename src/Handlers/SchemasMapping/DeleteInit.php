<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\SchemasMapping;

use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * SchemaSchema delete init action (GET modal).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return SchemaSchema::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete mapping', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getColor(): UiColor { return UiColor::Danger; }

    protected function primaryKeys(): array
    {
        return [
            'regularElasticSchemaId',
            'xeoElasticSchemaId',
        ];
    }

    protected function getModelName(): string
    {
        $model = $this->models['main'];
        $regular = $model->regularElasticSchema;
        $xeo = $model->xeoElasticSchema;
        return ($regular !== null ? $regular->getName() : '?') . ' → ' . ($xeo !== null ? $xeo->getName() : '?');
    }

    protected function getMessage(): string
    {
        return $this->translator->translate('Mapping "{name}" will be permanently deleted.', [
            'name' => $this->getModelName(),
        ], 'dboard-modules');
    }
}
