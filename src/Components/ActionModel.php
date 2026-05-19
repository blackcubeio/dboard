<?php

declare(strict_types=1);

/**
 * ActionModel.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Components;

use Blackcube\BridgeModel\BridgeFormModel;
use Blackcube\Dboard\Models\Forms\BridgeFormModel as DboardBridgeFormModel;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Encapsulates the configuration for a model and its associated form model within an action.
 * Provides factory methods for creating model and form model instances.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ActionModel
{
    /**
     * @param string|null $modelClass Fully qualified class name of the ActiveRecord model
     * @param string|null $formModelClass Fully qualified class name of the BridgeFormModel
     * @param string|null $formModelScenario Scenario to apply to the form model
     * @param bool $isMain Whether this is the main model configuration for the action
     */
    public function __construct(
        private ?string $modelClass = null,
        private ?string $formModelClass = null,
        private ?string $formModelScenario = null,
        private bool $isMain = false,
        private ?TranslatorInterface $translator = null,
    ) {
    }

    /**
     * Checks whether this is the main model configuration.
     *
     * @return bool True if this is the main model
     */
    public function isMain(): bool
    {
        return $this->isMain;
    }

    /**
     * Checks whether a model class is configured.
     *
     * @return bool True if a model class is defined
     */
    public function hasModel(): bool
    {
        return $this->modelClass !== null;
    }

    /**
     * Checks whether a form model class is configured.
     *
     * @return bool True if a form model class is defined
     */
    public function hasFormModel(): bool
    {
        return $this->formModelClass !== null;
    }

    /**
     * Returns an instance of the model.
     * If a primary key is provided, queries the database and returns the matching record.
     * If no record is found or no primary key is provided, returns a new instance.
     * If no model class is configured, returns null.
     *
     * @param array<string, mixed>|null $pkey Primary key conditions for the query (e.g., ['id' => 1])
     * @return ActiveRecord|null The model instance or null if no model class is configured
     */
    public function getModel(?array $pkey = null): ?ActiveRecord
    {
        if ($this->modelClass === null) {
            return null;
        }

        $modelClass = $this->modelClass;

        if ($pkey !== null) {
            $model = $modelClass::query()
                ->andWhere($pkey)
                ->one();
            if ($model !== null) {
                return $model;
            }
        }

        return new $modelClass();
    }

    /**
     * Returns an instance of the form model.
     * If an ActiveRecord model is provided, initializes the form model from it.
     * If a scenario is configured, applies it to the form model.
     * If no form model class is configured, returns null.
     *
     * @param ActiveRecord|null $model The ActiveRecord model to initialize from
     * @return BridgeFormModel|null The form model instance or null if no form model class is configured
     */
    public function getFormModel(?ActiveRecord $model = null): ?BridgeFormModel
    {
        if ($this->formModelClass === null) {
            return null;
        }

        $formModelClass = $this->formModelClass;

        if ($model !== null && is_subclass_of($formModelClass, DboardBridgeFormModel::class)) {
            $formModel = $formModelClass::createFromModel($model, $this->translator);
        } elseif ($model !== null) {
            $formModel = $formModelClass::createFromModel($model);
        } elseif (is_subclass_of($formModelClass, DboardBridgeFormModel::class)) {
            $formModel = new $formModelClass(translator: $this->translator);
        } else {
            $formModel = new $formModelClass();
        }

        if ($this->formModelScenario !== null) {
            $formModel->setScenario($this->formModelScenario);
        }

        return $formModel;
    }
}
