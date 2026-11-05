<?php

use app\library\FormUi;
use app\models\IcdoMorphology;
use app\models\Tumour;

/** @var yii\web\View $this */
/** @var app\models\IcdoMorphologySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$behaviours = Tumour::getBehaviour();

echo $this->render('//setup/_index', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'title' => 'ICD-O Morphology',
    'subtitle' => 'Histology codes offered on the patient form. Behaviour, when set, fills the tumour\'s behaviour automatically.',
    'ctaLabel' => 'New Code',
    'ctaIcon' => 'add',
    'columns' => [
        ['attribute' => 'code', 'td' => 'primary'],
        'description',
        [
            'attribute' => 'behaviour_code',
            'filter' => $behaviours,
            'content' => static fn(IcdoMorphology $m) => $m->behaviour_code === null || $m->behaviour_code === ''
                ? '<span class="text-outline">—</span>'
                : FormUi::badge($behaviours[$m->behaviour_code] ?? $m->behaviour_code, (int) $m->behaviour_code === 3 ? 'error' : 'default'),
        ],
        ['attribute' => 'source_version', 'td' => 'muted'],
        [
            'attribute' => 'is_active',
            'filter' => [1 => 'Active', 0 => 'Inactive'],
            'content' => static fn(IcdoMorphology $m) => $m->is_active ? FormUi::badge('Active', 'secondary') : FormUi::badge('Inactive'),
        ],
    ],
]);
