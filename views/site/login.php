<?php

/** @var yii\web\View $this */
/** @var yii\widgets\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\AuthUi;
use app\library\FormUi;

$this->title = 'Registrar Login';
$this->params['card_subtitle'] = 'Please fill out the following fields to login.';

?>
<div class="site-login">

    <?php $form = ActiveForm::begin(FormUi::formConfig('login-form')); ?>

    <?= $form->field($model, 'username', FormUi::fieldConfig('person'))->textInput([
        'autofocus' => true,
        'placeholder' => 'Username',
        'class' => FormUi::inputClass(true),
    ]) ?>

    <?= $form->field($model, 'password', FormUi::fieldConfig('lock'))->passwordInput([
        'placeholder' => '••••••••',
        'autocomplete' => 'current-password',
        'class' => FormUi::inputClass(true),
    ]) ?>

    <?= $form->field($model, 'rememberMe', AuthUi::checkboxFieldConfig())->checkbox(AuthUi::checkboxConfig('Remember me')) ?>

    <?= Html::submitButton('Login <span class="material-symbols-outlined">login</span>', [
        'class' => FormUi::buttonClass(),
        'name' => 'login-button',
    ]) ?>

    <?php ActiveForm::end(); ?>

    <?= $this->render('_authLinks', ['links' => [
        ['Forgot password?', ['site/request-password-reset']],
        ['Sign up', ['site/signup'], 'No account yet?'],
    ]]) ?>

</div>
