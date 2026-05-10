<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Patients'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="patient-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'full_name',
            'national_id',
            'telephone_no_patient',
            'telephone_no_nok',
            'age',
            'date_of_birth',
            'place_of_birth',
            'ethnic_group',
            'religion',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ],
    ]) ?>


<!-- map pin -->
 <?php if ($model->geo_lat && $model->geo_lng): ?>

<section class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(0,26,72,0.04)]">

    <div class="flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined text-primary">location_on</span>
        <h3 class="text-base font-bold text-primary">Registration Location</h3>
    </div>

    <div class="flex flex-wrap gap-4 text-xs text-on-surface-variant mb-4">
        <span><span class="font-semibold text-primary">Lat</span> <?= $model->geo_lat ?></span>
        <span><span class="font-semibold text-primary">Lng</span> <?= $model->geo_lng ?></span>
        <?php if ($model->geo_accuracy): ?>
        <span><span class="font-semibold text-primary">Accuracy</span> ±<?= round($model->geo_accuracy) ?>m</span>
        <?php endif; ?>
        <?php if ($model->geo_captured): ?>
        <span><span class="font-semibold text-primary">Captured</span> <?= Yii::$app->formatter->asDatetime($model->geo_captured) ?></span>
        <?php endif; ?>
    </div>

    <div id="patient-map" class="w-full rounded-xl overflow-hidden" style="height:280px"></div>

</section>

<?php

// 1. Leaflet CSS/JS (CDN, scoped to this view only)
$this->registerCssFile(
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css',
    ['position' => \yii\web\View::POS_HEAD]
);

$this->registerJsFile(
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js',
    ['position' => \yii\web\View::POS_END]
);

// 2. Inline config block — must run BEFORE patientMap.js
//    json_encode handles escaping, so XSS-safe
$this->registerJs(
    'window.PatientMapConfig = ' . json_encode([
        'lat'      => (float) $model->geo_lat,
        'lng'      => (float) $model->geo_lng,
        'label'    => $model->full_name,
        'accuracy' => (float) $model->geo_accuracy,
    ]) . ';',
    \yii\web\View::POS_END   // runs before patientMap.js below
);

// 3. External map script — depends on Leaflet being loaded first
$this->registerJsFile(
    '@web/js/patientMap.js',
    ['position' => \yii\web\View::POS_END]
);

?>

<?php endif; ?>

</div>
