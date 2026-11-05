<?php

use app\library\FormUi;
use app\models\IcdoSiteMorphologyRule;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\IcdoSiteMorphologyRuleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

echo $this->render('//setup/_index', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'title' => 'Site–Morphology Rules',
    'subtitle' => '"Common" histologies are listed first for a site; combinations that are neither common nor allowed flag the record for review.',
    'ctaLabel' => 'New Rule',
    'ctaIcon' => 'add',
    'columns' => [
        ['attribute' => 'topography_pattern', 'td' => 'primary'],
        ['attribute' => 'morphology_code', 'td' => 'primary'],
        [
            'attribute' => 'morphologyDescription',
            'content' => static fn(IcdoSiteMorphologyRule $m) => $m->morphology
                ? Html::encode($m->morphology->description)
                : '<span class="text-error">Unknown code</span>',
        ],
        [
            'attribute' => 'rule_type',
            'filter' => IcdoSiteMorphologyRule::RULE_TYPES,
            'content' => static fn(IcdoSiteMorphologyRule $m) => FormUi::badge(
                IcdoSiteMorphologyRule::RULE_TYPES[$m->rule_type] ?? $m->rule_type,
                $m->rule_type === 'common' ? 'secondary' : 'tertiary'
            ),
        ],
        ['attribute' => 'source_version', 'td' => 'muted'],
    ],
]);
