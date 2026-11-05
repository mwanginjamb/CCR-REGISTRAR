<?php

use app\library\FormUi;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */

$chips = static fn(array $names, string $variant = 'default') => $names
    ? '<div class="flex flex-wrap gap-1">' . implode('', array_map(fn($n) => FormUi::chip($n, $variant), $names)) . '</div>'
    : '<span class="text-outline">—</span>';
?>

<?= $this->render('_header', [
    'title' => 'Roles',
    'section' => 'Roles',
    'subtitle' => 'Each role grants its own permissions plus those of the roles it inherits.',
    'cta' => ['New Role', 'add_moderator', ['create-role']],
]) ?>

<div class="<?= FormUi::gridContainerClass() ?>">
    <div class="overflow-x-auto">
        <?= GridView::widget(FormUi::gridView([
            'dataProvider' => $dataProvider,
            'columns' => array_merge(FormUi::gridColumns([
                [
                    'attribute' => 'name',
                    'label' => 'Role',
                    'td' => 'primary',
                    'content' => static fn($row) => Html::encode($row['name'])
                        . ($row['core'] ? ' ' . FormUi::badge('Built-in', 'secondary') : ''),
                ],
                ['attribute' => 'description', 'label' => 'Description'],
                ['label' => 'Inherits', 'content' => static fn($row) => $chips($row['inherits'], 'tertiary')],
                ['label' => 'Own Permissions', 'content' => static fn($row) => $chips($row['permissions'])],
                ['attribute' => 'users', 'label' => 'Users', 'td' => 'muted'],
            ]), [
                FormUi::actionColumn([
                    'update' => static fn($row) => FormUi::actionBtn('edit_square', ['update-role', 'name' => $row['name']], 'edit', ['title' => 'Edit']),
                    'delete' => static fn($row) => $row['core'] ? '' : FormUi::actionBtn('delete', ['delete-role', 'name' => $row['name']], 'delete', [
                        'title' => 'Delete',
                        'data' => ['method' => 'post', 'confirm' => "Delete role \"{$row['name']}\"? Its users lose the role."],
                    ]),
                ]),
            ]),
        ])) ?>
    </div>
</div>
