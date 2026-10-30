<?php

use app\library\FormUi;
use app\models\FollowUp;
use app\models\Patient;
use app\models\SourceReference;
use app\models\Sources;
use app\models\Treatment;
use app\models\Tumour;
use app\models\User;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */
/** @var app\models\Tumour|null $modelTumour */
/** @var app\models\FollowUp|null $modelFollowUp */
/** @var app\models\Treatment[] $modelTreatments */
/** @var app\models\Sources[] $modelSources */
/** @var app\models\IcdoTopography|null $topography */
/** @var app\models\IcdoTopography|null $subsite */
/** @var app\models\IcdoMorphology|null $morphology */

$patientId = sprintf('PT-%05d', $model->id);

$this->title = $model->full_name ?: $patientId;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Patients'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $patientId;

/*
|--------------------------------------------------------------------------
| Display helpers
|--------------------------------------------------------------------------
*/

$dash = '<span class="text-outline">—</span>';

// Stored code → label. Codes may legitimately be 0 (e.g. behaviour "Benign"),
// so only null / '' count as missing.
$decode = static function (array $map, $value): ?string {
    if ($value === null || $value === '') {
        return null;
    }
    return $map[$value] ?? (string) $value;
};

$text = static function ($value) use ($dash): string {
    return ($value === null || $value === '') ? $dash : Html::encode($value);
};

// source_date is free text on the form, so fall back to the raw value
$date = static function ($value) use ($dash): string {
    if (empty($value)) {
        return $dash;
    }
    try {
        return Html::encode(Yii::$app->formatter->asDate($value, 'd MMM yyyy'));
    } catch (\Throwable $e) {
        return Html::encode($value);
    }
};

$item = static function (string $label, string $valueHtml, string $span = '') : string {
    return '<div class="' . $span . '">'
        . '<dt class="text-[10px] uppercase font-bold text-outline tracking-wider mb-1">' . Html::encode($label) . '</dt>'
        . '<dd class="text-sm font-medium text-on-surface break-words">' . $valueHtml . '</dd>'
        . '</div>';
};

$sectionHeader = static function (string $icon, string $title, string $aside = ''): string {
    return '<div class="flex items-center gap-2 mb-6 border-b border-surface-container-high pb-4">'
        . '<span class="material-symbols-outlined text-primary">' . $icon . '</span>'
        . '<h3 class="text-base md:text-lg font-bold text-primary">' . Html::encode($title) . '</h3>'
        . ($aside !== '' ? '<div class="ml-auto">' . $aside . '</div>' : '')
        . '</div>';
};

$card = 'bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)]';

/*
|--------------------------------------------------------------------------
| Resolved values
|--------------------------------------------------------------------------
*/

$ethnicGroup = $decode(Patient::getEthnicGroups(), $model->ethnic_group);
$religion = $decode(Patient::getReligions(), $model->religion);

$nameParts = preg_split('/\s+/', trim((string) $model->full_name), -1, PREG_SPLIT_NO_EMPTY);
$initials = $nameParts
    ? mb_strtoupper(mb_substr($nameParts[0], 0, 1) . (count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''))
    : '?';

// Tumour — decoded with the same option lists the form uses
$t = $modelTumour;
$basis = $t ? $decode(Tumour::getBasisOfDiagnosis(), $t->basis_of_diagnosis) : null;
$laterality = $t ? $decode(Tumour::getLaterality(), $t->laterality) : null;
$behaviour = $t ? $decode(Tumour::getBehaviour(), $t->behaviour) : null;
$stage = $t ? $decode(Tumour::getStage(), $t->stage) : null;
$grade = $t ? $decode(Tumour::getGrade(), $t->grade) : null;
$gradeNotApplicable = $t && $t->behaviour !== null && in_array((int) $t->behaviour, [0, 1], true);
$fullTnm = $t && (int) $t->full_tnm === 1;

$behaviourVariant = match ($t && $t->behaviour !== null ? (int) $t->behaviour : null) {
    3 => 'error',      // Malignant
    2 => 'warning',    // In situ
    default => 'default',
};

