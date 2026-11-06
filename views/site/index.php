<?php

use app\components\Rbac;
use yii\helpers\Html;

/**
 * Overview dashboard (qa/admin). Figures come from app\components\Dashboard
 * via SiteController::actionIndex(); this view only formats and draws them.
 *
 * @var yii\web\View $this
 * @var array $metrics
 */

$this->title = 'Registry Overview';

$m = $metrics;
$fmtPct = static fn(?float $p) => $p === null ? '—' : rtrim(rtrim(number_format($p, 1), '0'), '.') . '%';
$cases = static fn(int $n) => number_format($n) . ($n === 1 ? ' case' : ' cases');
$card = 'bg-surface-container-lowest rounded-2xl p-5 md:p-6 shadow-[0_12px_32px_rgba(0,26,72,0.04)]';
$cardTitle = static fn(string $title, string $sub = '') =>
    '<div class="mb-5"><h3 class="font-headline text-base md:text-lg font-bold text-primary">' . Html::encode($title) . '</h3>'
    . ($sub !== '' ? '<p class="text-xs text-on-surface-variant mt-0.5">' . Html::encode($sub) . '</p>' : '') . '</div>';

$new = $m['newThisMonth'];
$review = $m['review'];
$completeness = $m['completeness'];
$quality = $m['quality'];
?>

<?php /* ─── Header ──────────────────────────────────────────────────────── */ ?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6 lg:mb-8">
    <div>
        <p class="text-secondary font-bold text-xs uppercase tracking-widest mb-1">Live registry figures</p>
        <h2 class="text-2xl lg:text-3xl font-extrabold font-headline text-primary tracking-tight"><?= Html::encode($this->title) ?></h2>
        <p class="text-xs text-on-surface-variant mt-1">
            Active cases only (deleted records excluded) · as of <?= Html::encode(Yii::$app->formatter->asDatetime(time(), 'd MMM yyyy, HH:mm')) ?>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <?= Html::a('<span class="material-symbols-outlined text-[18px]">list_alt</span>Patient registry', ['/patient/index'], [
            'class' => 'inline-flex items-center gap-2 px-4 py-2 bg-white text-primary border border-outline-variant/30 rounded-xl text-sm font-semibold hover:bg-surface-container transition-colors shadow-sm',
        ]) ?>
        <?php if (Yii::$app->user->can(Rbac::CREATE_PATIENT)): ?>
            <?= Html::a('<span class="material-symbols-outlined text-[18px]">add</span>New case', ['/patient/create'], [
                'class' => 'inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity shadow-sm',
            ]) ?>
        <?php endif; ?>
    </div>
</div>

<?php /* ─── Headline numbers ────────────────────────────────────────────── */ ?>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">

    <div class="<?= $card ?>">
        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Registered cases</p>
        <p class="mt-2 text-4xl font-extrabold font-headline tabular-nums text-primary"><?= number_format($m['total']) ?></p>
        <p class="mt-1 text-xs text-on-surface-variant">All active cases in the registry</p>
    </div>

    <div class="<?= $card ?>">
        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">New this month</p>
        <p class="mt-2 text-4xl font-extrabold font-headline tabular-nums text-primary"><?= number_format($new['current']) ?></p>
        <p class="mt-1 flex items-center gap-1 text-xs text-on-surface-variant">
            <?php if ($new['change'] === null): ?>
                <?= number_format($new['previous']) ?> last month
            <?php else: ?>
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true"><?= $new['change'] > 0 ? 'trending_up' : ($new['change'] < 0 ? 'trending_down' : 'trending_flat') ?></span>
                <span class="font-semibold text-on-surface"><?= ($new['change'] > 0 ? '+' : '') . $fmtPct($new['change']) ?></span>
                vs last month (<?= number_format($new['previous']) ?>)
            <?php endif; ?>
        </p>
    </div>

    <div class="<?= $card ?>">
        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Complete abstracts</p>
        <p class="mt-2 text-4xl font-extrabold font-headline tabular-nums text-primary"><?= $fmtPct($completeness['completePct']) ?></p>
        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-tint/15" role="meter" aria-label="Complete abstracts"
             aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $completeness['completePct'] ?? 0 ?>">
            <?php if ($completeness['completePct']): ?>
                <div class="h-full rounded-full bg-surface-tint" style="width: <?= $completeness['completePct'] ?>%"></div>
            <?php endif; ?>
        </div>
        <p class="mt-1.5 text-xs text-on-surface-variant">
            <?= number_format($completeness['complete']) ?> of <?= number_format($completeness['total']) ?> have every core tumour field
        </p>
    </div>

    <div class="<?= $card ?>">
        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Flagged for review</p>
        <p class="mt-2 text-4xl font-extrabold font-headline tabular-nums text-primary"><?= number_format($review['flagged']) ?></p>
        <ul class="mt-2 space-y-1 text-xs">
            <li class="flex items-center gap-1.5 text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-[#7a5c00]" aria-hidden="true">pending_actions</span>
                Awaiting registrar <span class="ml-auto font-semibold tabular-nums text-on-surface"><?= number_format($review['awaitingRegistrar']) ?></span>
            </li>
            <li class="flex items-center gap-1.5 text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-on-tertiary-fixed-variant" aria-hidden="true">rate_review</span>
                Awaiting QA <span class="ml-auto font-semibold tabular-nums text-on-surface"><?= number_format($review['awaitingQa']) ?></span>
            </li>
            <li class="flex items-center gap-1.5 text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-secondary" aria-hidden="true">task_alt</span>
                Resolved, last 30 days <span class="ml-auto font-semibold tabular-nums text-on-surface"><?= number_format($review['resolved30d']) ?></span>
            </li>
        </ul>
    </div>
