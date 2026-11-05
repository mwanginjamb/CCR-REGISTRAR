<?php

use app\library\FormUi;
use app\models\IcdoSiteRule;

/** @var yii\web\View $this */
/** @var app\models\IcdoSiteRuleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$yesNo = [1 => 'Yes', 0 => 'No'];
$flag = static fn($value) => $value === null
    ? '<span class="text-outline">—</span>'
    : ((int) $value === 1 ? FormUi::badge('Yes', 'secondary') : FormUi::badge('No'));

echo $this->render('//setup/_index', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'title' => 'Site Rules',
    'subtitle' => 'Per topography category: whether laterality applies on the patient form, and whether it is required.',
    'ctaLabel' => 'New Rule',
    'ctaIcon' => 'add',
    'columns' => [
        ['attribute' => 'topography_pattern', 'td' => 'primary'],
        ['attribute' => 'laterality_allowed', 'filter' => $yesNo, 'content' => static fn(IcdoSiteRule $m) => $flag($m->laterality_allowed)],
        ['attribute' => 'laterality_required', 'filter' => $yesNo, 'content' => static fn(IcdoSiteRule $m) => $flag($m->laterality_required)],
        ['attribute' => 'tnm_allowed', 'filter' => $yesNo, 'content' => static fn(IcdoSiteRule $m) => $flag($m->tnm_allowed)],
        ['attribute' => 'grade_allowed', 'filter' => $yesNo, 'content' => static fn(IcdoSiteRule $m) => $flag($m->grade_allowed)],
    ],
]);