// Treatments — concurrent illness is stored on the first treatment row,
// sometimes on a row that has no treatment of its own.
$concurrentIllness = null;
$treatmentRows = [];
foreach ($modelTreatments as $treatment) {
    if ($concurrentIllness === null && trim((string) $treatment->concurrent_illness) !== '') {
        $concurrentIllness = $treatment->concurrent_illness;
    }
    if (!empty($treatment->treatment)) {
        $treatmentRows[] = $treatment;
    }
}
$treatmentLabels = Treatment::getTreatment();
$treatmentStatuses = Treatment::getTreatmentStatus();
$treatmentStatusVariant = [1 => 'default', 2 => 'secondary', 3 => 'warning'];

$sourceTypes = Sources::getSourceTypeOptions();
$sourceReferences = SourceReference::optionsByType();
$modelSources = array_values(array_filter(
    $modelSources,
    static fn(Sources $s): bool => !empty($s->source_type) || !empty($s->source_no) || !empty($s->record_no) || !empty($s->source_date)
));

// Follow-up
$presentStatus = $modelFollowUp ? $decode(FollowUp::getPresentStatusOptions(), $modelFollowUp->present_status) : null;
$presentStatusVariant = $modelFollowUp && (int) $modelFollowUp->present_status === 2 ? 'error' : 'secondary';

// Record audit trail
$userIds = array_filter([$model->created_by, $model->updated_by]);
$usernames = $userIds
    ? User::find()->select(['username', 'id'])->where(['id' => $userIds])->indexBy('id')->column()
    : [];
?>

