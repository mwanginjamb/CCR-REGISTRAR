<?php

use app\components\Rbac;
use app\models\PatientReview;
use yii\helpers\Html;

/**
 * Review banner on the patient page.
 *
 * Shows system warnings (review_note) and the active QA review, with:
 *   - QA: "Mark as reviewed", with an optional resolution note
 *   - the registrar who created the case: "Resubmit to QA" while the review is open
 *
 * @var yii\web\View $this
 * @var app\models\Patient $model
 */

if (!$model->needs_review) {
    return;
}

$user = Yii::$app->user;
$review = $model->getActiveReviews()->with('flaggedByUser')->one();
$canResolve = $user->can(Rbac::REVIEW_PATIENT);
$canResubmit = $review !== null
    && $review->status === PatientReview::STATUS_OPEN
    && $user->can(Rbac::RESUBMIT_PATIENT_REVIEW)
    && ((int) $model->created_by === (int) $user->id || $canResolve);

$fmt = Yii::$app->formatter;
$textarea = 'w-full rounded-lg border-none bg-white/80 p-3 text-sm text-on-surface focus:ring-2 focus:ring-[#7a5c00]/40';
$button = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/80 font-bold hover:bg-white transition-colors';
?>
<div class="mb-8 rounded-2xl bg-[#fff3cd] text-[#7a5c00] p-5 md:p-6 space-y-4">

    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined shrink-0">rule</span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold">
                <?php if ($review && $review->status === PatientReview::STATUS_OPEN): ?>
                    QA flagged this case: waiting for the registrar to correct and resubmit it
                <?php elseif ($review && $review->status === PatientReview::STATUS_RESUBMITTED): ?>
                    Resubmitted by the registrar: waiting for QA
                <?php else: ?>
                    This record needs review
                <?php endif; ?>
            </p>

            <?php if ($review): ?>
                <div class="mt-3 rounded-xl bg-white/60 p-4 text-on-surface">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#7a5c00]"><?= Html::encode($review->getReasonLabel()) ?></p>
                    <p class="text-sm mt-1 whitespace-pre-line"><?= Html::encode($review->note) ?></p>
                    <p class="text-xs text-on-surface-variant mt-2">
                        Flagged by <?= Html::encode($review->flaggedByUser->username ?? 'unknown') ?>,
                        <?= Html::encode($fmt->asRelativeTime($review->flagged_at)) ?>
                        <?php if ($review->notified_at): ?>
                            · registrar emailed (<?= Html::encode($review->notified_email) ?>)
                        <?php elseif ($review->notify_error): ?>
                            · <span class="text-error">registrar not emailed: <?= Html::encode($review->notify_error) ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if ($review->status === PatientReview::STATUS_RESUBMITTED && $review->resubmit_note): ?>
                        <p class="text-sm mt-3 pt-3 border-t border-[#7a5c00]/20 whitespace-pre-line">
                            <span class="font-bold">Registrar's note:</span> <?= Html::encode($review->resubmit_note) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($model->review_note): ?>
                <p class="text-sm mt-3 whitespace-pre-line"><span class="font-bold">System check:</span> <?= Html::encode($model->review_note) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php /* Registrar: send the corrected case back to QA */ ?>
    <?php if ($canResubmit): ?>
        <?= Html::beginForm(['review/resubmit', 'id' => $review->id], 'post', ['class' => 'flex flex-col sm:flex-row gap-3 sm:items-end']) ?>
            <label class="flex-1">
                <span class="block text-xs font-bold mb-1">Note to QA (optional)</span>
                <?= Html::textarea('PatientReview[resubmit_note]', '', ['rows' => 2, 'class' => $textarea, 'placeholder' => 'What did you correct?']) ?>
            </label>
            <?= Html::submitButton(
                '<span class="material-symbols-outlined text-[20px]">send</span><span>Resubmit to QA</span>',
                ['class' => $button, 'data-confirm' => 'Send this case back to QA? Make sure your corrections are saved first.']
            ) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>

    <?php /* QA: close the review */ ?>
    <?php if ($canResolve): ?>
        <?= Html::beginForm(['patient/mark-reviewed', 'id' => $model->id], 'post', ['class' => 'flex flex-col sm:flex-row gap-3 sm:items-end']) ?>
            <label class="flex-1">
                <span class="block text-xs font-bold mb-1">Resolution note (optional)</span>
                <?= Html::textarea('resolution_note', '', ['rows' => 2, 'class' => $textarea, 'placeholder' => 'e.g. Coding confirmed after correction']) ?>
            </label>
            <?= Html::submitButton(
                '<span class="material-symbols-outlined text-[20px]">task_alt</span><span>Mark as reviewed</span>',
                ['class' => $button, 'data-confirm' => 'Mark this case as reviewed and close the QA review?']
            ) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>

</div>
