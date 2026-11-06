<?php

/**
 * Email verification confirm step. The emailed link opens this page (GET);
 * the button POSTs back to verify, so mail link scanners that prefetch the
 * link cannot verify the account on the user's behalf.
 *
 * @var yii\web\View $this
 * @var string $token
 */

use app\library\FormUi;
use yii\helpers\Html;

$this->title = 'Confirm Your Email';
$this->params['card_subtitle'] = 'One last step: confirm your email address to activate your account.';
?>
<div class="site-verify-email">

    <?= Html::beginForm(['site/verify-email', 'token' => $token], 'post', ['id' => 'verify-email-form']) ?>

    <?= Html::submitButton('Confirm my email <span class="material-symbols-outlined">verified</span>', [
        'class' => FormUi::buttonClass(),
        'name' => 'confirm-button',
    ]) ?>

    <?= Html::endForm() ?>

    <?= $this->render('_authLinks', ['links' => [
        ['Back to login', ['site/login']],
    ]]) ?>

</div>
