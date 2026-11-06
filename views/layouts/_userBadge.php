<?php

use app\components\Rbac;
use yii\helpers\Html;

/**
 * Logged-in user's name (first letter capitalised) with their roles as tags,
 * shown at the top right of the sidebar layouts.
 *
 * Usage: <?= $this->render('_userBadge', ['showEmail' => true]) ?>
 *
 * @var yii\web\View $this
 * @var bool $showEmail  show the email under the name (main layout)
 */

if (Yii::$app->user->isGuest) {
    return;
}

$identity = Yii::$app->user->identity;
$username = (string) $identity->username;
$displayName = mb_strtoupper(mb_substr($username, 0, 1)) . mb_substr($username, 1);

$roleLabels = [Rbac::ROLE_QA => 'QA'];
$roles = array_keys(Yii::$app->authManager->getRolesByUser($identity->id));

// Tag colours per built-in role; custom roles use the neutral style
$roleClasses = [
    Rbac::ROLE_ADMIN => 'bg-primary text-white',
    Rbac::ROLE_QA => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
    Rbac::ROLE_REGISTRAR => 'bg-secondary-container text-on-secondary-container',
];
?>
<div class="text-right hidden sm:block">
    <div class="flex items-center justify-end gap-1.5 flex-wrap">
        <span class="text-xs font-bold text-primary leading-none"><?= Html::encode($displayName) ?></span>
        <?php foreach ($roles as $role): ?>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide leading-none <?= $roleClasses[$role] ?? 'bg-surface-container-high text-on-surface-variant' ?>">
                <?= Html::encode($roleLabels[$role] ?? mb_strtoupper(mb_substr($role, 0, 1)) . mb_substr($role, 1)) ?>
            </span>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($showEmail) && !empty($identity->email)): ?>
        <p class="text-[10px] text-on-surface-variant mt-0.5"><?= Html::encode($identity->email) ?></p>
    <?php endif; ?>
</div>
