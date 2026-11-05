<?php

use app\library\FormUi;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var callable $rolesByUser fn(User): string[] */
?>

<?= $this->render('../_header', [
    'title' => 'User Role Assignment',
    'section' => 'User Roles',
    'subtitle' => 'New users get the registrar role once their email is verified. Escalate them here.',
]) ?>

<div class="<?= FormUi::gridContainerClass() ?>">
    <div class="overflow-x-auto">
        <?= GridView::widget(FormUi::gridView([
            'dataProvider' => $dataProvider,
            'columns' => array_merge(FormUi::gridColumns([
                ['attribute' => 'username', 'td' => 'primary'],
                ['attribute' => 'email', 'td' => 'muted'],
                [
                    'label' => 'Roles',
                    'content' => static function (User $user) use ($rolesByUser) {
                        $roles = $rolesByUser($user);
                        return $roles
                            ? implode(' ', array_map(fn($r) => FormUi::chip($r, 'secondary'), $roles))
                            : FormUi::badge('No role', 'warning');
                    },
                ],
            ]), [
                FormUi::actionColumn([
                    'assign' => static fn(User $user) => FormUi::actionBtn('manage_accounts', ['assign-role', 'userId' => $user->id], 'edit', ['title' => 'Assign roles']),
                ]),
            ]),
        ])) ?>
    </div>
</div>
