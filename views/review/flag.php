<?php

use app\library\AuthUi;
use app\library\FormUi;
use app\models\PatientReview;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Patient $patient */
/** @var app\models\PatientReview $review */

$caseId = sprintf('PT-%05d', $patient->id);
$registrar = $patient->created_by ? User::findOne($patient->created_by) : null;

$this->title = "Flag {$caseId} for Review";
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Patients'), 'url' => ['patient/index']];
$this->params['breadcrumbs'][] = ['label' => $caseId, 'url' => ['patient/view', 'id' => $patient->id]];
$this->params['breadcrumbs'][] = 'Flag for review';
?>
<div class="mb-8">
    <?= FormUi::breadcrumb(['Patients', $caseId, 'Flag for review']) ?>
    <h2 class="text-2xl md:text-3xl font-headline font-extrabold text-primary tracking-tight"><?= Html::encode($this->title) ?></h2>
    <p class="text-on-surface-variant mt-2">
        <?= Html::encode($patient->full_name ?: 'Unnamed patient') ?>.
        The registrar will be asked to correct the case and resubmit it to QA.
    </p>
</div>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-3xl">

    <?php /* Who will be notified */ ?>
    <div class="flex items-start gap-3 mb-6 p-4 rounded-xl <?= $registrar && $registrar->email ? 'bg-surface-container-low' : 'bg-[#fff3cd] text-[#7a5c00]' ?>">
        <span class="material-symbols-outlined shrink-0"><?= $registrar && $registrar->email ? 'mail' : 'warning' ?></span>
        <p class="text-sm">
            <?php if ($registrar && $registrar->email): ?>
                <strong><?= Html::encode($registrar->username) ?></strong> (<?= Html::encode($registrar->email) ?>), who registered this case,
                will be emailed your note. Replies come to you
                (<?= Html::encode(Yii::$app->user->identity->email ?? '') ?>).
            <?php elseif ($registrar): ?>
                The registrar (<?= Html::encode($registrar->username) ?>) has no email address, so no email will be sent.
            <?php else: ?>
                This case has no recorded registrar, so no email will be sent.
            <?php endif; ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin(['id' => 'review-flag-form']); ?>

    <?= $form->field($review, 'reason')->dropDownList(PatientReview::REASONS, [
        'class' => AuthUi::inputClass(),
        'prompt' => 'Select a reason',
        'showKeys' => false,
    ]) ?>

    <?= $form->field($review, 'note')->textarea([
        'class' => AuthUi::inputClass(),
        'rows' => 5,
        'placeholder' => 'Describe what is wrong and what the registrar should check or correct.',
    ])->hint('Sent to the registrar by email and shown on the case.') ?>

    <?= $this->render('//setup/_buttons', ['cancelUrl' => ['patient/view', 'id' => $patient->id]]) ?>

    <?php ActiveForm::end(); ?>

</section>
