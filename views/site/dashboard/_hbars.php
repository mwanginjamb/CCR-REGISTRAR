<?php

use yii\helpers\Html;

/**
 * Horizontal bar chart: one hue, one row per category, value at the bar end.
 * Rows are drawn in the order given (ranked or in scale order, as the caller decides).
 *
 * @var yii\web\View $this
 * @var array $rows      [['label' => string, 'count' => int, 'pct' => ?float], ...]
 * @var string $caption  accessible name / table caption
 * @var string $empty    message when every count is 0
 * @var string $unit     noun for tooltips, e.g. 'cases'
 */

$unit ??= 'cases';
$max = max(array_merge([0], array_column($rows, 'count')));
?>
<?php if ($max === 0): ?>
    <p class="py-8 text-center text-sm text-on-surface-variant"><?= Html::encode($empty) ?></p>
<?php else: ?>
    <ul class="space-y-2.5" aria-label="<?= Html::encode($caption) ?>">
        <?php foreach ($rows as $row): ?>
            <?php
            $width = $row['count'] > 0 ? max(1.5, $row['count'] / $max * 100) : 0;
            $pct = $row['pct'] !== null ? rtrim(rtrim(number_format($row['pct'], 1), '0'), '.') . '%' : '';
            $tip = "{$row['label']}: " . number_format($row['count']) . " $unit" . ($pct ? " ($pct)" : '');
            ?>
            <li class="group relative grid grid-cols-[minmax(0,9rem)_1fr_auto] items-center gap-3 rounded-lg -mx-2 px-2 py-0.5 hover:bg-surface-container-low focus:bg-surface-container-low focus:outline-none"
                tabindex="0" aria-label="<?= Html::encode($tip) ?>">
                <span class="truncate text-xs text-on-surface-variant" title="<?= Html::encode($row['label']) ?>"><?= Html::encode($row['label']) ?></span>
                <span class="relative h-4">
                    <?php if ($width > 0): ?>
                        <span class="absolute inset-y-0 left-0 rounded-r-[4px] bg-surface-tint" style="width: <?= round($width, 2) ?>%"></span>
                    <?php endif; ?>
                </span>
                <span class="text-xs tabular-nums text-on-surface whitespace-nowrap">
                    <span class="font-semibold"><?= number_format($row['count']) ?></span>
                    <?php if ($pct): ?><span class="text-on-surface-variant"> · <?= $pct ?></span><?php endif; ?>
                </span>
                <span role="tooltip" class="pointer-events-none absolute left-1/2 -top-8 z-10 -translate-x-1/2 whitespace-nowrap rounded-md bg-inverse-surface px-2 py-1 text-[11px] text-inverse-on-surface opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus:opacity-100">
                    <?= Html::encode($tip) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <?= $this->render('_table', ['rows' => $rows, 'caption' => $caption, 'unit' => $unit]) ?>
<?php endif; ?>
