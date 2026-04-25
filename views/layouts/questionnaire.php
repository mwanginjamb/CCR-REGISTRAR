<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\bootstrap5\Alert;

use app\assets\AuthAsset;

AuthAsset::register($this);
$this->beginPage();

?>
<!DOCTYPE html>
<html class="light" lang="<?= Yii::$app->language ?>">

<head>

    <meta charset="<?= Yii::$app->charset ?>">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    />

    <?= Html::csrfMetaTags() ?>

    <title><?= Html::encode($this->title ?: Yii::$app->name) ?></title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet"
    />

    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    />

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {

                    colors: {

                        "primary": "#001a48",
                        "primary-container": "#002d72",
                        "primary-fixed": "#dae2ff",

                        "secondary": "#0d6683",
                        "secondary-container": "#98deff",

                        "background": "#f7f9fb",

                        "surface": "#f7f9fb",
                        "surface-container": "#eceef0",
                        "surface-container-low": "#f2f4f6",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e6e8ea",
                        "surface-container-highest": "#e0e3e5",

                        "surface-dim": "#d8dadc",

                        "outline": "#747782",
                        "outline-variant": "#c4c6d2",

                        "on-background": "#191c1e",
                        "on-surface": "#191c1e",
                        "on-surface-variant": "#444651",

                        "on-primary": "#ffffff",

                        "on-secondary-container": "#056380",

                    },

                    borderRadius: {

                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"

                    },

                    fontFamily: {

                        headline: ["Manrope"],
                        body: ["Inter"],
                        label: ["Inter"]

                    }

                }
            }
        }
    </script>

    <style>

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Manrope', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }

        /* Hide Scrollbars */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Active Step */
        .active-step {
            background: white;
            border-left: 4px solid #001a48;
            box-shadow: 0 12px 32px rgba(0, 26, 72, 0.06);
        }

        .active-step .step-number {
            background: #001a48;
            color: white;
        }

        .active-step .step-title {
            color: #001a48;
            font-weight: 700;
        }

        /* Inactive Step */
        .inactive-step {
            background: #eceef0;
            opacity: 0.75;
        }

        .inactive-step .step-number {
            background: #e0e3e5;
            color: #747782;
        }

        /* Yii Validation */
        .help-block,
        .invalid-feedback {
            font-size: 12px;
            color: #ba1a1a;
            margin-top: 4px;
        }

        .has-error input,
        .has-error select,
        .has-error textarea {
            border-color: #ba1a1a;
            ring: 2px;
            ring-color: #ba1a1a;
        }

        /* Breadcrumbs */
        .breadcrumb {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            font-size: 12px;
            color: #747782;
        }

        .breadcrumb li {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .breadcrumb li + li::before {
            content: "chevron_right";
            font-family: 'Material Symbols Outlined';
            font-size: 14px;
        }

    </style>

    <?php $this->head() ?>

</head>

<body class="bg-background text-on-background min-h-screen">

<?php $this->beginBody() ?>

<!-- APP SHELL -->
<div class="min-h-screen flex flex-col">

    <!-- TOP NAVBAR -->
    <header
        class="fixed top-0 left-0 right-0 z-50 h-16 bg-slate-50 border-b border-surface-container-high"
    >

        <div class="h-full px-4 md:px-6 flex items-center justify-between">

            <!-- LEFT -->
            <div class="flex items-center gap-4 lg:gap-8">

                <!-- BRAND -->
                <a
                    href="<?= Yii::$app->homeUrl ?>"
                    class="text-xl font-bold text-primary tracking-tight"
                >
                    <?= Html::encode(Yii::$app->name ?: 'OncoRegistry') ?>
                </a>

                <!-- SEARCH -->
                <div class="relative hidden md:block">

                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                        search
                    </span>

                    <input
                        type="text"
                        placeholder="Search registry..."
                        class="pl-10 pr-4 py-2 bg-surface-container border-none rounded-xl text-sm w-56 lg:w-72 focus:ring-2 focus:ring-primary"
                    />

                </div>

            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-2 md:gap-4">

                <button
                    type="button"
                    class="p-2 hover:bg-surface-container-high rounded-full transition-colors text-primary"
                >
                    <span class="material-symbols-outlined">
                        notifications
                    </span>
                </button>

                <button
                    type="button"
                    class="hidden sm:flex p-2 hover:bg-surface-container-high rounded-full transition-colors text-primary"
                >
                    <span class="material-symbols-outlined">
                        settings
                    </span>
                </button>

                <!-- AVATAR -->
                <div class="h-9 w-9 rounded-full overflow-hidden bg-primary-fixed">

                    <img
                        src="https://ui-avatars.com/api/?name=<?= urlencode(Yii::$app->user->identity->username ?? 'User') ?>"
                        alt="Profile"
                        class="w-full h-full object-cover"
                    />

                </div>

            </div>

        </div>

    </header>

    <!-- BODY -->
    <div class="flex flex-1 pt-16">

        <!-- SIDEBAR -->
        <aside
            class="fixed left-0 top-16 bottom-0 z-40 w-20 lg:w-64 bg-surface-container-low border-r border-surface-container-high"
        >

            <div class="h-full flex flex-col px-3 lg:px-4 py-6">

                <!-- BRAND -->
                <div class="mb-6 px-2">

                    <div class="hidden lg:block">

                        <h2 class="font-extrabold text-primary">
                            KEMRI Registry
                        </h2>

                        <p class="text-[10px] text-on-surface-variant mt-1">
                            Precision Data Entry
                        </p>

                    </div>

                    <div class="lg:hidden flex justify-center">

                        <span class="material-symbols-outlined text-primary">
                            clinical_notes
                        </span>

                    </div>

                </div>

                <!-- NAV -->
                <nav class="flex-1 space-y-1">

                    <a
                        href="<?= yii\helpers\Url::to(['/site/index']) ?>"
                        class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 rounded-lg hover:bg-surface-container transition-colors text-on-surface-variant"
                    >

                        <span class="material-symbols-outlined text-xl">
                            dashboard
                        </span>

                        <span class="hidden lg:block text-sm font-medium">
                            Dashboard
                        </span>

                    </a>

                    <a
                        href="<?= yii\helpers\Url::to(['/patient/index']) ?>"
                        class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 rounded-lg bg-white shadow-[0_4px_12px_rgba(0,26,72,0.04)] text-primary font-bold"
                    >

                        <span class="material-symbols-outlined text-xl">
                            groups
                        </span>

                        <span class="hidden lg:block text-sm font-medium">
                            Patients
                        </span>

                    </a>

                    <a
                        href="<?= yii\helpers\Url::to(['/report/index']) ?>"
                        class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 rounded-lg hover:bg-surface-container transition-colors text-on-surface-variant"
                    >

                        <span class="material-symbols-outlined text-xl">
                            assessment
                        </span>

                        <span class="hidden lg:block text-sm font-medium">
                            Reports
                        </span>

                    </a>

                </nav>

                <!-- FOOTER -->
                <div class="pt-4 border-t border-surface-container-high space-y-2">

                    <a
                        href="#"
                        class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 rounded-lg hover:bg-surface-container transition-colors text-on-surface-variant"
                    >

                        <span class="material-symbols-outlined text-xl">
                            help
                        </span>

                        <span class="hidden lg:block text-sm font-medium">
                            Help Center
                        </span>

                    </a>

                    <a
                        href="<?= yii\helpers\Url::to(['/patient/create']) ?>"
                        class="w-full bg-primary text-white p-2.5 rounded-xl font-semibold text-sm hover:opacity-90 transition-opacity flex items-center justify-center"
                    >

                        <span class="material-symbols-outlined lg:hidden">
                            add
                        </span>

                        <span class="hidden lg:block">
                            New Site
                        </span>

                    </a>

                </div>

            </div>

        </aside>

        <!-- CONTENT -->
        <main
            class="flex-1 ml-20 lg:ml-64 p-4 md:p-8 bg-background overflow-x-hidden"
        >

            <div class="max-w-7xl mx-auto">

                <!-- ALERTS -->
                <div class="mb-6">
                    <?= Alert::widget() ?>
                </div>

                <!-- BREADCRUMBS -->
                <?php if (!empty($this->params['breadcrumbs'])): ?>

                    <div class="mb-6">

                        <?= Breadcrumbs::widget([
                            'links' => $this->params['breadcrumbs'],
                            'options' => [
                                'class' => 'breadcrumb'
                            ],
                        ]) ?>

                    </div>

                <?php endif; ?>

                <!-- VIEW CONTENT -->
                <?= $content ?>

            </div>

        </main>

    </div>

</div>

<?php $this->endBody() ?>

</body>

</html>

<?php $this->endPage() ?>