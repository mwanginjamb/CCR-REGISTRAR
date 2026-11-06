<?php

use yii\helpers\Html;

/**
 * Column chart over time: one hue, columns <= 24px, hover tooltip per month,
 * the peak month labelled directly.
 *
 * @var yii\web\View $this
 * @var array $rows      [['key' => 'YYYY-MM', 'label' => 'Mar 2026', 'count' => int], ...] oldest first
 * @var string $caption
 * @var string $empty
 */

$max = max(array_merge([0], array_column($rows, 'count')));
$peakKey = null;
foreach ($rows as $row) {
    if ($max > 0 && $row['count'] === $max) {
        $peakKey = $row['key']; // the latest month at the peak
    }
}
?>
<?php if ($max === 0): ?>
    <p class="py-16 text-center text-sm text-on-surface-variant"><?= Html::encode($empty) ?></p>
<?php else: ?>
    <div class="relative" aria-label="<?= Html::encode($caption) ?>" role="group">
        <?php /* Recessive scale: max at the top, 0 at the baseline */ ?>
        <div class="pointer-events-none absolute inset-x-0 top-0 border-t border-dashed border-surface-container-high"></div>
        <span class="absolute -top-2 left-0 bg-surface-container-lowest pr-1 text-[10px] tabular-nums text-outline"><?= number_format($max) ?></span>

        <div class="flex h-44 items-end gap-1 border-b border-outline-variant pt-3">
            <?php foreach ($rows as $row): ?>
                <?php
                $height = $row['count'] > 0 ? max(2, $row['count'] / $max * 100) : 0;
                $tip = "{$row['label']}: " . number_format($row['count']) . ' ' . ($row['count'] === 1 ? 'case' : 'cases');
                ?>
                <div class="group relative flex h-full flex-1 items-end justify-center rounded-t-md hover:bg-surface-container-low focus:bg-surface-container-low focus:outline-none"
                     tabindex="0" aria-label="<?= Html::encode($tip) ?>">
                    <?php if ($height > 0): ?>
                        <div class="relative w-full max-w-[24px] rounded-t-[4px] bg-surface-tint" style="height: <?= round($height, 2) ?>%">
                            <?php if ($row['key'] === $peakKey): ?>
                                <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[11px] font-semibold tabular-nums text-on-surface"><?= number_format($row['count']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <span role="tooltip" class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 -translate-x-1/2 whitespace-nowrap rounded-md bg-inverse-surface px-2 py-1 text-[11px] text-inverse-on-surface opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus:opacity-100">
                        <?= Html::encode($tip) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-1.5 flex gap-1">
            <?php foreach ($rows as $i => $row): ?>
                <span class="flex-1 text-center text-[10px] text-outline">
                    <?= Html::encode(date('M', strtotime($row['key'] . '-01'))) ?>
                    <?php if ($i === 0 || str_ends_with($row['key'], '-01')): ?>
                        <span class="block text-[9px]"><?= Html::encode(substr($row['key'], 0, 4)) ?></span>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <?= $this->render('_table', ['rows' => array_map(fn($r) => $r + ['pct' => null], $rows), 'caption' => $caption, 'unit' => 'cases']) ?>
<?php endif; ?>
