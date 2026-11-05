<?php

use app\components\Rbac;
use app\library\AuthUi;
use app\library\FormUi;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Shared role form (create-role / update-role).
 *
 * @var yii\web\View $this
 * @var app\models\RoleForm $model
 * @var yii\rbac\Permission[] $permissions
 * @var yii\rbac\Role[] $roles
 */

$isCore = $model->originalName && Rbac::isCoreItem($model->originalName);

// A role cannot inherit itself
$inheritable = array_filter($roles, fn($role) => $role->name !== $model->originalName);
?>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-4xl">

    <?php $form = ActiveForm::begin(['id' => 'role-form']); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
        <div>
            <?= $form->field($model, 'role_name')->textInput([
                'class' => AuthUi::inputClass(),
                'maxlength' => 64,
                'readonly' => $isCore,
                'placeholder' => 'e.g. dataManager',
            ])->hint($isCore ? 'Built-in role: the application checks this name, so it cannot be renamed.' : null) ?>
        </div>
        <div>
            <?= $form->field($model, 'description')->textInput([
                'class' => AuthUi::inputClass(),
                'maxlength' => 255,
            ]) ?>
        </div>
    </div>

    <div class="mt-6">
        <?= $form->field($model, 'childRoles')->checkboxList(
            ArrayHelper::map($inheritable, 'name', 'name'),
            [
                'class' => 'grid grid-cols-1 md:grid-cols-3 gap-3',
                'item' => FormUi::checkboxCard(ArrayHelper::map($inheritable, 'name', 'description')),
                'unselect' => '',
            ]
        )->hint('The role also gets every permission of the roles ticked here.') ?>
    </div>

    <div class="mt-6">
        <?= $form->field($model, 'permissions')->checkboxList(
            ArrayHelper::map($permissions, 'name', 'name'),
            [
                'class' => 'grid grid-cols-1 md:grid-cols-2 gap-3',
                'item' => FormUi::checkboxCard(ArrayHelper::map($permissions, 'name', 'description')),
                'unselect' => '',
            ]
        )->hint('Permissions granted directly by this role. Inherited permissions are not listed here.') ?>
    </div>

    <?= $this->render('//setup/_buttons') ?>

    <?php ActiveForm::end(); ?>

</section>
