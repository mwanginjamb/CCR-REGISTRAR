<?php

use app\library\FormUi;
use app\models\SourceReference;
use app\models\Sources;

/** @var yii\web\View $this */
/** @var app\models\SourceReferenceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$sourceTypes = Sources::getSourceTypeOptions();

echo $this->render('//setup/_index', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'title' => 'Sources',
    'subtitle' => 'Hospitals, labs and hospices suggested for "Source No" on the patient form.',
    'ctaLabel' => 'New Source',
    'ctaIcon' => 'add_business',
    'columns' => [
        [
            'attribute' => 'source_type',
            'filter' => $sourceTypes,
            'content' => static fn(SourceReference $m) => FormUi::chip($sourceTypes[$m->source_type] ?? (string) $m->source_type, 'secondary'),
        ],
        ['attribute' => 'source_no', 'td' => 'primary'],
        'name',
        ['attribute' => 'redcap_value', 'td' => 'muted'],
        [
            'attribute' => 'is_active',
            'filter' => [1 => 'Active', 0 => 'Inactive'],
            'content' => static fn(SourceReference $m) => $m->is_active ? FormUi::badge('Active', 'secondary') : FormUi::badge('Inactive'),
        ],
    ],
]);
