<?php

use yii\helpers\Html;

/**
 * Danger-themed error summary for the patient form.
 *
 * PatientController collects every model's errors into Patient '_form' as
 * "[Section] attribute: message" (e.g. "[Tumour] incident_date: Incident Date
 * cannot be blank."). They are grouped by section here; the attribute name is
 * dropped because the message already names the field.
 *
 * @var yii\web\View $this
 * @var app\models\Patient $model
 */

$errorGroups = [];
foreach ($model->getErrors('_form') as $error) {
    if (preg_match('/^\[(.+?)\]\s+[\w.]+:\s+(.*)$/s', $error, $m)) {
        $errorGroups[$m[1]][] = $m[2];
    } else {
        $errorGroups['Form'][] = $error;
    }
}

if (!$errorGroups) {
    return;
}

$errorCount = array_sum(array_map('count', $errorGroups));
?>
<div role="alert" class="mb-6 rounded-2xl border border-error/30 border-l-4 border-l-error bg-error-container/60 text-on-error-container p-5 md:p-6">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-error shrink-0">error</span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-sm md:text-base">
                The record was not saved: please fix <?= $errorCount ?> <?= $errorCount === 1 ? 'problem' : 'problems' ?>.
            </p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <?php foreach ($errorGroups as $section => $messages): ?>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-error"><?= Html::encode($section) ?></p>
                        <ul class="mt-1 space-y-1 text-sm">
                            <?php foreach ($messages as $message): ?>
                                <li class="flex gap-2">
                                    <span class="text-error" aria-hidden="true">•</span>
                                    <span><?= Html::encode($message) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
