<?php

use app\library\AuthUi;
use app\models\Sources;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SourceReference $model */
?>

<?php $form = ActiveForm::begin(['id' => 'source-reference-form']); ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

    <div>
        <?= $form->field($model, 'source_type')->dropDownList(Sources::getSourceTypeOptions(), [
            'class' => AuthUi::inputClass(),
            'prompt' => 'Select Source Category',
        ]) ?>
    </div>

    <div>
        <?= $form->field($model, 'source_no')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. 02',
        ])->hint('Code shown on the CRF, unique within the category.') ?>
    </div>

    <div class="md:col-span-2">
        <?= $form->field($model, 'name')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. KENYATTA NATIONAL HOSPITAL',
        ]) ?>
    </div>

    <div>
        <?= $form->field($model, 'redcap_value')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
        ])->hint('Choice value in the REDCap dictionary, used for exports. Leave empty if none.') ?>
    </div>

    <div class="flex items-center">
        <?= $form->field($model, 'is_active')->checkbox(AuthUi::checkboxConfig('Active (suggested on the patient form)')) ?>
    </div>

</div>

<?= $this->render('//setup/_buttons') ?>

<?php ActiveForm::end(); ?>
