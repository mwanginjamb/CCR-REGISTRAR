<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use app\assets\AppAsset;
AppAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= Html::encode($this->title) ?> | Clinical Curator</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet"/>

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <?php $this->head() ?>
</head>
<body class="bg-background text-on-surface">
<?php $this->beginBody() ?>

<!-- ============================================================
     Side Navigation
     ============================================================ -->
<aside class="nav-aside fixed left-0 top-0 h-full flex flex-col p-4 z-40 bg-[#eceef0] dark:bg-slate-800 w-64 shadow-[12px_0_32px_rgba(0,26,72,0.04)] transition-all duration-300 transition-transform">

    <!-- Logo -->
    <div class="mb-10 lg:px-4 flex justify-center lg:justify-start">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-container rounded-lg flex items-center justify-center text-white shrink-0">
                <span class="material-symbols-outlined">biotech</span>
            </div>
            <div class="logo-text">
                <h1 class="text-lg font-bold text-[#001a48] dark:text-blue-100 headline-font leading-tight">Clinical Curator</h1>
                <p class="text-[10px] uppercase tracking-widest text-secondary font-bold">Precision Registry</p>
            </div>
        </div>
    </div>

    <!-- Primary Nav Links -->
    <nav class="flex-1 space-y-2">
        <?= Html::a(
            '<span class="material-symbols-outlined">dashboard</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Overview</span>',
            ['/site/index'],
            [
                'class' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
                'encode' => false,
            ]
        ) ?>
        <?= Html::a(
            '<span class="material-symbols-outlined">query_stats</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Incidence</span>',
            ['/incidence/index'],
            [
                'class' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
                'encode' => false,
            ]
        ) ?>
        <?= Html::a(
            '<span class="material-symbols-outlined">analytics</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Stage Data</span>',
            ['/stage/index'],
            [
                'class' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
                'encode' => false,
            ]
        ) ?>
        <?= Html::a(
            '<span class="material-symbols-outlined">speed</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Registry Performance</span>',
            ['/performance/index'],
            [
                'class' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
                'encode' => false,
            ]
        ) ?>
        <?= Html::a(
            '<span class="material-symbols-outlined">assessment</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Reports</span>',
            ['/report/index'],
            [
                'class' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-3 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1',
                'encode' => false,
            ]
        ) ?>
    </nav>

    <!-- Bottom Actions -->
    <div class="mt-auto pt-6 border-t border-outline-variant/20 space-y-2 flex flex-col items-center lg:items-stretch">
        <button class="new-analysis-btn w-full bg-primary text-white py-3 px-4 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 flex items-center justify-center gap-2 mb-4 hover:opacity-90 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span class="nav-label">New Analysis</span>
        </button>
        <?= Html::a(
            '<span class="material-symbols-outlined">settings</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Settings</span>',
            ['/site/settings'],
            [
                'class' => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-2 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1 w-full',
                'encode' => false,
            ]
        ) ?>
        <?= Html::a(
            '<span class="material-symbols-outlined">logout</span><span class="nav-label font-[\'Inter\'] font-medium text-sm">Log Out</span>',
            ['/site/logout'],
            [
                'class'  => 'nav-link flex items-center gap-3 text-slate-600 dark:text-slate-400 px-4 py-2 hover:bg-white/50 transition-transform duration-200 hover:translate-x-1 w-full',
                'data'   => ['method' => 'post'],
                'encode' => false,
            ]
        ) ?>
    </div>
</aside>

<!-- ============================================================
     Main Canvas
     ============================================================ -->
<main class="main-canvas ml-0 lg:ml-64 min-h-screen transition-all duration-300">

    <!-- Top Navigation Bar -->
    <header class="flex justify-between items-center w-full px-4 lg:px-8 h-16 sticky top-0 z-30 bg-[#f2f4f6] dark:bg-slate-900 border-b-0">
        <div class="flex items-center gap-4 lg:gap-8">

            <!-- Sidebar Toggle -->
            <button class="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-[#eceef0] rounded-full transition-colors z-50"
                    id="sidebar-toggle"
                    aria-label="Toggle sidebar">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <!-- Search -->
            <div class="relative hidden sm:block">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input class="bg-surface-container-highest border-none rounded-full pl-10 pr-4 py-1.5 text-sm w-48 md:w-64 focus:ring-2 focus:ring-primary-container"
                       placeholder="Search registry..."
                       type="text"/>
            </div>
        </div>

        <div class="flex items-center gap-2 lg:gap-4">
            <button class="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-[#eceef0] rounded-full transition-colors"
                    aria-label="Notifications">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <button class="hidden md:flex w-10 h-10 items-center justify-center text-slate-500 hover:bg-[#eceef0] rounded-full transition-colors"
                    aria-label="Help">
                <span class="material-symbols-outlined">help</span>
            </button>

            <div class="h-8 w-px bg-outline-variant/30 mx-1 lg:mx-2"></div>

            <!-- User Info -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-primary leading-none">
                        <?= Html::encode(Yii::$app->user->identity->name ?? 'Guest') ?>
                    </p>
                    <p class="text-[10px] text-on-surface-variant">
                        <?= Html::encode(Yii::$app->user->identity->role ?? '') ?>
                    </p>
                </div>
                <img class="w-8 h-8 lg:w-10 lg:h-10 rounded-full border-2 border-white shadow-sm object-cover"
                     src="<?= Html::encode(Yii::$app->user->identity->avatarUrl ?? '/img/avatar-placeholder.png') ?>"
                     alt="<?= Html::encode(Yii::$app->user->identity->name ?? 'User') ?>"/>
            </div>
        </div>
    </header>

    <!-- Flash Alerts -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="mx-4 lg:mx-8 mt-4 p-4 bg-secondary-container/20 border border-secondary/20 rounded-xl flex items-center gap-3">
            <span class="material-symbols-outlined text-secondary">check_circle</span>
            <p class="text-sm font-medium text-on-secondary-container">
                <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
            </p>
        </div>
    <?php endif ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="mx-4 lg:mx-8 mt-4 p-4 bg-error-container/40 border border-error/20 rounded-xl flex items-center gap-3">
            <span class="material-symbols-outlined text-error">error</span>
            <p class="text-sm font-medium text-on-error-container">
                <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
            </p>
        </div>
    <?php endif ?>

    <!-- Page Content -->
    <div class="p-4 lg:p-8">
        <?= $content ?>
    </div>

</main>

<!-- Contextual FAB -->
<button class="fixed bottom-6 right-6 lg:bottom-8 lg:right-8 w-14 h-14 bg-primary text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all z-50"
        aria-label="Add chart">
    <span class="material-symbols-outlined text-2xl">add_chart</span>
</button>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<?php
$this->registerJs(<<<JS
(function () {
    var toggle  = document.getElementById('sidebar-toggle');
    var overlay = document.getElementById('sidebar-overlay');
    var body    = document.body;

    function openSidebar()  { body.classList.add('sidebar-open');       }
    function closeSidebar() { body.classList.remove('sidebar-open');    }
    function collapseSidebar() { body.classList.toggle('sidebar-collapsed'); }

    toggle.addEventListener('click', function () {
        if (window.innerWidth >= 1024) {
            collapseSidebar();
        } else {
            body.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            closeSidebar();
        }
    });
}());
JS
);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>