<?php

use app\library\FormUi;
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

$buttons = [
    'update' => static fn($model) => FormUi::actionBtn('edit_square', ['update', 'id' => $model->id], 'edit', ['title' => 'Edit']),
];
if ($softDelete) {
    $buttons['toggle'] = static fn($model) => FormUi::actionBtn(
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
    );
} else {
    $buttons['delete'] = static fn($model) => FormUi::actionBtn('delete', ['delete', 'id' => $model->id], 'delete', ['title' => 'Delete']);
}
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
        <?= GridView::widget(FormUi::gridView([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'rowOptions' => static fn($model) => [
                'class' => FormUi::trClass() . ($softDelete && !$model->is_active ? ' opacity-60' : ''),
            ],
            'emptyText' => 'No entries match the filters.',
            'columns' => array_merge(FormUi::gridColumns($columns), [FormUi::actionColumn($buttons)]),
        ])) ?>
    </div>
</div>
