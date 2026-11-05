<?php

namespace app\models;

use app\components\Rbac;
use Yii;
use yii\base\Model;

/**
 * Assigns RBAC roles to a user.
 */
class UserRoleForm extends Model
{
    public $userId;
    public $roles = [];

    public function rules()
    {
        return [
            ['roles', 'default', 'value' => []],
            ['roles', 'each', 'rule' => ['in', 'range' => fn() => array_keys(Yii::$app->authManager->getRoles())]],
            ['roles', 'validateOwnAdmin'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'roles' => 'Roles',
        ];
    }

    // Stops an admin from removing their own admin role and locking everyone out
    public function validateOwnAdmin($attribute)
    {
        $isSelf = !Yii::$app->user->isGuest && (int) Yii::$app->user->id === (int) $this->userId;

        if ($isSelf && !in_array(Rbac::ROLE_ADMIN, (array) $this->roles, true)) {
            $this->addError($attribute, 'You cannot remove the admin role from your own account.');
        }
    }
}