</div>

<?php /* ─── Trend + quality indicators ─────────────────────────────────── */ ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8">

    <section class="<?= $card ?> lg:col-span-2">
        <?= $cardTitle('Cases registered per month', 'Last 12 months, by date entered into the registry') ?>
        <?= $this->render('dashboard/_columns', [
            'rows' => $m['byMonth'],
            'caption' => 'Cases registered per month',
            'empty' => 'No cases registered in the last 12 months.',
        ]) ?>
    </section>

    <section class="<?= $card ?>">
        <?= $cardTitle('Registry quality indicators', 'IARC indicators of diagnostic validity') ?>
        <div class="space-y-6">
            <?= $this->render('dashboard/_meter', [
                'label' => 'Morphologically verified (MV%)',
                'pct' => $quality['mvPct'],
                'detail' => number_format($quality['mv']) . ' of ' . $cases($quality['withBasis']) . ' with a recorded basis',
                'help' => 'Confirmed by cytology or histology. Higher is better.',
            ]) ?>
            <?= $this->render('dashboard/_meter', [
                'label' => 'Death certificate only (DCO%)',
                'pct' => $quality['dcoPct'],
                'detail' => number_format($quality['dco']) . ' of ' . $cases($quality['withBasis']) . ' with a recorded basis',
                'help' => 'Known only from a death certificate. Lower is better.',
            ]) ?>
        </div>
    </section>
</div>

<?php /* ─── Case mix ────────────────────────────────────────────────────── */ ?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8">

    <section class="<?= $card ?>">
        <?= $cardTitle('Cases by primary site', 'Top sites by ICD-O-3 topography category') ?>
        <?= $this->render('dashboard/_hbars', [
            'rows' => $m['bySite'],
            'caption' => 'Cases by primary site',
            'empty' => 'No cases with a primary site recorded yet.',
        ]) ?>
    </section>

    <section class="<?= $card ?>">
        <?= $cardTitle('Stage at diagnosis', 'All active cases, in stage order') ?>
        <?= $this->render('dashboard/_hbars', [
            'rows' => $m['stage'],
            'caption' => 'Stage at diagnosis',
            'empty' => 'No cases registered yet.',
        ]) ?>
    </section>
</div>

<?php /* ─── Data quality + activity ─────────────────────────────────────── */ ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">

    <section class="<?= $card ?>">
        <?= $cardTitle('Data completeness', 'Share of cases with each core tumour field') ?>
        <?php if ($completeness['total'] === 0): ?>
            <p class="py-8 text-center text-sm text-on-surface-variant">No cases registered yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($completeness['fields'] as $field): ?>
                    <?= $this->render('dashboard/_meter', [
                        'label' => $field['label'],
                        'pct' => $field['pct'],
                        'detail' => number_format($field['filled']) . ' of ' . $cases($completeness['total']),
                        'compact' => true,
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="<?= $card ?>">
        <?= $cardTitle('Basis of diagnosis', 'All active cases, in code order') ?>
        <?= $this->render('dashboard/_hbars', [
            'rows' => $m['basis'],
            'caption' => 'Basis of diagnosis',
            'empty' => 'No cases registered yet.',
        ]) ?>
    </section>

    <section class="<?= $card ?>">
        <?= $cardTitle('Most active registrars', 'Cases registered in the last 30 days') ?>
        <?php if (!$m['topRegistrars']): ?>
            <p class="py-8 text-center text-sm text-on-surface-variant">No cases registered in the last 30 days.</p>
        <?php else: ?>
            <table class="w-full text-sm">
                <caption class="sr-only">Most active registrars, last 30 days</caption>
                <thead>
                    <tr class="border-b border-surface-container-high text-xs text-on-surface-variant">
                        <th scope="col" class="py-2 text-left font-semibold">Registrar</th>
                        <th scope="col" class="py-2 text-right font-semibold">Cases</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <?php foreach ($m['topRegistrars'] as $registrar): ?>
                        <tr>
                            <td class="py-2 text-on-surface"><?= Html::encode(mb_strtoupper(mb_substr($registrar['username'], 0, 1)) . mb_substr($registrar['username'], 1)) ?></td>
                            <td class="py-2 text-right font-semibold tabular-nums text-primary"><?= number_format($registrar['count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
