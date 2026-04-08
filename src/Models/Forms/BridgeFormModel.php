<?php

declare(strict_types=1);

/**
 * BridgeFormModel.php
 *
 * PHP Version 8.2
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Yiisoft\Translator\TranslatorInterface;

/**
 * Abstract form model for bridging data between models
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class BridgeFormModel extends \Blackcube\BridgeModel\BridgeFormModel
{
    protected ?string $translateCategory = null;
    protected ?string $translateElasticCategory = null;

    private ?array $translatedLabels = null;
    private ?array $translatedHints = null;
    private ?array $translatedPlaceholders = null;

    public function __construct(
        protected ?TranslatorInterface $translator = null,
    ) {
    }

    public static function createFromModel(object $model, ?TranslatorInterface $translator = null): static
    {
        $formModel = new static(translator: $translator);
        $formModel->initFromModel($model);
        return $formModel;
    }

    protected function getRawLabels(): array
    {
        return [];
    }

    protected function getRawHints(): array
    {
        return [];
    }

    protected function getRawPlaceholders(): array
    {
        return [];
    }

    public function getPropertyLabels(): array
    {
        if ($this->translatedLabels === null) {
            $raws = $this->translateCategory !== null ? $this->translateValues($this->getRawLabels(), $this->translateCategory) : $this->getRawLabels();
            $elastics = $this->translateElasticCategory !== null ? $this->translateValues($this->getElasticPropertyLabels(), $this->translateElasticCategory) : $this->getElasticPropertyLabels();
            $this->translatedLabels = array_merge($raws, $elastics);
        }
        return $this->translatedLabels;
    }

    public function getPropertyHints(): array
    {
        if ($this->translatedHints === null) {
            $raws = $this->translateCategory !== null ? $this->translateValues($this->getRawHints(), $this->translateCategory) : $this->getRawHints();
            $elastics = $this->translateElasticCategory !== null ? $this->translateValues($this->getElasticPropertyHints(), $this->translateElasticCategory) : $this->getElasticPropertyHints();
            $this->translatedHints = array_merge($raws, $elastics);
        }
        return $this->translatedHints;
    }

    public function getPropertyPlaceholders(): array
    {
        if ($this->translatedPlaceholders === null) {
            $raws = $this->translateCategory !== null ? $this->translateValues($this->getRawPlaceholders(), $this->translateCategory) : $this->getRawPlaceholders();
            $elastics = $this->translateElasticCategory !== null ? $this->translateValues($this->getElasticPropertyPlaceholders(), $this->translateElasticCategory) : $this->getElasticPropertyPlaceholders();
            $this->translatedPlaceholders = array_merge($raws, $elastics);
        }
        return $this->translatedPlaceholders;
    }

    public function getPropertyLabel(string $property): string
    {
        $labels = $this->getPropertyLabels();
        return $labels[$property] ?? parent::getPropertyLabel($property);
    }

    public function getPropertyHint(string $property): string
    {
        $hints = $this->getPropertyHints();
        return $hints[$property] ?? parent::getPropertyHint($property);
    }

    public function getPropertyPlaceholder(string $property): string
    {
        $placeholders = $this->getPropertyPlaceholders();
        return $placeholders[$property] ?? parent::getPropertyPlaceholder($property);
    }

    private function translateValues(array $values, string $category): array
    {
        $translated = [];
        foreach ($values as $prop => $value) {
            $translated[$prop] = $this->translator?->translate($value, category: $category) ?? $value;
        }
        return $translated;
    }
}
