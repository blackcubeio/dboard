<?php

declare(strict_types=1);

/**
 * PermissionForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\InRange;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Result;

/**
 * Permission assignment form model.
 * Handles role/permission toggle for administrator assignments.
 */
final class PermissionForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $itemType = '';
    protected string $itemName = '';
    protected bool $checked = false;

    private ?ItemsStorageInterface $itemsStorage = null;

    public function load(mixed $data, ?string $scope = null): bool
    {
        $this->checked = false;
        return parent::load($data, $scope);
    }

    #[Bridge]
    public function setItemType(string $itemType): void
    {
        $this->itemType = $itemType;
    }

    #[Bridge]
    public function getItemType(): string
    {
        return $this->itemType;
    }

    #[Bridge]
    public function setItemName(string $itemName): void
    {
        $this->itemName = $itemName;
    }

    #[Bridge]
    public function getItemName(): string
    {
        return $this->itemName;
    }

    #[Bridge]
    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }

    #[Bridge]
    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function setItemsStorage(ItemsStorageInterface $itemsStorage): void
    {
        $this->itemsStorage = $itemsStorage;
    }

    /**
     * Creates form from request body.
     * Parses body format: role[ROLE_NAME] => '1' or permission[PERMISSION_NAME] => '0'
     *
     * @param array|null $body
     * @return self|null Returns null if body format is invalid
     */
    public static function fromRequestBody(?array $body): ?self
    {
        if (!is_array($body)) {
            return null;
        }

        foreach ($body as $key => $value) {
            if ($key === 'role' && is_array($value)) {
                $itemName = array_key_first($value);
                if ($itemName === null) {
                    return null;
                }
                $form = new self();
                $form->setItemType('role');
                $form->setItemName($itemName);
                $form->setChecked($value[$itemName] === '1');
                return $form;
            }
            if ($key === 'permission' && is_array($value)) {
                $itemName = array_key_first($value);
                if ($itemName === null) {
                    return null;
                }
                $form = new self();
                $form->setItemType('permission');
                $form->setItemName($itemName);
                $form->setChecked($value[$itemName] === '1');
                return $form;
            }
        }

        return null;
    }

    public function scenarios(): array
    {
        return [
            'default' => ['itemType', 'itemName', 'checked'],
        ];
    }

    public function rules(): array
    {
        return [
            'itemType' => [
                new Required(),
                new InRange(['role', 'permission']),
            ],
            'itemName' => [
                new Required(),
                new Callback(
                    callback: function (mixed $value): Result {
                        $result = new Result();
                        if ($this->itemsStorage === null) {
                            return $result;
                        }
                        $item = $this->itemType === 'role'
                            ? $this->itemsStorage->getRole($value)
                            : $this->itemsStorage->getPermission($value);
                        if ($item === null) {
                            $result->addError('The RBAC item does not exist.');
                        }
                        return $result;
                    }
                ),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'itemType' => 'Type',
            'itemName' => 'Name',
            'checked' => 'Active',
        ];
    }
}
