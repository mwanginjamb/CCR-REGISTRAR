<?php

use app\components\Rbac;
use app\library\AuthUi;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Shared permission form (create-permission / update-permission).
 *
 * @var yii\web\View $this
 * @var app\models\PermissionForm $model
 * @var yii\rbac\Permission[] $existingPermissions
 */

$isCore = $model->originalName && Rbac::isCoreItem($model->originalName);
$parents = ArrayHelper::map(
    array_filter($existingPermissions, fn($p) => $p->name !== $model->originalName),
    'name',
    'name'
);
?>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-3xl">

    <?php $form = ActiveForm::begin(['id' => 'permission-form']); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
        <div>
            <?= $form->field($model, 'name')->textInput([
                'class' => AuthUi::inputClass(),
                'maxlength' => 64,
                'readonly' => $isCore,
                'placeholder' => 'e.g. exportReports',
            ])->hint($isCore ? 'Built-in permission: the application checks this name, so it cannot be renamed.' : null) ?>
        </div>
        <div>
            <?= $form->field($model, 'parent')->dropDownList($parents, [
                'class' => AuthUi::inputClass(),
                'prompt' => 'None',
                'showKeys' => false,
            ])->hint('Optional: a permission that includes this one.') ?>
        </div>
        <div class="md:col-span-2">
            <?= $form->field($model, 'description')->textInput([
                'class' => AuthUi::inputClass(),
                'maxlength' => 255,
            ]) ?>
        </div>
    </div>

    <?= $this->render('//setup/_buttons', ['cancelUrl' => ['permissions']]) ?>

    <?php ActiveForm::end(); ?>

</section>
