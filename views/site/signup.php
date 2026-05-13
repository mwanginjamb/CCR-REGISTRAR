<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use app\library\FormUi;

$this->title = 'Register | Create an Account';
?>

<div class="row">


<div class="form-container">

            <?php $form = ActiveForm::begin(\app\library\FormUi::formConfig('signup-form')); ?>

            <!-- Add an error summary -->
            <?= $form->errorSummary($model) ?>

            <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'placeholder' => 'Your Username'
            ])->label('Username') ?>

            <?= $form->field($model, 'email')->textInput([
                'type' => 'email',
                'placeholder' => 'Your E-mail Address'
            ])->label('Email') ?>

            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => '••••••••'
            ]) ?>

            <?= $form->field($model, 'passwordConfirm')->passwordInput([
                'placeholder' => '••••••••'
            ]) ?>


            <div class="mt-2 flex flex-col gap-1">


            </div>

            <?= Html::submitButton('Register to Portal <span class="material-symbols-outlined">arrow_forward</span>', [
                'class' => 'w-full py-4 bg-primary text-white font-headline font-bold rounded-xl shadow-lg shadow-primary/20 hover:bg-primary-container transition-all flex items-center justify-center gap-2',
                'name' => 'signup-button'
            ]) ?>

            <?php ActiveForm::end(); ?>

           

            <div class="mt-12 pt-8 border-t border-surface-container-highest text-center">
                <div class="text-xs text-gray-500">
                    Already have an Account ?
                    <?= Html::a('Sign In', ['site/login'], [
                        'class' => 'font-semibold text-primary hover:underline'
                    ]) ?>
                </div>
</div>
                
    </div>
</div>