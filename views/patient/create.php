<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */

$this->title = Yii::t('app', 'Create Patient');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Patients'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelTumour' => $modelTumour,
    ]) ?>


