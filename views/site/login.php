<?php

/** @var yii\web\View $this */
/** @var yii\widgets\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\AuthUi;
use app\library\FormUi;

$this->title = 'Registrar Login';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-login">


    <p class="text-center">Please fill out the following fields to login:</p>

    <div class="row">
        <div class="form-container">

            <?php $form = ActiveForm::begin(FormUi::formConfig('login-form')); ?>

            <?= $form->field($model, 'username', FormUi::fieldConfig('person'))->textInput([
                'autofocus' => true,
                'placeholder' => 'Username',
                'class' => FormUi::inputClass(true),
            ]) ?>

            <?= $form->field($model, 'password', FormUi::fieldConfig('lock'))->passwordInput([
                'placeholder' => '••••••••',
                'autocomplete' => false,
                'type' => 'password',
                'class' => FormUi::inputClass(true)
            ]) ?>

            <?= $form->field($model, 'rememberMe', AuthUi::checkboxFieldConfig())->checkbox(AuthUi::checkboxConfig('Remember me')) ?>

            <div class="form-group">
                <div>
                    <?= Html::submitButton('Login', ['class' => FormUi::buttonClass()]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

            <!-- 2 columns layout: forgot password + request access -->
            <div class="flex flex-row gap-4 mt-4 justify-between">
                <div style="color:#999;">
                <?= FormUi::link('Forgot password?', ['site/request-password-reset']) ?>
                </div>
                <div>
                    <?php
                    echo \app\library\FormUi::link('Signup', ['site/signup']);
                    ?>
                </div>
            </div>
           

        </div>
    </div>
</div>