<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AuthAsset;

/** @var yii\web\View $this */
/** @var string $content */

AuthAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html class="light h-full" lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no"
    >

    <?= Html::csrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <?php //$this->render('_tailwind') ?>

    <?php $this->head() ?>
</head>

<body class="
    bg-background
    text-on-background
    min-h-screen
    flex
    flex-col
    antialiased
">

<?php $this->beginBody() ?>

<!-- =========================================================
| TOP NAVBAR
========================================================= -->

<header class="
    fixed
    top-0
    inset-x-0
    z-50
    h-16
    bg-slate-50
    border-b
    border-surface-container-high
    px-4
    md:px-6
">

    <div class="h-full flex items-center justify-between gap-4">

        <!-- LEFT -->
        <div class="flex items-center gap-4 lg:gap-8 min-w-0">

            <!-- MOBILE MENU -->
            <button
                type="button"
                class="
                    lg:hidden
                    p-2
                    rounded-lg
                    hover:bg-surface-container
                    transition-colors
                "
                data-sidebar-toggle
            >
                <span class="material-symbols-outlined">
                    menu
                </span>
            </button>

            <!-- BRAND -->
            <?= Html::a(
                Yii::$app->name,
                Yii::$app->homeUrl,
                [
                    'class' => '
                        text-xl
                        font-extrabold
                        text-primary
                        tracking-tight
                        whitespace-nowrap
                    '
                ]
            ) ?>

            <!-- SEARCH -->
            <div class="relative hidden md:block">

                <span class="
                    material-symbols-outlined
                    absolute
                    left-3
                    top-1/2
                    -translate-y-1/2
                    text-outline
                ">
                    search
                </span>

                <input
                    type="text"
                    placeholder="Search registry..."
                    class="
                        pl-10
                        pr-4
                        py-2
                        bg-surface-container
                        border-none
                        rounded-xl
                        text-sm
                        w-48
                        lg:w-72
                        focus:ring-2
                        focus:ring-primary
                    "
                >
            </div>

        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-2 md:gap-4 shrink-0">

            <!-- NOTIFICATIONS -->
            <button class="
                p-2
                rounded-full
                hover:bg-surface-container
                transition-colors
                text-primary
            ">
                <span class="material-symbols-outlined">
                    notifications
                </span>
            </button>

            <!-- SETTINGS -->
            <button class="
                hidden
                sm:flex
                p-2
                rounded-full
                hover:bg-surface-container
                transition-colors
                text-primary
            ">
                <span class="material-symbols-outlined">
                    settings
                </span>
            </button>

            <!-- USER -->
            <div class="
                h-9
                w-9
                rounded-full
                bg-primary-fixed
                overflow-hidden
                ring-2
                ring-white
            ">
                <?= Html::img(
                    '@web/images/default-avatar.jpg',
                    [
                        'class' => 'h-full w-full object-cover',
                        'alt' => 'User Avatar'
                    ]
                ) ?>
            </div>

        </div>

    </div>

</header>

<!-- =========================================================
| LAYOUT WRAPPER
========================================================= -->

