<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Analytics Overview';
?>

<!-- Title & Quick Actions -->
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6 lg:mb-8">
    <div>
        <p class="text-secondary font-bold text-xs uppercase tracking-widest mb-1">Regional Oncology Data</p>
        <h2 class="text-2xl lg:text-3xl font-extrabold headline-font text-primary tracking-tight">
            <?= Html::encode($this->title) ?>
        </h2>
    </div>
    <div class="flex gap-2 sm:gap-3">
        <button class="flex-1 sm:flex-none px-3 lg:px-4 py-2 bg-white text-primary border border-outline-variant/30 rounded-xl text-xs lg:text-sm font-semibold flex items-center justify-center gap-2 hover:bg-surface-container transition-colors shadow-sm">
            <span class="material-symbols-outlined text-sm">calendar_today</span>
            <span class="hidden lg:inline">Last 30 Days</span>
            <span class="lg:hidden">30 Days</span>
        </button>
        <button class="flex-1 sm:flex-none px-3 lg:px-4 py-2 bg-white text-primary border border-outline-variant/30 rounded-xl text-xs lg:text-sm font-semibold flex items-center justify-center gap-2 hover:bg-surface-container transition-colors shadow-sm">
            <span class="material-symbols-outlined text-sm">download</span>
            <span>Export</span>
        </button>
    </div>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">

    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0_4px_20px_rgba(0,26,72,0.02)] border border-outline-variant/10">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-primary-container/10 rounded-lg">
                <span class="material-symbols-outlined text-primary">groups</span>
            </div>
            <span class="text-xs font-bold text-secondary bg-secondary-container/20 px-2 py-1 rounded-full">+4.2%</span>
        </div>
        <p class="text-on-surface-variant text-xs font-medium mb-1">Total Cases</p>
        <h3 class="text-2xl font-extrabold headline-font text-primary">12,842</h3>
    </div>

    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0_4px_20px_rgba(0,26,72,0.02)] border border-outline-variant/10">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-tertiary-container/10 rounded-lg">
                <span class="material-symbols-outlined text-on-tertiary-container">pending_actions</span>
            </div>
            <span class="text-xs font-bold text-on-tertiary-container bg-tertiary-fixed/30 px-2 py-1 rounded-full">High Priority</span>
        </div>
        <p class="text-on-surface-variant text-xs font-medium mb-1">Pending Reviews</p>
        <h3 class="text-2xl font-extrabold headline-font text-primary">148</h3>
    </div>

    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0_4px_20px_rgba(0,26,72,0.02)] border border-outline-variant/10">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-secondary-container/10 rounded-lg">
                <span class="material-symbols-outlined text-secondary">verified</span>
            </div>
            <span class="text-xs font-bold text-secondary bg-secondary-container/20 px-2 py-1 rounded-full">Target: 95%</span>
        </div>
        <p class="text-on-surface-variant text-xs font-medium mb-1">Data Completeness</p>
        <h3 class="text-2xl font-extrabold headline-font text-primary">92.4%</h3>
    </div>

    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-[0_4px_20px_rgba(0,26,72,0.02)] border border-outline-variant/10">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-error-container/10 rounded-lg">
                <span class="material-symbols-outlined text-error">assignment_late</span>
            </div>
            <span class="text-xs font-bold text-error bg-error-container/40 px-2 py-1 rounded-full">-2.1%</span>
        </div>
        <p class="text-on-surface-variant text-xs font-medium mb-1">Lost to Follow-up</p>
        <h3 class="text-2xl font-extrabold headline-font text-primary">8.6%</h3>
    </div>

</div>

<!-- Main Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-6 lg:mb-8">

    <!-- Incidence by Primary Site -->
    <div class="bg-surface-container-lowest p-6 lg:p-8 rounded-2xl shadow-[0_12px_32px_rgba(0,26,72,0.04)] border border-outline-variant/10">
        <div class="flex justify-between items-center mb-6 lg:mb-8">
            <h4 class="headline-font font-bold text-primary text-lg">Incidence by Primary Site</h4>
            <button class="text-secondary text-sm font-bold flex items-center gap-1">
                Details <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </div>
        <div class="space-y-4 lg:space-y-5">
            <?php
            $sites = [
                ['label' => 'Breast',       'pct' => 24.2, 'color' => 'bg-primary'],
                ['label' => 'Cervix Uteri', 'pct' => 18.5, 'color' => 'bg-secondary'],
                ['label' => 'Prostate',     'pct' => 14.1, 'color' => 'bg-primary-container'],
                ['label' => 'Esophagus',    'pct' =>  9.8, 'color' => 'bg-secondary-fixed-dim'],
                ['label' => 'Colorectal',   'pct' =>  7.4, 'color' => 'bg-outline'],
            ];
            foreach ($sites as $site): ?>
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-bold mb-1">
                        <span class="text-primary"><?= Html::encode($site['label']) ?></span>
                        <span class="text-on-surface-variant"><?= $site['pct'] ?>%</span>
                    </div>
                    <div class="h-2.5 w-full bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full <?= $site['color'] ?> rounded-full" style="width: <?= $site['pct'] ?>%"></div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <!-- New Abstracts Trend (bar chart) -->
    <div class="bg-surface-container-lowest p-6 lg:p-8 rounded-2xl shadow-[0_12px_32px_rgba(0,26,72,0.04)] border border-outline-variant/10 flex flex-col">
        <div class="flex justify-between items-center mb-8">
            <h4 class="headline-font font-bold text-primary text-lg">New Abstracts Trend</h4>
            <div class="flex gap-2">
                <span class="flex items-center gap-1.5 text-[10px] font-bold text-primary">
                    <span class="w-2 h-2 rounded-full bg-primary"></span> 2023
                </span>
                <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-slate-200"></span> 2022
                </span>
            </div>
        </div>
        <?php
        // Each entry: [month abbreviation, bar height %, is-active (current month)]
        $bars = [
            ['MAR', 40,  false],
            ['APR', 60,  false],
            ['MAY', 55,  false],
            ['JUN', 85,  true],   // active / current month
            ['JUL', 70,  false],
            ['AUG', 95,  false],
            ['SEP', 80,  false],
        ];
        ?>
        <div class="flex-1 relative flex items-end gap-1.5 lg:gap-2 pb-6 min-h-[200px]">
            <?php foreach ($bars as [$month, $height, $active]): ?>
                <div class="flex-1 bg-surface-container rounded-t-md group relative" style="height: <?= $height ?>%">
                    <div class="absolute inset-x-0 bottom-0 rounded-t-md h-full transition-all
                        <?= $active ? 'bg-primary' : 'bg-primary/20 group-hover:bg-primary/40' ?>">
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        <div class="flex justify-between px-2 pt-4 border-t border-outline-variant/10">
            <?php foreach ($bars as [$month, $height, $active]): ?>
                <span class="text-[10px] font-bold <?= $active ? 'text-primary' : 'text-on-surface-variant' ?>">
                    <?= $month ?>
                </span>
            <?php endforeach ?>
        </div>
    </div>

