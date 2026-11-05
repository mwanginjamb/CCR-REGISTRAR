<?php

namespace app\models;

use app\components\Rbac;
use Yii;
use yii\base\Model;

/**
 * Create / update form for an RBAC permission.
 *
 * $parent is an optional permission that includes this one (a permission group).
 */
class PermissionForm extends Model
{
    public $name;
    public $description;
    public $parent;

    /** Permission name before editing (update scenario) */
    public $originalName;

    public function scenarios()
    {
        $attributes = ['name', 'description', 'parent'];

        return [
            'create' => $attributes,
            'update' => $attributes,
        ];
    }

    public function rules()
    {
        return [
            [['name', 'description'], 'trim'],
            ['name', 'required'],
            ['name', 'match', 'pattern' => '/^[a-zA-Z][a-zA-Z0-9_.-]{1,63}$/', 'message' => 'Use letters, digits, "_", "." or "-", starting with a letter.'],
            ['name', 'validateUniqueName'],
            ['name', 'validateCoreRename', 'on' => 'update'],
            ['description', 'string', 'max' => 255],
            ['parent', 'default', 'value' => null],
            ['parent', 'in', 'range' => fn() => array_keys(Yii::$app->authManager->getPermissions())],
            ['parent', 'compare', 'compareAttribute' => 'name', 'operator' => '!=', 'message' => 'A permission cannot be its own parent.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Permission Name',
            'description' => 'Description',
            'parent' => 'Parent Permission',
        ];
    }

    // Role and permission names share one namespace in the auth tables
    public function validateUniqueName($attribute)
    {
        if ($this->name === $this->originalName) {
            return;
        }

        $auth = Yii::$app->authManager;
        if ($auth->getRole($this->name) || $auth->getPermission($this->name)) {
            $this->addError($attribute, "A role or permission named \"{$this->name}\" already exists.");
        }
    }

    public function validateCoreRename($attribute)
    {
        if ($this->name !== $this->originalName && Rbac::isCoreItem((string) $this->originalName)) {
            $this->addError($attribute, "\"{$this->originalName}\" is used by the application and cannot be renamed.");
        }
    }
}
