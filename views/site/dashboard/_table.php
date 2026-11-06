<?php

use yii\helpers\Html;

/**
 * Collapsible table view of a chart's data (accessibility and exact values).
 *
 * @var yii\web\View $this
 * @var array $rows      [['label' => string, 'count' => int, 'pct' => ?float], ...]
 * @var string $caption
 * @var string $unit
 */

$hasPct = array_filter(array_column($rows, 'pct'), fn($p) => $p !== null) !== [];
?>
<details class="mt-4 group/table">
    <summary class="inline-flex cursor-pointer select-none items-center gap-1 text-xs font-semibold text-secondary hover:underline list-none [&::-webkit-details-marker]:hidden">
        <span class="material-symbols-outlined text-[16px]">table_rows</span>
        <span class="group-open/table:hidden">Show as table</span>
        <span class="hidden group-open/table:inline">Hide table</span>
    </summary>
    <table class="mt-3 w-full text-left text-xs">
        <caption class="sr-only"><?= Html::encode($caption) ?></caption>
        <thead>
            <tr class="border-b border-surface-container-high text-on-surface-variant">
                <th scope="col" class="py-1.5 pr-2 font-semibold">Category</th>
                <th scope="col" class="py-1.5 pr-2 text-right font-semibold"><?= Html::encode(ucfirst($unit)) ?></th>
                <?php if ($hasPct): ?><th scope="col" class="py-1.5 text-right font-semibold">Share</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-container-high">
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="py-1.5 pr-2"><?= Html::encode($row['label']) ?></td>
                    <td class="py-1.5 pr-2 text-right tabular-nums"><?= number_format($row['count']) ?></td>
                    <?php if ($hasPct): ?>
                        <td class="py-1.5 text-right tabular-nums"><?= $row['pct'] !== null ? number_format($row['pct'], 1) . '%' : '—' ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</details>
