<?php

declare(strict_types=1);

/**
 * SchemaEditor.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Blackcube\Bleet\Widgets\AbstractWidget;
use Yiisoft\Html\Html;

/**
 * SchemaEditor widget - JSON Schema editor using JSONEditor
 *
 * Usage:
 *   Dboard::schemaEditor()->active($model, 'schema')->language('fr')->render()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SchemaEditor extends AbstractWidget
{
    use BleetModelAwareTrait;

    private ?string $name = null;
    private ?string $id = null;
    private ?string $schema = null;
    private string $language = 'en';
    private bool $disabled = false;

    /**
     * Sets the name
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the id
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    /**
     * Sets the schema value
     */
    public function schema(string $schema): self
    {
        $new = clone $this;
        $new->schema = $schema;
        return $new;
    }

    /**
     * Sets the language for JSONEditor
     */
    public function language(string $language): self
    {
        $new = clone $this;
        $new->language = $language;
        return $new;
    }

    /**
     * Disables the editor (read-only mode)
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    public function render(): string
    {
        $name = $this->name ?? $this->getInputName();
        $id = $this->id ?? $this->getInputId();
        $schema = $this->schema ?? $this->getValue() ?? '{}';

        $aureliaAttributes = [
            'fieldName' => $name,
            'fieldId' => $id,
            'schema' => $schema,
            'language' => $this->language,
            'class' => 'dboard-schema-editor-' . $this->color,
        ];
        if ($this->disabled) {
            $aureliaAttributes['disabled'] = true;
        }
        $attributes = Aurelia::attributesCustomElement($aureliaAttributes);

        return Html::tag('dboard-schema-editor', '')
            ->attributes($attributes)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }
}
