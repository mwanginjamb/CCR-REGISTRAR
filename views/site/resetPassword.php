<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ResetPasswordForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\FormUi;

$this->title = 'Choose a New Password';
$this->params['card_subtitle'] = 'Enter the new password for your account.';
?>
<div class="site-reset-password">

    <?php $form = ActiveForm::begin(FormUi::formConfig('reset-password-form')); ?>

    <?= $form->field($model, 'password', FormUi::fieldConfig('lock'))->passwordInput([
        'autofocus' => true,
        'placeholder' => '••••••••',
        'autocomplete' => 'new-password',
        'class' => FormUi::inputClass(true),
    ])->label('New Password') ?>

    <?= Html::submitButton('Save Password <span class="material-symbols-outlined">check</span>', [
        'class' => FormUi::buttonClass(),
        'name' => 'save-button',
    ]) ?>

    <?php ActiveForm::end(); ?>

    <?= $this->render('_authLinks', ['links' => [
        ['Back to login', ['site/login']],
    ]]) ?>

</div>
