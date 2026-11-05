<?php

use app\library\AuthUi;
use app\models\IcdoTopography;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IcdoSiteRule $model */

// Category suggestions: "C50.*" labelled with the category's primary site name
$categories = [];
foreach (IcdoTopography::primarySiteOptions() as $code => $description) {
    $categories[substr($code, 0, 3) . '.*'] ??= $description;
}
?>

<?php $form = ActiveForm::begin(['id' => 'icdo-site-rule-form']); ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

    <div class="md:col-span-2">
        <?= $form->field($model, 'topography_pattern')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'list' => 'topography-category-options',
            'autocomplete' => 'off',
            'placeholder' => 'e.g. C50.*',
        ])->hint('Applies to every code in the category, e.g. C50.* covers C50.0 to C50.9.') ?>
        <datalist id="topography-category-options">
            <?php foreach ($categories as $pattern => $description): ?>
                <option value="<?= Html::encode($pattern) ?>"><?= Html::encode($description) ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>

    <?php // ml-5 lines the hint up with the label text (w-4 checkbox + a space) ?>
    <div>
        <?= $form->field($model, 'laterality_allowed')
            ->checkbox(AuthUi::checkboxConfig('Laterality allowed'))
            ->hint('Unticked: the form offers only "Not Applicable".', ['class' => 'text-xs text-on-surface-variant mt-1 ml-5']) ?>
    </div>

    <div>
        <?= $form->field($model, 'laterality_required')
            ->checkbox(AuthUi::checkboxConfig('Laterality required'))
            ->hint('Saving a tumour without laterality fails for this site.', ['class' => 'text-xs text-on-surface-variant mt-1 ml-5']) ?>
    </div>

    <div>
        <?= $form->field($model, 'tnm_allowed')->checkbox(AuthUi::checkboxConfig('TNM allowed')) ?>
    </div>

    <div>
        <?= $form->field($model, 'grade_allowed')->checkbox(AuthUi::checkboxConfig('Grade allowed')) ?>
    </div>

</div>

<p class="text-xs text-outline mt-4">TNM and grade flags are stored for future use; the patient form does not read them yet.</p>

<?= $this->render('//setup/_buttons') ?>

<?php ActiveForm::end(); ?>
