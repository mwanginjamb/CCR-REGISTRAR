<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * One expandable sidebar group (accordion), rendered from a config array:
 *
 *   [
 *       'label'    => 'RBAC Mgt',
 *       'icon'     => 'admin_panel_settings',
 *       'children' => [
 *           ['label' => 'Roles', 'url' => ['/rbac/index'], 'icon' => 'security_update_good',
 *            'active' => ['rbac/index', 'rbac/*-role']],   // optional route patterns
 *       ],
 *   ]
 *
 * A child is highlighted when the current route matches one of its 'active'
 * patterns (default: its own url route). The group opens when a child is active.
 * Uses <details>, so it works without JavaScript.
 *
 * @var yii\web\View $this
 * @var array $group
 * @var string $variant 'main' | 'questionnaire'
 */

$currentRoute = Yii::$app->controller->route ?? '';

$isActive = static function (array $child) use ($currentRoute): bool {
    $patterns = $child['active'] ?? [ltrim($child['url'][0], '/')];
    foreach ($patterns as $pattern) {
        if (StringHelper::matchWildcard($pattern, $currentRoute)) {
            return true;
        }
    }
    return false;
};

$open = false;
foreach ($group['children'] as $child) {
    $open = $open || $isActive($child);
}

$styles = [
    'main' => [
        'summary' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1 cursor-pointer select-none',
        'label' => "nav-label font-['Inter'] font-medium text-sm",
        'chevron' => 'nav-label material-symbols-outlined text-base ml-auto transition-transform duration-200 group-open/nav:rotate-180',
        'list' => 'mt-1 space-y-1 lg:pl-4',
        'item' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-2 rounded-lg hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
        'active' => 'bg-white text-[#001a48] font-bold shadow-[0_4px_12px_rgba(0,26,72,0.04)]',
        'itemLabel' => "nav-label font-['Inter'] text-sm",
    ],
    'questionnaire' => [
        'summary' => 'flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg cursor-pointer select-none',
        'label' => 'font-inter text-sm font-medium hidden lg:block',
        'chevron' => 'material-symbols-outlined text-base ml-auto hidden lg:block transition-transform duration-200 group-open/nav:rotate-180',
        'list' => 'mt-1 space-y-1 lg:pl-4',
        'item' => 'flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg',
        'active' => 'bg-white text-[#001a48] font-bold shadow-[0_4px_12px_rgba(0,26,72,0.04)]',
        'itemLabel' => 'font-inter text-sm hidden lg:block',
    ],
];
$s = $styles[$variant] ?? $styles['main'];
?>
<details class="group/nav" <?= $open ? 'open' : '' ?>>
    <summary class="<?= $s['summary'] ?> list-none [&::-webkit-details-marker]:hidden" title="<?= Html::encode($group['label']) ?>">
        <span class="material-symbols-outlined"><?= Html::encode($group['icon'] ?? 'folder') ?></span>
        <span class="<?= $s['label'] ?>"><?= Html::encode($group['label']) ?></span>
        <span class="<?= $s['chevron'] ?>" aria-hidden="true">expand_more</span>
    </summary>

    <div class="<?= $s['list'] ?>">
        <?php foreach ($group['children'] as $child): ?>
            <?= Html::a(
                Html::tag('span', Html::encode($child['icon'] ?? 'chevron_right'), ['class' => 'material-symbols-outlined text-xl'])
                . Html::tag('span', Html::encode($child['label']), ['class' => $s['itemLabel']]),
                $child['url'],
                [
                    'class' => $s['item'] . ($isActive($child) ? ' ' . $s['active'] : ''),
                    'title' => $child['label'],
                    'encode' => false,
                ]
            ) ?>
        <?php endforeach; ?>
    </div>
</details>