</div>

<!-- Bottom Data Layer -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">

    <!-- Stage Distribution (donut) -->
    <div class="xl:col-span-1 bg-surface-container-lowest p-6 lg:p-8 rounded-2xl shadow-[0_12px_32px_rgba(0,26,72,0.04)] border border-outline-variant/10">
        <h4 class="headline-font font-bold text-primary text-lg mb-6">Stage Distribution</h4>
        <div class="relative w-32 h-32 lg:w-40 lg:h-40 mx-auto mb-6">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                <circle cx="18" cy="18" r="16" fill="transparent" stroke="#eceef0" stroke-width="4"/>
                <circle cx="18" cy="18" r="16" fill="transparent" stroke="#001a48" stroke-width="4" stroke-dasharray="25, 100" stroke-dashoffset="0"/>
                <circle cx="18" cy="18" r="16" fill="transparent" stroke="#0d6683" stroke-width="4" stroke-dasharray="35, 100" stroke-dashoffset="-25"/>
                <circle cx="18" cy="18" r="16" fill="transparent" stroke="#b1c5ff" stroke-width="4" stroke-dasharray="30, 100" stroke-dashoffset="-60"/>
                <circle cx="18" cy="18" r="16" fill="transparent" stroke="#e0e3e5" stroke-width="4" stroke-dasharray="10, 100" stroke-dashoffset="-90"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Reported</span>
                <span class="text-lg lg:text-xl font-extrabold text-primary headline-font">90%</span>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 text-[10px] font-bold">
            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-primary"></div> Stage I (25%)</div>
            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-secondary"></div> Stage II (35%)</div>
            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-primary-fixed-dim"></div> Stage III (30%)</div>
            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-surface-variant"></div> Stage IV (10%)</div>
        </div>
    </div>

    <!-- Registry Health Metrics -->
    <div class="xl:col-span-2 bg-surface-container-lowest p-6 lg:p-8 rounded-2xl shadow-[0_12px_32px_rgba(0,26,72,0.04)] border border-outline-variant/10 flex flex-col">
        <div class="flex justify-between items-center mb-8">
            <h4 class="headline-font font-bold text-primary text-lg">Registry Health Status</h4>
            <div class="bg-secondary-container/20 text-on-secondary-container text-xs px-3 py-1 rounded-full font-bold flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-on-secondary-container rounded-full"></span> Healthy
            </div>
        </div>

        <?php
        $metrics = [
            ['label' => 'Microscopic Confirmation', 'pct' => 88, 'color' => 'bg-secondary',    'text' => 'text-secondary'],
            ['label' => 'Treatment Information',    'pct' => 74, 'color' => 'bg-secondary',    'text' => 'text-secondary'],
            ['label' => '5-Year Follow-up Rate',    'pct' => 62, 'color' => 'bg-secondary',    'text' => 'text-secondary'],
            ['label' => 'DCO (Death Only)',         'pct' =>  4, 'color' => 'bg-on-tertiary-container', 'text' => 'text-on-tertiary-container'],
        ];
        // Split into two columns
        $columns = array_chunk($metrics, 2);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-6 lg:gap-y-8 flex-1">
            <?php foreach ($columns as $col): ?>
                <div class="space-y-4">
                    <?php foreach ($col as $m): ?>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-xs font-bold text-primary"><?= Html::encode($m['label']) ?></span>
                                <span class="text-xs font-bold <?= $m['text'] ?>"><?= $m['pct'] ?>%</span>
                            </div>
                            <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden">
                                <div class="h-full <?= $m['color'] ?> rounded-full" style="width: <?= $m['pct'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
        </div>

        <!-- Recommendation Banner -->
        <div class="mt-8 glass-panel p-4 rounded-xl border border-white/20 flex flex-col md:flex-row items-center gap-4">
            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-white text-xl" style="font-variation-settings: 'FILL' 1;">lightbulb</span>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h5 class="text-xs font-bold text-primary">Optimization Recommendation</h5>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">
                    Follow-up rates in Rift Valley region are 12% below average. Mobilize officers.
                </p>
            </div>
            <button class="text-[10px] font-bold text-primary px-3 py-1 bg-primary-fixed rounded-lg whitespace-nowrap">
                Review Now
            </button>
        </div>
    </div>

</div>