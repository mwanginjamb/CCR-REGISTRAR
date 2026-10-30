<?php

use app\library\AuthUi;
use app\models\Sources;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SourceReference $model */
/** @var yii\widgets\ActiveForm $form */
?>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-3xl">

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

    <div class="flex items-center justify-end gap-3 mt-8">
        <?= Html::a('Cancel', ['index'], [
            'class' => 'px-6 py-3 rounded-xl bg-surface-container-high text-outline font-bold hover:bg-surface-dim transition-colors',
        ]) ?>
        <?= Html::submitButton(Yii::t('app', 'Save'), [
            'class' => 'px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-[#002d72] text-white font-bold hover:opacity-90 transition-opacity shadow-md',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</section>
