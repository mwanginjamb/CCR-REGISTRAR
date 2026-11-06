<?php

use app\library\FormUi;
use yii\helpers\Html;

/**
 * Footer links under the auth forms (login, signup, password reset, ...).
 *
 * Usage:
 *   <?= $this->render('_authLinks', ['links' => [
 *       ['Forgot password?', ['site/request-password-reset']],
 *       ['Sign up', ['site/signup'], 'No account yet?'],   // optional lead-in text
 *   ]]) ?>
 *
 * @var yii\web\View $this
 * @var array $links [label, url, ?leadIn]
 */
?>
<div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-6 pt-6 border-t border-surface-container-high text-sm <?= count($links) > 1 ? 'justify-between' : 'justify-center' ?>">
    <?php foreach ($links as $link): ?>
        <span class="text-on-surface-variant">
            <?php if (!empty($link[2])): ?>
                <?= Html::encode($link[2]) ?>
            <?php endif; ?>
            <?= FormUi::link($link[0], $link[1]) ?>
        </span>
    <?php endforeach; ?>
</div>
