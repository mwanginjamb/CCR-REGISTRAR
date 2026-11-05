<?php

use app\library\AuthUi;
use app\library\FormUi;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * Shared list page for Settings controllers (app\components\SetupController).
 *
 * Data columns get the registry's table styling; set 'td' => 'primary'|'muted'
 * on a column for a cell variant. The action column is added automatically:
 * edit, plus activate/deactivate for tables with is_active, or delete otherwise.
 *
 * @var yii\web\View $this
 * @var yii\base\Model $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var string $title
 * @var string $subtitle
 * @var string $ctaLabel
 * @var string $ctaIcon
 * @var array $columns
 */

$this->title = $title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = $this->title;

$softDelete = $searchModel->hasAttribute('is_active');

$styled = array_map(static function ($column) {
    $column = is_string($column) ? ['attribute' => $column] : $column;
    $variant = $column['td'] ?? 'default';
    unset($column['td']);

    return array_merge([
        'headerOptions' => ['class' => FormUi::thClass()],
        'contentOptions' => ['class' => FormUi::tdClass($variant)],
        'filterInputOptions' => ['class' => AuthUi::inputClass() . ' !p-2 !text-sm'],
    ], $column);
}, $columns);

$styled[] = [
    'class' => ActionColumn::class,
    'header' => 'Actions',
    'headerOptions' => ['class' => FormUi::thClass(true)],
    'contentOptions' => ['class' => FormUi::tdClass() . ' text-right whitespace-nowrap'],
    'template' => $softDelete ? '{update} {toggle}' : '{update} {delete}',
    'buttons' => [
        'update' => static fn($url, $model) => FormUi::actionBtn('edit_square', ['update', 'id' => $model->id], 'edit', ['title' => 'Edit']),
        'toggle' => static fn($url, $model) => FormUi::actionBtn(
            $model->is_active ? 'toggle_off' : 'toggle_on',
            ['toggle-active', 'id' => $model->id],
            $model->is_active ? 'delete' : 'edit',
            [
                'title' => $model->is_active ? 'Deactivate' : 'Activate',
                'data' => [
                    'method' => 'post',
                    'confirm' => $model->is_active
                        ? 'Deactivate this entry? It will no longer be offered on the patient form.'
                        : 'Activate this entry?',
                ],
            ]
        ),
        'delete' => static fn($url, $model) => FormUi::actionBtn('delete', ['delete', 'id' => $model->id], 'delete', ['title' => 'Delete']),
    ],
];
?>

<?php /* ─── Page header ─────────────────────────────────────────────────── */ ?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8">
    <div>
        <?= FormUi::breadcrumb(['Settings', $title]) ?>
        <h2 class="text-3xl md:text-4xl font-headline font-extrabold text-primary tracking-tight">
            <?= Html::encode($title) ?>
        </h2>
        <p class="text-on-surface-variant mt-2 text-base"><?= Html::encode($subtitle) ?></p>
    </div>
    <?= FormUi::ctaButton($ctaLabel, $ctaIcon, ['create']) ?>
</div>

<div class="<?= FormUi::gridContainerClass() ?>">
    <div class="overflow-x-auto">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'w-full text-left border-collapse'],
            'headerRowOptions' => ['class' => 'bg-surface-container-high'],
            'filterRowOptions' => ['class' => 'bg-surface-container'],
            'rowOptions' => static fn($model) => [
                'class' => FormUi::trClass() . ($softDelete && !$model->is_active ? ' opacity-60' : ''),
            ],
            'layout' => "{items}\n<div class=\"" . FormUi::gridFooterClass() . "\"><div class=\"text-xs text-outline\">{summary}</div>{pager}</div>",
            'emptyText' => 'No entries match the filters.',
            'emptyTextOptions' => ['class' => 'px-6 py-10 text-center text-sm text-on-surface-variant'],
            'pager' => [
                'options' => ['class' => 'flex flex-wrap items-center gap-1'],
                'linkContainerOptions' => ['class' => 'rounded-lg overflow-hidden'],
                'linkOptions' => ['class' => 'block px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container-high'],
                'activePageCssClass' => 'bg-primary [&>a]:text-white',
                'disabledPageCssClass' => 'opacity-40',
                'disabledListItemSubTagOptions' => ['class' => 'block px-3 py-1.5 text-sm'],
                'prevPageLabel' => '‹',
                'nextPageLabel' => '›',
                'maxButtonCount' => 7,
            ],
            'columns' => $styled,
        ]) ?>
    </div>
</div>
