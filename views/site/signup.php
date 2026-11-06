<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\FormUi;

$this->title = 'Create an Account';
$this->params['card_subtitle'] = 'Register to request access. You will get an email to verify your address.';
?>
<div class="site-signup">

    <?php $form = ActiveForm::begin(FormUi::formConfig('signup-form')); ?>

    <?= $form->field($model, 'username', FormUi::fieldConfig('person'))->textInput([
        'autofocus' => true,
        'placeholder' => 'Choose a username',
        'class' => FormUi::inputClass(true),
    ])->label('Username') ?>

    <?= $form->field($model, 'email', FormUi::fieldConfig('mail'))->textInput([
        'type' => 'email',
        'placeholder' => 'you@example.org',
        'autocomplete' => 'email',
        'class' => FormUi::inputClass(true),
    ])->label('Email') ?>

    <?= $form->field($model, 'password', FormUi::fieldConfig('lock'))->passwordInput([
        'placeholder' => '••••••••',
        'autocomplete' => 'new-password',
        'class' => FormUi::inputClass(true),
    ]) ?>

    <?= $form->field($model, 'passwordConfirm', FormUi::fieldConfig('lock_reset'))->passwordInput([
        'placeholder' => '••••••••',
        'autocomplete' => 'new-password',
        'class' => FormUi::inputClass(true),
    ]) ?>

    <?= Html::submitButton('Create Account <span class="material-symbols-outlined">arrow_forward</span>', [
        'class' => FormUi::buttonClass(),
        'name' => 'signup-button',
    ]) ?>

    <?php ActiveForm::end(); ?>

    <?= $this->render('_authLinks', ['links' => [
        ['Sign in', ['site/login'], 'Already have an account?'],
    ]]) ?>

</div>
