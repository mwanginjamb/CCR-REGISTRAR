<?php

/** @var yii\web\View $this */
/** @var app\models\PermissionForm $model */
/** @var yii\rbac\Permission[] $existingPermissions */

echo $this->render('../_header', ['title' => 'New Permission', 'section' => 'Permissions']);
echo $this->render('_form', ['model' => $model, 'existingPermissions' => $existingPermissions]);
