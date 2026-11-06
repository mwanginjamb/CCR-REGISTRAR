<?php

use app\library\FormUi;
use app\models\PatientReview;
use yii\helpers\Html;

/**
 * QA review history on the patient page (newest first). Hidden when the case
 * has never been reviewed.
 *
 * @var yii\web\View $this
 * @var app\models\Patient $model
 * @var string $card            section card classes from view.php
 * @var callable $sectionHeader section header renderer from view.php
 */

$reviews = $model->getReviews()->with(['flaggedByUser', 'resubmittedByUser', 'resolvedByUser'])->all();
if (!$reviews) {
    return;
}

$fmt = Yii::$app->formatter;
$statusBadge = [
    PatientReview::STATUS_OPEN => ['Awaiting registrar', 'warning'],
    PatientReview::STATUS_RESUBMITTED => ['Awaiting QA', 'tertiary'],
    PatientReview::STATUS_RESOLVED => ['Resolved', 'secondary'],
];

// One timeline entry: who did what, when, and an optional note
$step = static function (string $icon, string $action, ?string $who, ?int $at, ?string $note = null) use ($fmt): string {
    if ($at === null) {
        return '';
    }
    return '<li class="flex gap-3">'
        . '<span class="material-symbols-outlined text-[18px] text-outline mt-0.5">' . $icon . '</span>'
        . '<div class="min-w-0">'
        . '<p class="text-sm"><span class="font-semibold">' . Html::encode($action) . '</span> by '
        . Html::encode($who ?? 'unknown') . ' · <span class="text-on-surface-variant">'
        . Html::encode($fmt->asDatetime($at, 'd MMM yyyy, HH:mm')) . '</span></p>'
        . ($note ? '<p class="text-sm text-on-surface-variant whitespace-pre-line mt-0.5">' . Html::encode($note) . '</p>' : '')
        . '</div></li>';
};
?>
<section class="<?= $card ?>">
    <?= $sectionHeader('history_edu', 'QA Review History', FormUi::badge(count($reviews) . ' review' . (count($reviews) === 1 ? '' : 's'))) ?>

    <ol class="space-y-4">
        <?php foreach ($reviews as $review): ?>
            <?php [$label, $variant] = $statusBadge[$review->status] ?? [$review->status, 'default']; ?>
            <li class="rounded-xl border border-surface-container-high p-4">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="text-sm font-bold text-primary"><?= Html::encode($review->getReasonLabel()) ?></span>
                    <span class="ml-auto"><?= FormUi::badge($label, $variant) ?></span>
                </div>
                <ul class="space-y-2">
                    <?= $step('flag', 'Flagged', $review->flaggedByUser->username ?? null, $review->flagged_at, $review->note) ?>
                    <?= $step('send', 'Resubmitted', $review->resubmittedByUser->username ?? null, $review->resubmitted_at, $review->resubmit_note) ?>
                    <?= $step('task_alt', 'Resolved', $review->resolvedByUser->username ?? null, $review->resolved_at, $review->resolution_note) ?>
                </ul>
                <?php if ($review->notify_error): ?>
                    <p class="text-xs text-error mt-3">Registrar not emailed: <?= Html::encode($review->notify_error) ?></p>
                <?php elseif ($review->notified_at): ?>
                    <p class="text-xs text-on-surface-variant mt-3">Registrar emailed at <?= Html::encode($review->notified_email) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</section>
