<?php

use app\library\AuthUi;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IcdoTopography $model */
?>

<?php $form = ActiveForm::begin(['id' => 'icdo-topography-form']); ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

    <div>
        <?= $form->field($model, 'code')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. C50.9',
        ])->hint('ICD-O-3 topography code. Codes ending in .9 are offered as primary sites.') ?>
    </div>

    <div>
        <?= $form->field($model, 'redcap_value')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
        ])->hint('Choice value for primary_site_topography in the REDCap dictionary.') ?>
    </div>

    <div class="md:col-span-2">
        <?= $form->field($model, 'description')->textInput([
            'class' => AuthUi::inputClass(),
            'placeholder' => 'e.g. Breast, NOS',
        ]) ?>
    </div>

    <div>
        <?= $form->field($model, 'source_version')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. REDCap dictionary',
        ]) ?>
    </div>

    <div class="flex items-center">
        <?= $form->field($model, 'is_active')->checkbox(AuthUi::checkboxConfig('Active (offered on the patient form)')) ?>
    </div>

</div>

<?= $this->render('//setup/_buttons') ?>

<?php ActiveForm::end(); ?>
