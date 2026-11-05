<?php

use app\library\FormUi;
use app\models\IcdoTopography;

/** @var yii\web\View $this */
/** @var app\models\IcdoTopographySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

echo $this->render('//setup/_index', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'title' => 'ICD-O Topography',
    'subtitle' => 'Primary site codes. C**.9 codes are offered as primary sites; the others as sub-sites ("C").',
    'ctaLabel' => 'New Code',
    'ctaIcon' => 'add',
    'columns' => [
        ['attribute' => 'code', 'td' => 'primary'],
        'description',
        ['attribute' => 'redcap_value', 'td' => 'muted'],
        ['attribute' => 'source_version', 'td' => 'muted'],
        [
            'attribute' => 'is_active',
            'filter' => [1 => 'Active', 0 => 'Inactive'],
            'content' => static fn(IcdoTopography $m) => $m->is_active ? FormUi::badge('Active', 'secondary') : FormUi::badge('Inactive'),
        ],
    ],
]);
