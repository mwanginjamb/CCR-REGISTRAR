<?php

use yii\helpers\Html;

/**
 * Expandable "Settings" group for the sidebar layouts.
 *
 * Usage: <?= $this->render('_settingsNav', ['variant' => 'main']) ?>
 *   variant 'main'          — views/layouts/main.php (labels hidden < 1024px via .nav-label)
 *   variant 'questionnaire' — views/layouts/questionnaire.php (labels hidden < lg)
 *
 * Uses <details>, so it expands without JavaScript; it opens automatically
 * on any Settings page.
 *
 * @var yii\web\View $this
 * @var string $variant
 */

// Shown to the same users the Settings controllers allow (SetupController::ACCESS_ROLES,
// currently any logged-in user). Once RBAC exists, change both to the admin role,
// e.g. here: if (!Yii::$app->user->can('admin')) { return; }
if (Yii::$app->user->isGuest) {
    return;
}

$items = [
    ['id' => 'icdo-topography', 'icon' => 'location_searching', 'label' => 'ICD-O Topography'],
    ['id' => 'icdo-morphology', 'icon' => 'biotech', 'label' => 'ICD-O Morphology'],
    ['id' => 'icdo-site-rule', 'icon' => 'rule', 'label' => 'Site Rules'],
    ['id' => 'icdo-site-morphology-rule', 'icon' => 'join_inner', 'label' => 'Site–Morphology Rules'],
    ['id' => 'source-reference', 'icon' => 'local_hospital', 'label' => 'Sources'],
];

$currentId = Yii::$app->controller->id ?? null;
$open = in_array($currentId, array_column($items, 'id'), true);

$styles = [
    'main' => [
        'summary' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1 cursor-pointer select-none',
        'label' => "nav-label font-['Inter'] font-medium text-sm",
        'chevron' => 'nav-label material-symbols-outlined text-base ml-auto transition-transform duration-200 group-open/settings:rotate-180',
        'list' => 'mt-1 space-y-1 lg:pl-4',
        'item' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-2 rounded-lg hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
        'active' => 'bg-white text-[#001a48] font-bold shadow-[0_4px_12px_rgba(0,26,72,0.04)]',
        'itemLabel' => "nav-label font-['Inter'] text-sm",
    ],
    'questionnaire' => [
        'summary' => 'flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg cursor-pointer select-none',
        'label' => 'font-inter text-sm font-medium hidden lg:block',
        'chevron' => 'material-symbols-outlined text-base ml-auto hidden lg:block transition-transform duration-200 group-open/settings:rotate-180',
        'list' => 'mt-1 space-y-1 lg:pl-4',
        'item' => 'flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg',
        'active' => 'bg-white text-[#001a48] font-bold shadow-[0_4px_12px_rgba(0,26,72,0.04)]',
        'itemLabel' => 'font-inter text-sm hidden lg:block',
    ],
];
$s = $styles[$variant] ?? $styles['main'];
?>
<details class="group/settings" <?= $open ? 'open' : '' ?>>
    <summary class="<?= $s['summary'] ?> list-none [&::-webkit-details-marker]:hidden" title="Settings">
        <span class="material-symbols-outlined">settings</span>
        <span class="<?= $s['label'] ?>">Settings</span>
        <span class="<?= $s['chevron'] ?>" aria-hidden="true">expand_more</span>
    </summary>

    <div class="<?= $s['list'] ?>">
        <?php foreach ($items as $item): ?>
            <?= Html::a(
                Html::tag('span', $item['icon'], ['class' => 'material-symbols-outlined text-xl'])
                . Html::tag('span', Html::encode($item['label']), ['class' => $s['itemLabel']]),
                ['/' . $item['id'] . '/index'],
                [
                    'class' => $s['item'] . ($item['id'] === $currentId ? ' ' . $s['active'] : ''),
                    'title' => $item['label'],
                    'encode' => false,
                ]
            ) ?>
        <?php endforeach; ?>
    </div>
</details>
