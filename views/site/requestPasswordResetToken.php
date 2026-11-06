<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\PasswordResetRequestForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\FormUi;

$this->title = 'Reset Your Password';
$this->params['card_subtitle'] = 'Enter your email and we will send you a link to reset your password.';
?>
<div class="site-request-password-reset">

    <?php $form = ActiveForm::begin(FormUi::formConfig('request-password-reset-form')); ?>

    <?= $form->field($model, 'email', FormUi::fieldConfig('mail'))->textInput([
        'autofocus' => true,
        'type' => 'email',
        'placeholder' => 'you@example.org',
        'autocomplete' => 'email',
        'class' => FormUi::inputClass(true),
    ]) ?>

    <?= Html::submitButton('Send Reset Link <span class="material-symbols-outlined">arrow_forward</span>', [
        'class' => FormUi::buttonClass(),
        'name' => 'reset-button',
    ]) ?>

    <?php ActiveForm::end(); ?>

    <?= $this->render('_authLinks', ['links' => [
        ['Back to login', ['site/login']],
        ['Resend verification email', ['site/resend-verification-email']],
    ]]) ?>

</div>
