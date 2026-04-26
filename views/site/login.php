<?php

/** @var yii\web\View $this */
/** @var yii\widgets\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\library\AuthUi;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <div class="row">
        <div class="form-container">

            <?php $form = ActiveForm::begin(AuthUi::formConfig('login-form')); ?>

            <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'class' => AuthUi::inputClass(),
            ]) ?>

            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Password',
                'autocomplete' => false,
                'type' => 'password',
                'class' => AuthUi::inputClass()
            ]) ?>

            <?= $form->field($model, 'rememberMe', AuthUi::checkboxFieldConfig())->checkbox(AuthUi::checkboxConfig('Remember me')) ?>

            <div class="form-group">
                <div>
                    <?= Html::submitButton('Login', ['class' => AuthUi::buttonClass()]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

            <div style="color:#999;">
               <?= Html::a(
    'Forgot password?',
    ['site/request-password-reset'],
    [
        'class' => AuthUi::linkClass(),
    ]
) ?>
            </div>

        </div>
    </div>
</div>
