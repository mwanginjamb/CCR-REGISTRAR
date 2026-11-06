<?php

use yii\helpers\Html;

/**
 * Meter: a single ratio against its whole (same-hue track).
 *
 * @var yii\web\View $this
 * @var string $label
 * @var ?float $pct     null when there is nothing to measure
 * @var string $detail  e.g. "12 of 40 cases"
 * @var string|null $help
 * @var bool $compact   smaller value text, for lists of meters
 */

$value = $pct !== null ? rtrim(rtrim(number_format($pct, 1), '0'), '.') . '%' : '—';
$compact ??= false;
?>
<div>
    <div class="flex items-baseline justify-between gap-3">
        <span class="<?= $compact ? 'text-xs' : 'text-sm' ?> font-semibold text-on-surface"><?= Html::encode($label) ?></span>
        <span class="<?= $compact ? 'text-sm font-bold' : 'text-xl font-extrabold' ?> tabular-nums text-primary"><?= $value ?></span>
    </div>
    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-tint/15"
         role="meter" aria-label="<?= Html::encode($label) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $pct ?? 0 ?>">
        <?php if ($pct !== null && $pct > 0): ?>
            <div class="h-full rounded-full bg-surface-tint" style="width: <?= min(100, $pct) ?>%"></div>
        <?php endif; ?>
    </div>
    <p class="mt-1.5 text-xs text-on-surface-variant">
        <?= Html::encode($detail) ?>
        <?php if (!empty($help)): ?><span class="block mt-0.5 text-outline"><?= Html::encode($help) ?></span><?php endif; ?>
    </p>
</div>