<div class="max-w-7xl mx-auto w-full">

    <?php /* ─── Page header ─────────────────────────────────────────────── */ ?>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">

        <div class="flex items-center gap-4 min-w-0">
            <div class="h-14 w-14 md:h-16 md:w-16 shrink-0 rounded-2xl bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center text-xl md:text-2xl font-extrabold">
                <?= Html::encode($initials) ?>
            </div>
            <div class="min-w-0">
                <h1 class="text-2xl md:text-3xl font-headline font-extrabold text-primary tracking-tight truncate">
                    <?= Html::encode($model->full_name ?: 'Unnamed patient') ?>
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <?= FormUi::badge($patientId, 'secondary') ?>
                    <?php if ($model->national_id): ?>
                        <?= FormUi::chip('ID ' . $model->national_id) ?>
                    <?php endif; ?>
                    <?php if ($model->age !== null): ?>
                        <?= FormUi::chip($model->age . ' yrs') ?>
                    <?php endif; ?>
                    <?php if ($presentStatus): ?>
                        <?= FormUi::badge($presentStatus, $presentStatusVariant) ?>
                    <?php endif; ?>
                    <?php if ($model->needs_review): ?>
                        <?= FormUi::badge('Needs review', 'warning') ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <?= Html::a(
                '<span class="material-symbols-outlined text-[20px]">arrow_back</span><span>Back</span>',
                ['index'],
                ['class' => 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container-high text-outline font-bold hover:bg-surface-dim transition-colors']
            ) ?>
            <?= Html::a(
                '<span class="material-symbols-outlined text-[20px]">delete</span><span class="hidden sm:inline">Delete</span>',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-error font-bold hover:bg-error-container transition-colors',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this patient and all related records?'),
                        'method' => 'post',
                    ],
                ]
            ) ?>
            <?= Html::a(
                '<span class="material-symbols-outlined text-[20px]">edit_square</span><span>Edit</span>',
                ['update', 'id' => $model->id],
                ['class' => 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-primary to-primary-container text-white font-bold shadow-[0_12px_32px_rgba(0,26,72,0.12)] hover:opacity-90 transition-opacity']
            ) ?>
        </div>

    </div>

    <?php /* ─── Review banner ───────────────────────────────────────────── */ ?>
    <?php if ($model->needs_review): ?>
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8 px-5 py-4 rounded-2xl bg-[#fff3cd] text-[#7a5c00]">
            <span class="material-symbols-outlined shrink-0">rule</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold">This record needs review</p>
                <?php if ($model->review_note): ?>
                    <p class="text-sm mt-1 whitespace-pre-line"><?= Html::encode($model->review_note) ?></p>
                <?php endif; ?>
            </div>
            <?= Html::a(
                '<span class="material-symbols-outlined text-[20px]">task_alt</span><span>Mark as reviewed</span>',
                ['mark-reviewed', 'id' => $model->id],
                [
                    'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/70 font-bold hover:bg-white transition-colors shrink-0',
                    'data' => [
                        'confirm' => 'Confirm that the coding on this record has been checked?',
                        'method' => 'post',
                    ],
                ]
            ) ?>
        </div>
    <?php endif; ?>

    <?php /* ─── Clinical summary strip ──────────────────────────────────── */ ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?= FormUi::statChip('location_searching', 'Primary Site', $t && $t->primary_site ? $t->primary_site : '—') ?>
        <?= FormUi::statChip('biotech', 'Histology', $t && $t->histology ? (string) $t->histology : '—', 'bg-tertiary-fixed', 'text-on-tertiary-fixed-variant') ?>
        <?= FormUi::statChip('stacked_bar_chart', 'Stage', $stage ?? '—') ?>
        <?= FormUi::statChip('event', 'Incidence', $t && $t->incident_date ? Yii::$app->formatter->asDate($t->incident_date, 'd MMM yyyy') : '—', 'bg-tertiary-fixed', 'text-on-tertiary-fixed-variant') ?>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 md:gap-8">

        <?php /* ═══ Main column ═══════════════════════════════════════════ */ ?>
        <div class="w-full lg:w-2/3 space-y-6 md:space-y-8">

            <?php /* ── Patient information ──────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('person', 'Patient Information') ?>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <?= $item('Full Name', $text($model->full_name), 'sm:col-span-2') ?>
                    <?= $item('National ID / Passport', $text($model->national_id)) ?>
                    <?= $item('Next of Kin Telephone', $model->telephone_no_nok
                        ? Html::a(Html::encode($model->telephone_no_nok), 'tel:' . $model->telephone_no_nok, ['class' => 'text-secondary hover:underline'])
                        : $dash) ?>
                    <?= $item('Date of Birth', $date($model->date_of_birth)) ?>
                    <?= $item('Age', $model->age !== null ? Html::encode($model->age) . ' years' : $dash) ?>
                    <?= $item('Place of Birth', $text($model->place_of_birth)) ?>
                    <?= $item('Ethnic Group', $ethnicGroup ? FormUi::chip($ethnicGroup) : $dash) ?>
                    <?= $item('Religion', $religion ? FormUi::chip($religion) : $dash) ?>
                </dl>
            </section>

            <?php /* ── Tumour details ───────────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('biotech', 'Tumour Details', $behaviour ? FormUi::badge($behaviour, $behaviourVariant) : '') ?>

                <?php if (!$t): ?>
                    <p class="text-sm text-on-surface-variant">No tumour details have been recorded for this patient.</p>
                <?php else: ?>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <?= $item('Incident Date', $date($t->incident_date)) ?>
                        <?= $item('Basis of Diagnosis', $text($basis)) ?>
                        <?= $item(
                            'Primary Site (Topography)',
                            $t->primary_site
                                ? '<span class="font-bold text-primary">' . Html::encode($t->primary_site) . '</span>'
                                    . ($topography ? ' <span class="text-on-surface-variant">— ' . Html::encode($topography->description) . '</span>' : '')
                                : $dash,
                            'sm:col-span-2'
                        ) ?>
                        <?= $item(
                            $t->getAttributeLabel('topography_code'),
                            $t->topography_code
                                ? '<span class="font-bold text-primary">' . Html::encode($t->topography_code) . '</span>'
                                    . ($subsite ? ' <span class="text-on-surface-variant">— ' . Html::encode($subsite->description) . '</span>' : '')
                                : $dash,
                            'sm:col-span-2'
                        ) ?>
                        <?= $item(
                            'Histology (Morphology)',
                            $t->histology
                                ? '<span class="font-bold text-primary">' . Html::encode($t->histology) . '</span>'
                                    . ($morphology ? ' <span class="text-on-surface-variant">— ' . Html::encode($morphology->description) . '</span>' : '')
                                : $dash,
                            'sm:col-span-2'
                        ) ?>
                        <?= $item('Laterality', $text($laterality)) ?>
                        <?= $item('Behaviour', $behaviour ? FormUi::badge($behaviour, $behaviourVariant) : $dash) ?>
                        <?= $item('Grade', $gradeNotApplicable && !$grade
                            ? '<span class="text-on-surface-variant">Not applicable</span>'
                            : $text($grade)) ?>
                        <?= $item('Stage', $stage ? FormUi::chip($stage) : $dash) ?>
                    </dl>

                    <?php /* Staging: full TNM when available, otherwise the essential TNM fields */ ?>
                    <div class="mt-6 rounded-xl border border-surface-container-high bg-surface-container-low p-4 md:p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-sm text-primary">grid_view</span>
                            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide">
                                <?= $fullTnm ? 'TNM Staging' : 'Essential TNM' ?>
                            </span>
                            <span class="ml-auto">
                                <?= FormUi::badge($fullTnm ? 'Full TNM available' : 'Full TNM not available', $fullTnm ? 'secondary' : 'default') ?>
                            </span>
                        </div>

                        <?php if ($fullTnm): ?>
                            <div class="grid grid-cols-3 gap-3">
                                <?php foreach (['T' => $t->t, 'N' => $t->n, 'M' => $t->m] as $axis => $value): ?>
                                    <div class="rounded-lg bg-surface-container-lowest p-3 text-center">
                                        <div class="text-[10px] uppercase font-bold text-outline tracking-wider"><?= $axis ?></div>
                                        <div class="text-lg font-extrabold text-primary"><?= $text($value) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                <?= $item('Metastasis', $text($decode(Tumour::getMetastasis(), $t->metastasis))) ?>
                                <?= $item('Regional Nodes Involvement', $text($decode(Tumour::getRegionalNodesInvolvement(), $t->regional_nodes_involvement))) ?>
                                <?= $item('Localized Advanced', $text($decode(Tumour::getLocalizedAdvanced(), $t->localized_advanced))) ?>
                                <?= $item('Localized Limited', $text($decode(Tumour::getLocalizedLimited(), $t->localized_limited))) ?>
                            </dl>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

            <?php /* ── Treatment ────────────────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('medical_services', 'Treatment', $treatmentRows ? FormUi::badge(count($treatmentRows) . ' recorded') : '') ?>

                <?php if (!$treatmentRows): ?>
                    <p class="text-sm text-on-surface-variant">No treatment has been recorded.</p>
                <?php else: ?>
                    <div class="hidden sm:grid grid-cols-3 gap-4 px-4 pb-2 text-[10px] uppercase font-bold text-outline tracking-wider">
                        <span>Treatment</span><span>Received</span><span>Date</span>
                    </div>
                    <ul class="divide-y divide-surface-container-high rounded-xl border border-surface-container-high">
                        <?php foreach ($treatmentRows as $treatment): ?>
                            <?php $status = $decode($treatmentStatuses, $treatment->treatment_status); ?>
                            <li class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4 items-center px-4 py-3">
                                <span class="col-span-2 sm:col-span-1 text-sm font-bold text-primary">
                                    <?= Html::encode($decode($treatmentLabels, $treatment->treatment)) ?>
                                </span>
                                <span>
                                    <?= $status ? FormUi::badge($status, $treatmentStatusVariant[$treatment->treatment_status] ?? 'default') : $dash ?>
                                </span>
                                <span class="text-sm text-on-surface-variant text-right sm:text-left">
                                    <?= $date($treatment->treatment_date) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="mt-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-sm text-primary">medical_information</span>
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide">Concurrent Illness</span>
                    </div>
                    <p class="text-sm text-on-surface whitespace-pre-line">
                        <?= $concurrentIllness !== null ? Html::encode($concurrentIllness) : '<span class="text-outline">None recorded</span>' ?>
                    </p>
                </div>
            </section>

            <?php /* ── Sources ──────────────────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('source', 'Sources', $modelSources ? FormUi::badge(count($modelSources) . ' recorded') : '') ?>

                <?php if (!$modelSources): ?>
                    <p class="text-sm text-on-surface-variant">No sources have been recorded.</p>
                <?php else: ?>
                    <div class="hidden sm:grid grid-cols-4 gap-4 px-4 pb-2 text-[10px] uppercase font-bold text-outline tracking-wider">
                        <span>Source Type</span><span>Source No.</span><span>Record No.</span><span>Date</span>
                    </div>
                    <ul class="divide-y divide-surface-container-high rounded-xl border border-surface-container-high">
                        <?php foreach ($modelSources as $source): ?>
                            <?php $sourceType = $decode($sourceTypes, $source->source_type); ?>
                            <li class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 items-center px-4 py-3">
                                <span class="col-span-2 sm:col-span-1">
                                    <?= $sourceType ? FormUi::chip($sourceType, 'secondary') : $dash ?>
                                </span>
                                <?php $sourceName = SourceReference::nameFor($source->source_type, $source->source_no, $sourceReferences); ?>
                                <span class="text-sm font-medium text-on-surface">
                                    <?= $text($source->source_no) ?>
                                    <?php if ($sourceName): ?>
                                        <span class="block text-xs text-on-surface-variant"><?= Html::encode($sourceName) ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="text-sm font-medium text-on-surface text-right sm:text-left">
                                    <span class="sm:hidden text-[10px] uppercase font-bold text-outline">Record </span><?= $text($source->record_no) ?>
                                </span>
                                <span class="col-span-2 sm:col-span-1 text-sm text-on-surface-variant sm:text-left"><?= $date($source->source_date) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

        </div>

        <?php /* ═══ Sidebar ═════════════════════════════════════════════════ */ ?>
        <aside class="w-full lg:w-1/3 space-y-6 md:space-y-8">

            <?php /* ── Follow-up ────────────────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('follow_the_signs', 'Follow-up') ?>

                <?php if (!$modelFollowUp): ?>
                    <p class="text-sm text-on-surface-variant">No follow-up has been recorded.</p>
                <?php else: ?>
                    <dl class="space-y-5">
                        <?= $item('Present Status', $presentStatus ? FormUi::badge($presentStatus, $presentStatusVariant) : $dash) ?>
                        <?php if ((int) $modelFollowUp->present_status === 2 || $modelFollowUp->cause_of_death): ?>
                            <?= $item('Cause of Death', $text($modelFollowUp->cause_of_death)) ?>
                        <?php endif; ?>
                        <?= $item('Last Date of Contact', $date($modelFollowUp->last_date_of_contact)) ?>
                        <?= $item('Remarks', $modelFollowUp->remarks
                            ? '<span class="whitespace-pre-line">' . Html::encode($modelFollowUp->remarks) . '</span>'
                            : $dash) ?>
                    </dl>
                <?php endif; ?>
            </section>

            <?php /* ── Registration location ────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('location_on', 'Registration Location') ?>

                <?php if ($model->geo_lat && $model->geo_lng): ?>
                    <div id="patient-map" class="w-full rounded-xl overflow-hidden mb-4" style="height:240px"></div>
                    <dl class="grid grid-cols-2 gap-4">
                        <?= $item('Latitude', Html::encode($model->geo_lat)) ?>
                        <?= $item('Longitude', Html::encode($model->geo_lng)) ?>
                        <?= $item('Accuracy', $model->geo_accuracy ? '±' . round($model->geo_accuracy) . ' m' : $dash) ?>
                        <?= $item('Captured', $model->geo_captured ? Html::encode(Yii::$app->formatter->asDatetime($model->geo_captured, 'd MMM yyyy, HH:mm')) : $dash) ?>
                    </dl>
                <?php else: ?>
                    <p class="text-sm text-on-surface-variant">No coordinates were captured at registration.</p>
                <?php endif; ?>
            </section>

            <?php /* ── Record information ───────────────────────────────── */ ?>
            <section class="<?= $card ?>">
                <?= $sectionHeader('history', 'Record') ?>
                <dl class="space-y-5">
                    <?= $item('Registered', $model->created_at
                        ? Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'd MMM yyyy, HH:mm'))
                            . (isset($usernames[$model->created_by]) ? ' <span class="text-on-surface-variant">by ' . Html::encode($usernames[$model->created_by]) . '</span>' : '')
                        : $dash) ?>
                    <?= $item('Last Updated', $model->updated_at
                        ? Html::encode(Yii::$app->formatter->asRelativeTime($model->updated_at))
                            . (isset($usernames[$model->updated_by]) ? ' <span class="text-on-surface-variant">by ' . Html::encode($usernames[$model->updated_by]) . '</span>' : '')
                        : $dash) ?>
                </dl>
            </section>

        </aside>

    </div>

</div>

<?php
if ($model->geo_lat && $model->geo_lng) {
    // Leaflet (CDN, scoped to this view only)
    $this->registerCssFile(
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css',
        ['position' => \yii\web\View::POS_HEAD]
    );
    $this->registerJsFile(
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js',
        ['position' => \yii\web\View::POS_HEAD]
    );

    // Config must exist before patientMap.js runs; json_encode keeps it XSS-safe
    $this->registerJs(
        'window.PatientMapConfig = ' . json_encode([
            'lat' => (float) $model->geo_lat,
            'lng' => (float) $model->geo_lng,
            'label' => $model->full_name,
            'accuracy' => (float) $model->geo_accuracy,
        ]) . ';',
        \yii\web\View::POS_HEAD
    );

    $this->registerJsFile('@web/js/patientMap.js', ['position' => \yii\web\View::POS_END]);
}
?>
