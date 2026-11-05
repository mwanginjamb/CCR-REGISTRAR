<?php

/** @var yii\web\View $this */
/** @var app\models\RoleForm $model */
/** @var yii\rbac\Permission[] $permissions */
/** @var yii\rbac\Role[] $roles */

echo $this->render('_header', ['title' => 'Edit Role: ' . $model->originalName, 'section' => 'Roles']);
echo $this->render('_role_form', ['model' => $model, 'permissions' => $permissions, 'roles' => $roles]);
