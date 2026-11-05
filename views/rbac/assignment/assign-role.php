<?php

use app\library\FormUi;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\UserRoleForm $model
 * @var app\models\User $user
 * @var yii\rbac\Role[] $allRoles
 */

echo $this->render('../_header', [
    'title' => 'Assign Roles: ' . $user->username,
    'section' => 'User Roles',
    'subtitle' => $user->email,
]);
?>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-3xl">

    <?php $form = ActiveForm::begin(['id' => 'assign-role-form']); ?>

    <?= $form->field($model, 'roles')->checkboxList(
        ArrayHelper::map($allRoles, 'name', 'name'),
        [
            'class' => 'grid grid-cols-1 gap-3',
            'item' => FormUi::checkboxCard(ArrayHelper::map($allRoles, 'name', 'description')),
            'unselect' => '',
        ]
    )->hint('Roles include the roles they inherit: qa includes registrar, admin includes qa.') ?>

    <?= $this->render('//setup/_buttons', ['cancelUrl' => ['user-roles']]) ?>

    <?php ActiveForm::end(); ?>

</section>
