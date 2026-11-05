<?php

use app\library\FormUi;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */
?>

<?= $this->render('../_header', [
    'title' => 'Permissions',
    'section' => 'Permissions',
    'subtitle' => 'Actions a role can be allowed to perform. Built-in permissions are checked by the application.',
    'cta' => ['New Permission', 'add', ['create-permission']],
]) ?>

<div class="<?= FormUi::gridContainerClass() ?>">
    <div class="overflow-x-auto">
        <?= GridView::widget(FormUi::gridView([
            'dataProvider' => $dataProvider,
            'columns' => array_merge(FormUi::gridColumns([
                [
                    'attribute' => 'name',
                    'label' => 'Permission',
                    'td' => 'primary',
                    'content' => static fn($row) => Html::encode($row->name)
                        . ($row->core ? ' ' . FormUi::badge('Built-in', 'secondary') : ''),
                ],
                ['attribute' => 'description', 'label' => 'Description'],
                ['attribute' => 'roles', 'label' => 'Granted Directly By', 'td' => 'muted'],
                ['attribute' => 'parents', 'label' => 'Included In', 'td' => 'muted'],
                ['attribute' => 'children', 'label' => 'Includes', 'td' => 'muted'],
            ]), [
                FormUi::actionColumn([
                    'update' => static fn($row) => FormUi::actionBtn('edit_square', ['update-permission', 'name' => $row->name], 'edit', ['title' => 'Edit']),
                    'hierarchy' => static fn($row) => FormUi::actionBtn('account_tree', ['manage-hierarchy', 'name' => $row->name], 'view', ['title' => 'Included permissions']),
                    'delete' => static fn($row) => $row->core ? '' : FormUi::actionBtn('delete', ['delete-permission', 'name' => $row->name], 'delete', [
                        'title' => 'Delete',
                        'data' => ['method' => 'post', 'confirm' => "Delete permission \"{$row->name}\"? Roles lose it."],
                    ]),
                ]),
            ]),
        ])) ?>
    </div>
</div>
