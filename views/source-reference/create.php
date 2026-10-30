<?php

use app\library\FormUi;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SourceReference $model */

$this->title = Yii::t('app', 'New Source');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Sources Setup'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mb-8">
    <?= FormUi::breadcrumb(['Setup', 'Sources', 'New']) ?>
    <h2 class="text-3xl font-headline font-extrabold text-primary tracking-tight"><?= Html::encode($this->title) ?></h2>
</div>

<?= $this->render('_form', [
    'model' => $model,
]) ?>
