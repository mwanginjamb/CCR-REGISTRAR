<?php

use app\library\AuthUi;
use app\library\FormUi;
use app\models\SourceReference;
use app\models\Sources;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SourceReferenceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Sources Setup');
$this->params['breadcrumbs'][] = $this->title;

$sourceTypes = Sources::getSourceTypeOptions();

// Shared Tailwind classes for every data column (header, cell, filter input)
$column = static fn(array $config, string $tdVariant = 'default'): array => array_merge([
    'headerOptions' => ['class' => FormUi::thClass()],
    'contentOptions' => ['class' => FormUi::tdClass($tdVariant)],
    'filterInputOptions' => ['class' => AuthUi::inputClass() . ' !p-2 !text-sm'],
], $config);
?>

<?php /* ─── Page header ─────────────────────────────────────────────────── */ ?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8">
    <div>
        <?= FormUi::breadcrumb(['Setup', 'Sources']) ?>
        <h2 class="text-3xl md:text-4xl font-headline font-extrabold text-primary tracking-tight">
            <?= Html::encode($this->title) ?>
        </h2>
        <p class="text-on-surface-variant mt-2 text-base">
            Hospitals, labs and hospices suggested for "Source No" on the patient form.
        </p>
    </div>
    <?= FormUi::ctaButton('New Source', 'add_business', ['create']) ?>
</div>

<div class="<?= FormUi::gridContainerClass() ?>">
    <div class="overflow-x-auto">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'w-full text-left border-collapse'],
            'headerRowOptions' => ['class' => 'bg-surface-container-high'],
            'filterRowOptions' => ['class' => 'bg-surface-container'],
            'rowOptions' => static fn(SourceReference $model) => [
                'class' => FormUi::trClass() . ($model->is_active ? '' : ' opacity-60'),
            ],
            'layout' => "{items}\n<div class=\"" . FormUi::gridFooterClass() . "\"><div class=\"text-xs text-outline\">{summary}</div>{pager}</div>",
            'emptyText' => 'No sources match the filters.',
            'emptyTextOptions' => ['class' => 'px-6 py-10 text-center text-sm text-on-surface-variant'],
            'pager' => [
                'options' => ['class' => 'flex items-center gap-1'],
                'linkContainerOptions' => ['class' => 'rounded-lg overflow-hidden'],
                'linkOptions' => ['class' => 'block px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container-high'],
                'activePageCssClass' => 'bg-primary [&>a]:text-white',
                'disabledPageCssClass' => 'opacity-40',
                'disabledListItemSubTagOptions' => ['class' => 'block px-3 py-1.5 text-sm'],
                'prevPageLabel' => '‹',
                'nextPageLabel' => '›',
            ],
            'columns' => [
                $column([
                    'attribute' => 'source_type',
                    'filter' => $sourceTypes,
                    'content' => static fn(SourceReference $model) => FormUi::chip($sourceTypes[$model->source_type] ?? (string) $model->source_type, 'secondary'),
                ]),
                $column(['attribute' => 'source_no'], 'primary'),
                $column(['attribute' => 'name']),
                $column(['attribute' => 'redcap_value'], 'muted'),
                $column([
                    'attribute' => 'is_active',
                    'filter' => [1 => 'Active', 0 => 'Inactive'],
                    'content' => static fn(SourceReference $model) => $model->is_active
                        ? FormUi::badge('Active', 'secondary')
                        : FormUi::badge('Inactive'),
                ]),
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'header' => 'Actions',
                    'headerOptions' => ['class' => FormUi::thClass(true)],
                    'contentOptions' => ['class' => FormUi::tdClass() . ' text-right whitespace-nowrap'],
                    'template' => '{update} {toggle}',
                    'buttons' => [
                        'update' => static fn($url, SourceReference $model) => FormUi::actionBtn('edit_square', ['update', 'id' => $model->id], 'edit', ['title' => 'Edit']),
                        'toggle' => static fn($url, SourceReference $model) => FormUi::actionBtn(
                            $model->is_active ? 'toggle_off' : 'toggle_on',
                            ['toggle-active', 'id' => $model->id],
                            $model->is_active ? 'delete' : 'edit',
                            [
                                'title' => $model->is_active ? 'Deactivate' : 'Activate',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => $model->is_active
                                        ? 'Deactivate this source? It will no longer be suggested on the patient form.'
                                        : 'Activate this source?',
                                ],
                            ]
                        ),
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>
