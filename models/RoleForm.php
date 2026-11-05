<?php

namespace app\models;

use app\components\Rbac;
use Yii;
use yii\base\Model;

/**
 * Create / update form for an RBAC role.
 *
 * $permissions are the role's own (direct) permissions; $childRoles are the
 * roles it inherits from (e.g. qa inherits registrar).
 */
class RoleForm extends Model
{
    public $role_name;
    public $description;
    public $permissions = [];
    public $childRoles = [];

    /** Role name before editing (update scenario) */
    public $originalName;

    public function scenarios()
    {
        $attributes = ['role_name', 'description', 'permissions', 'childRoles'];

        return [
            'create' => $attributes,
            'update' => $attributes,
        ];
    }

    public function rules()
    {
        return [
            ['role_name', 'trim'],
            ['role_name', 'required'],
            ['role_name', 'match', 'pattern' => '/^[a-zA-Z][a-zA-Z0-9_-]{1,63}$/', 'message' => 'Use letters, digits, "_" or "-", starting with a letter.'],
            ['role_name', 'validateUniqueName'],
            ['role_name', 'validateCoreRename', 'on' => 'update'],
            ['description', 'string', 'max' => 255],
            [['permissions', 'childRoles'], 'default', 'value' => []],
            ['permissions', 'each', 'rule' => ['in', 'range' => fn() => array_keys(Yii::$app->authManager->getPermissions())]],
            ['childRoles', 'each', 'rule' => ['in', 'range' => fn() => array_keys(Yii::$app->authManager->getRoles())]],
            ['childRoles', 'validateNoCycle'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'role_name' => 'Role Name',
            'description' => 'Description',
            'permissions' => 'Permissions',
            'childRoles' => 'Inherits From',
        ];
    }

    // Role and permission names share one namespace in the auth tables
    public function validateUniqueName($attribute)
    {
        if ($this->role_name === $this->originalName) {
            return;
        }

        $auth = Yii::$app->authManager;
        if ($auth->getRole($this->role_name) || $auth->getPermission($this->role_name)) {
            $this->addError($attribute, "A role or permission named \"{$this->role_name}\" already exists.");
        }
    }

    public function validateCoreRename($attribute)
    {
        if ($this->role_name !== $this->originalName && Rbac::isCoreItem((string) $this->originalName)) {
            $this->addError($attribute, "\"{$this->originalName}\" is a built-in role and cannot be renamed.");
        }
    }

    // A role cannot inherit itself, directly or through another role
    public function validateNoCycle($attribute)
    {
        $self = $this->originalName ?: $this->role_name;
        $auth = Yii::$app->authManager;

        foreach ((array) $this->childRoles as $child) {
            if ($child === $self || isset($auth->getChildRoles($child)[$self])) {
                $this->addError($attribute, "\"{$self}\" cannot inherit \"{$child}\": that would create a loop.");
            }
        }
    }
}
