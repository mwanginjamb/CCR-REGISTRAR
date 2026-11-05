<?php

use app\library\AuthUi;
use app\models\Tumour;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IcdoMorphology $model */
?>

<?php $form = ActiveForm::begin(['id' => 'icdo-morphology-form']); ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

    <div>
        <?= $form->field($model, 'code')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. 8140',
        ])->hint('4-digit ICD-O-3 morphology code.') ?>
    </div>

    <div>
        <?= $form->field($model, 'behaviour_code')->dropDownList(Tumour::getBehaviour(), [
            'class' => AuthUi::inputClass(),
            'prompt' => 'Not set',
        ])->hint('When set, selecting this histology fills the tumour\'s behaviour.') ?>
    </div>

    <div class="md:col-span-2">
        <?= $form->field($model, 'description')->textInput([
            'class' => AuthUi::inputClass(),
            'placeholder' => 'e.g. Adenocarcinoma, NOS',
        ]) ?>
    </div>

    <div>
        <?= $form->field($model, 'redcap_value')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
        ])->hint('Choice value for histology_morphology in the REDCap dictionary (usually the code).') ?>
    </div>

    <div>
        <?= $form->field($model, 'source_version')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. REDCap dictionary',
        ]) ?>
    </div>

    <div class="md:col-span-2">
        <?= $form->field($model, 'is_active')->checkbox(AuthUi::checkboxConfig('Active (offered on the patient form)')) ?>
    </div>

</div>

<?= $this->render('//setup/_buttons') ?>

<?php ActiveForm::end(); ?>
