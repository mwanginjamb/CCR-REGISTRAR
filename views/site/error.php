<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use app\library\FormUi;
use yii\helpers\Html;

$this->title = $name;

// In the guest layout the card header already shows the title
$inGuestShell = $this->context->layout === 'guest';
$isGuest = Yii::$app->user->isGuest;
?>
<div class="site-error <?= $inGuestShell ? '' : 'max-w-2xl' ?>">

    <?php if (!$inGuestShell): ?>
        <h1 class="font-headline font-bold text-2xl text-primary mb-4"><?= Html::encode($this->title) ?></h1>
    <?php endif; ?>

    <div role="alert" class="flex items-start gap-4 mb-6 px-5 py-4 rounded-2xl bg-error-container text-on-error-container">
        <span class="material-symbols-outlined mt-0.5 flex-shrink-0">error</span>
        <span class="flex-1 text-sm font-medium"><?= nl2br(Html::encode($message)) ?></span>
    </div>

    <p class="text-sm text-on-surface-variant mb-2">
        The above error occurred while the Web server was processing your request.
    </p>
    <p class="text-sm text-on-surface-variant">
        Please contact us if you think this is a server error. Thank you.
    </p>

    <?php if ($inGuestShell): ?>
        <?= $this->render('_authLinks', ['links' => [
            $isGuest ? ['Back to login', ['site/login']] : ['Back to home', ['/site/index']],
        ]]) ?>
    <?php else: ?>
        <p class="mt-6">
            <?= FormUi::link('Back to home', ['/site/index']) ?>
        </p>
    <?php endif; ?>

</div>
