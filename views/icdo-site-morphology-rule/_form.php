<?php

use app\library\AuthUi;
use app\models\IcdoMorphology;
use app\models\IcdoSiteMorphologyRule;
use app\models\IcdoTopography;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IcdoSiteMorphologyRule $model */

// Topography suggestions: each category ("C50.*") followed by its exact codes
$topographies = [];
foreach (IcdoTopography::find()->select(['code', 'description'])->where(['is_active' => 1])->orderBy('code')->asArray()->all() as $row) {
    $category = substr($row['code'], 0, 3) . '.*';
    $topographies[$category] ??= 'All ' . $category . ' sites';
    $topographies[$row['code']] = $row['description'];
}

$morphologies = ArrayHelper::map(
    IcdoMorphology::find()->select(['code', 'description'])->where(['is_active' => 1])->orderBy('code')->asArray()->all(),
    'code',
    'description'
);
?>

<?php $form = ActiveForm::begin(['id' => 'icdo-site-morphology-rule-form']); ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

    <div>
        <?= $form->field($model, 'topography_pattern')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'list' => 'rule-topography-options',
            'autocomplete' => 'off',
            'placeholder' => 'e.g. C50.* or C50.9',
        ])->hint('A category (C50.*) covers all its codes; an exact code (C50.9) covers just that code.') ?>
        <datalist id="rule-topography-options">
            <?php foreach ($topographies as $value => $label): ?>
                <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>

    <div>
        <?= $form->field($model, 'morphology_code')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'list' => 'rule-morphology-options',
            'autocomplete' => 'off',
            'placeholder' => 'Type code or name, e.g. 8140',
        ]) ?>
        <datalist id="rule-morphology-options">
            <?php foreach ($morphologies as $code => $description): ?>
                <option value="<?= Html::encode($code) ?>"><?= Html::encode($description) ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>

    <div>
        <?= $form->field($model, 'rule_type')->dropDownList(IcdoSiteMorphologyRule::RULE_TYPES, [
            'class' => AuthUi::inputClass(),
            'showKeys' => false,
        ])->hint('Common: listed first in the histology dropdown. Allowed: accepted without a review warning.') ?>
    </div>

    <div>
        <?= $form->field($model, 'source_version')->textInput([
            'class' => AuthUi::inputClass(),
            'maxlength' => true,
            'placeholder' => 'e.g. starter-common-v1',
        ]) ?>
    </div>

    <div class="md:col-span-2">
        <?= $form->field($model, 'note')->textarea([
            'class' => AuthUi::inputClass(),
            'rows' => 3,
            'placeholder' => 'Source or reasoning for this rule (optional)',
        ]) ?>
    </div>

</div>

<?= $this->render('//setup/_buttons') ?>

<?php ActiveForm::end(); ?>