<div class="flex flex-1 pt-16 min-h-screen">

    <!-- =====================================================
    | SIDEBAR BACKDROP (MOBILE)
    ===================================================== -->

    <div
        class="
            fixed
            inset-0
            bg-black/40
            z-30
            hidden
            lg:hidden
        "
        data-sidebar-backdrop
    ></div>

    <!-- =====================================================
    | SIDEBAR
    ===================================================== -->

    <aside
        class="
            fixed
            top-16
            left-0
            z-40
            h-[calc(100vh-64px)]
            w-72
            lg:w-64
            bg-surface-container-low
            border-r
            border-surface-container-high
            transition-transform
            duration-300
            -translate-x-full
            lg:translate-x-0
            overflow-y-auto
            no-scrollbar
        "
        data-sidebar
    >

        <div class="flex flex-col h-full py-6 px-4">

            <!-- HEADER -->
            <div class="mb-8">

                <div class="flex items-center gap-3 mb-2">

                    <div class="
                        h-10
                        w-10
                        rounded-xl
                        bg-primary-container
                        flex
                        items-center
                        justify-center
                    ">
                        <span class="
                            material-symbols-outlined
                            text-on-primary-container
                        ">
                            clinical_notes
                        </span>
                    </div>

                    <div>
                        <h2 class="
                            font-headline
                            font-extrabold
                            text-primary
                        ">
                            KEMRI Registry
                        </h2>

                        <p class="
                            text-xs
                            text-on-surface-variant
                        ">
                            Precision Data Entry
                        </p>
                    </div>

                </div>

            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 space-y-1">

                <?php

                $menuItems = [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'dashboard',
                        'url' => ['/site/index'],
                    ],
                    [
                        'label' => 'Patients',
                        'icon' => 'groups',
                        'url' => ['/patient/index'],
                    ],
                    [
                        'label' => 'Registry',
                        'icon' => 'clinical_notes',
                        'url' => ['/registry/index'],
                    ],
                    [
                        'label' => 'Reports',
                        'icon' => 'assessment',
                        'url' => ['/report/index'],
                    ],
                ];

                foreach ($menuItems as $item):

                    $active = Yii::$app->controller->route === ltrim(
                        Url::to($item['url']),
                        '/'
                    );

                    $classes = $active
                        ? '
                            bg-white
                            text-primary
                            font-bold
                            shadow-[0_4px_12px_rgba(0,26,72,0.04)]
                        '
                        : '
                            text-on-surface-variant
                            hover:bg-surface-container
                        ';
                ?>

                    <?= Html::a(
                        '
                            <span class="material-symbols-outlined text-xl">
                                ' . $item['icon'] . '
                            </span>

                            <span class="text-sm font-medium">
                                ' . $item['label'] . '
                            </span>
                        ',
                        $item['url'],
                        [
                            'class' => "
                                flex
                                items-center
                                gap-3
                                px-4
                                py-3
                                rounded-xl
                                transition-all
                                {$classes}
                            ",
                        ]
                    ) ?>

                <?php endforeach; ?>

            </nav>

            <!-- FOOTER -->
            <div class="
                pt-6
                mt-6
                border-t
                border-surface-container-high
                space-y-2
            ">

                <?= Html::a(
                    '
                        <span class="material-symbols-outlined">
                            help
                        </span>

                        <span>Help Center</span>
                    ',
                    ['/site/help'],
                    [
                        'class' => '
                            flex
                            items-center
                            gap-3
                            px-4
                            py-3
                            rounded-xl
                            hover:bg-surface-container
                            transition-colors
                            text-on-surface-variant
                        '
                    ]
                ) ?>

                <?= Html::a(
                    '
                        <span class="material-symbols-outlined">
                            add
                        </span>

                        <span>New Site</span>
                    ',
                    ['/patient/create'],
                    [
                        'class' => '
                            flex
                            items-center
                            justify-center
                            gap-2
                            w-full
                            px-4
                            py-3
                            rounded-xl
                            bg-primary
                            text-white
                            font-semibold
                            hover:opacity-90
                            transition-opacity
                        '
                    ]
                ) ?>

            </div>

        </div>

    </aside>

    <!-- =====================================================
    | MAIN CONTENT
    ===================================================== -->

    <main class="
        flex-1
        lg:ml-64
        min-w-0
        overflow-x-hidden
    ">

        <div class="
            px-4
            md:px-6
            lg:px-8
            py-6
            md:py-8
        ">

            <!-- =============================================
            | PAGE HEADER AREA
            ============================================== -->

            <?php if (!empty($this->title)): ?>

                <div class="mb-6 md:mb-8">

                    <h1 class="
                        text-2xl
                        md:text-3xl
                        font-headline
                        font-extrabold
                        text-primary
                        tracking-tight
                    ">
                        <?= Html::encode($this->title) ?>
                    </h1>

                </div>

            <?php endif; ?>

            <!-- =============================================
            | BREADCRUMBS
            ============================================== -->

            <?php if (!empty($this->params['breadcrumbs'])): ?>

                <div class="mb-6">

                    <?= Breadcrumbs::widget([
                        'homeLink' => [
                            'label' => 'Dashboard',
                            'url' => Yii::$app->homeUrl,
                        ],

                        'links' => $this->params['breadcrumbs'],

                        'options' => [
                            'class' => '
                                flex
                                flex-wrap
                                items-center
                                gap-2
                                text-xs
                                md:text-sm
                                text-outline
                                font-medium
                            '
                        ],

                        'itemTemplate' => '
                            <li class="flex items-center gap-2">
                                {link}
                            </li>
                        ',

                        'activeItemTemplate' => '
                            <li class="
                                text-on-surface-variant
                                font-semibold
                            ">
                                {link}
                            </li>
                        ',

                        'tag' => 'ul',
                    ]) ?>

                </div>

            <?php endif; ?>

            <!-- =============================================
            | FLASH MESSAGES
            ============================================== -->

            <div class="space-y-4 mb-6">

                <?php if (Yii::$app->session->hasFlash('success')): ?>

                    <div class="
                        rounded-2xl
                        border
                        border-green-200
                        bg-green-50
                        p-4
                        flex
                        items-start
                        gap-3
                    ">

                        <span class="
                            material-symbols-outlined
                            text-green-600
                        ">
                            check_circle
                        </span>

                        <div>

                            <h4 class="
                                font-bold
                                text-green-800
                                mb-1
                            ">
                                Success
                            </h4>

                            <p class="text-sm text-green-700">
                                <?= Yii::$app->session->getFlash('success') ?>
                            </p>

                        </div>

                    </div>

                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('error')): ?>

                    <div class="
                        rounded-2xl
                        border
                        border-red-200
                        bg-red-50
                        p-4
                        flex
                        items-start
                        gap-3
                    ">

                        <span class="
                            material-symbols-outlined
                            text-red-600
                        ">
                            error
                        </span>

                        <div>

                            <h4 class="
                                font-bold
                                text-red-800
                                mb-1
                            ">
                                Error
                            </h4>

                            <p class="text-sm text-red-700">
                                <?= Yii::$app->session->getFlash('error') ?>
                            </p>

                        </div>

                    </div>

                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('warning')): ?>

                    <div class="
                        rounded-2xl
                        border
                        border-yellow-200
                        bg-yellow-50
                        p-4
                        flex
                        items-start
                        gap-3
                    ">

                        <span class="
                            material-symbols-outlined
                            text-yellow-600
                        ">
                            warning
                        </span>

                        <div>

                            <h4 class="
                                font-bold
                                text-yellow-800
                                mb-1
                            ">
                                Warning
                            </h4>

                            <p class="text-sm text-yellow-700">
                                <?= Yii::$app->session->getFlash('warning') ?>
                            </p>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

            <!-- =============================================
            | PAGE CONTENT
            ============================================== -->

            <div class="w-full max-w-full">

                <?= $content ?>

            </div>

        </div>

    </main>

</div>

<!-- =========================================================
| SIDEBAR TOGGLE SCRIPT
========================================================= -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (!sidebar || !toggle || !backdrop) {
        return;
    }

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }

    toggle.addEventListener('click', openSidebar);

    backdrop.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function () {

        if (window.innerWidth >= 1024) {
            backdrop.classList.add('hidden');
        }

    });

});
</script>

<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>