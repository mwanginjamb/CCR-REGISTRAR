<?php

use app\library\FormUi;
use yii\helpers\Html;

/**
 * Shared create/update page for Settings controllers (app\components\SetupController).
 * Renders the controller's own _form.php inside a common card.
 *
 * @var yii\web\View $this
 * @var yii\db\ActiveRecord $model
 * @var string $title
 */

/** @var app\components\SetupController $controller */
$controller = $this->context;
$listLabel = $controller->label . 's';

$this->title = $title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = ['label' => $listLabel, 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->isNewRecord ? Yii::t('app', 'New') : Yii::t('app', 'Edit');
?>
<div class="mb-8">
    <?= FormUi::breadcrumb(['Settings', $listLabel, $model->isNewRecord ? 'New' : 'Edit']) ?>
    <h2 class="text-2xl md:text-3xl font-headline font-extrabold text-primary tracking-tight"><?= Html::encode($this->title) ?></h2>
</div>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-3xl">
    <?= $this->render('//' . $controller->id . '/_form', ['model' => $model]) ?>
</section>
