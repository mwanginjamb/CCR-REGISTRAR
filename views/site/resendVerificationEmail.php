<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ResendVerificationEmailForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\FormUi;

$this->title = 'Resend Verification Email';
$this->params['card_subtitle'] = 'Enter the email you signed up with and we will send a new verification link.';
?>
<div class="site-resend-verification-email">

    <?php $form = ActiveForm::begin(FormUi::formConfig('resend-verification-email-form')); ?>

    <?= $form->field($model, 'email', FormUi::fieldConfig('mail'))->textInput([
        'autofocus' => true,
        'type' => 'email',
        'placeholder' => 'you@example.org',
        'autocomplete' => 'email',
        'class' => FormUi::inputClass(true),
    ]) ?>

    <?= Html::submitButton('Send Verification Email <span class="material-symbols-outlined">arrow_forward</span>', [
        'class' => FormUi::buttonClass(),
        'name' => 'send-button',
    ]) ?>

    <?php ActiveForm::end(); ?>

    <?= $this->render('_authLinks', ['links' => [
        ['Back to login', ['site/login']],
    ]]) ?>

</div>
