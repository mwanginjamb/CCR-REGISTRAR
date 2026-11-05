<?php

use app\library\FormUi;
use app\models\User;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var callable $rolesByUser fn(User): string[] */

$statuses = [
    User::STATUS_ACTIVE => ['Active', 'secondary'],
    User::STATUS_INACTIVE => ['Inactive', 'warning'],
    User::STATUS_DELETED => ['Deleted', 'error'],
];
?>

<?= $this->render('_header', [
    'title' => 'System Users',
    'section' => 'Users',
    'subtitle' => 'Accounts that can sign in. Inactive accounts (unverified or deactivated) cannot log in.',
]) ?>

<div class="<?= FormUi::gridContainerClass() ?>">
    <div class="overflow-x-auto">
        <?= GridView::widget(FormUi::gridView([
            'dataProvider' => $dataProvider,
            'rowOptions' => static fn(User $user) => [
                'class' => FormUi::trClass() . ((int) $user->status === User::STATUS_ACTIVE ? '' : ' opacity-60'),
            ],
            'columns' => array_merge(FormUi::gridColumns([
                ['attribute' => 'username', 'td' => 'primary'],
                ['attribute' => 'email', 'td' => 'muted'],
                [
                    'attribute' => 'status',
                    'content' => static fn(User $user) => FormUi::badge(...($statuses[(int) $user->status] ?? [(string) $user->status, 'default'])),
                ],
                [
                    'label' => 'Roles',
                    'content' => static function (User $user) use ($rolesByUser) {
                        $roles = $rolesByUser($user);
                        return $roles
                            ? implode(' ', array_map(fn($r) => FormUi::chip($r, 'secondary'), $roles))
                            : '<span class="text-outline">—</span>';
                    },
                ],
                ['attribute' => 'created_at', 'label' => 'Registered', 'format' => ['date', 'php:d M Y'], 'td' => 'muted'],
            ]), [
                FormUi::actionColumn([
                    'roles' => static fn(User $user) => FormUi::actionBtn('manage_accounts', ['assign-role', 'userId' => $user->id], 'edit', ['title' => 'Assign roles']),
                    'toggle' => static function (User $user) {
                        if ((int) $user->id === (int) Yii::$app->user->id) {
                            return '';
                        }
                        $active = (int) $user->status === User::STATUS_ACTIVE;
                        return FormUi::actionBtn($active ? 'person_off' : 'person_check', ['toggle-user-status', 'id' => $user->id], $active ? 'delete' : 'edit', [
                            'title' => $active ? 'Deactivate' : 'Activate',
                            'data' => [
                                'method' => 'post',
                                'confirm' => $active
                                    ? "Deactivate {$user->username}? They will no longer be able to log in."
                                    : "Activate {$user->username}?",
                            ],
                        ]);
                    },
                ]),
            ]),
        ])) ?>
    </div>
</div>
