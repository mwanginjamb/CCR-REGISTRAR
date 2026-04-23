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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?= Html::csrfMetaTags() ?>

    <title><?= Html::encode($this->title ?: Yii::$app->name) ?></title>

  

   

    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
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

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Yii2 ActiveForm Error Styling */
        .help-block,
        .invalid-feedback {
            @apply text-red-600 text-xs mt-1;
        }

        .has-error input,
        .has-error select,
        .has-error textarea {
            @apply ring-2 ring-red-500;
        }

        /* Breadcrumbs */
        .breadcrumb {
            @apply flex flex-wrap items-center gap-2 text-xs text-outline;
        }

        .breadcrumb li {
            @apply flex items-center gap-2;
        }

        .breadcrumb li+li::before {
            content: "chevron_right";
            font-family: 'Material Symbols Outlined';
            font-size: 14px;
        }
    </style>

    <?php $this->head() ?>
</head>

<body class="bg-background text-on-background min-h-screen flex flex-col">
    <?php $this->beginBody() ?>

    <!-- TOP NAVBAR -->
    <header
        class="bg-slate-50 flex justify-between items-center w-full px-4 md:px-6 py-3 h-16 fixed top-0 z-50 border-b border-surface-container-high">

        <!-- LEFT -->
        <div class="flex items-center gap-4 lg:gap-8">
            <a href="<?= Yii::$app->homeUrl ?>" class="text-xl font-bold text-primary tracking-tight">
                <?= Html::encode(Yii::$app->name) ?>
            </a>

            <!-- Search -->
            <div class="relative hidden sm:block">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                    search
                </span>

                <input type="text" placeholder="Search registry..."
                    class="pl-10 pr-4 py-2 bg-surface-container border-none rounded-xl text-sm w-48 lg:w-64 focus:ring-2 focus:ring-primary" />
            </div>
        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-2 md:gap-4">

            <button class="p-2 hover:bg-[#e0e3e5] transition-colors rounded-full text-primary">
                <span class="material-symbols-outlined">notifications</span>
            </button>

            <button class="p-2 hover:bg-[#e0e3e5] transition-colors rounded-full text-primary hidden sm:block">
                <span class="material-symbols-outlined">settings</span>
            </button>

            <!-- User Avatar -->
            <div class="h-8 w-8 rounded-full bg-primary-fixed overflow-hidden ml-1 md:ml-2">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode(Yii::$app->user->identity->username ?? 'User') ?>"
                    alt="Profile" class="w-full h-full object-cover" />
            </div>

        </div>
    </header>

    <!-- PAGE WRAPPER -->
    <div class="flex pt-16 flex-1 h-full">

        <!-- SIDEBAR -->
        <aside
            class="bg-[#f2f4f6] h-[calc(100vh-64px)] w-20 lg:w-64 fixed left-0 flex flex-col py-6 px-3 lg:px-4 space-y-2 transition-all duration-300 border-r border-surface-container-high z-40">

            <!-- BRAND -->
            <div class="mb-6 px-2 text-center lg:text-left">
                <h2 class="font-extrabold text-primary hidden lg:block">
                    KEMRI Registry
                </h2>

                <span class="material-symbols-outlined text-primary block lg:hidden">
                    clinical_notes
                </span>

                <p class="text-[10px] text-on-surface-variant hidden lg:block">
                    Precision Data Entry
                </p>
            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 space-y-1">

                <a href="<?= yii\helpers\Url::to(['/site/index']) ?>"
                    class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg">
                    <span class="material-symbols-outlined text-xl">dashboard</span>
                    <span class="text-sm font-medium hidden lg:block">Dashboard</span>
                </a>

                <a href="<?= yii\helpers\Url::to(['/patient/index']) ?>"
                    class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 bg-white text-primary font-bold rounded-lg shadow-[0_4px_12px_rgba(0,26,72,0.04)] lg:translate-x-1 transition-transform">
                    <span class="material-symbols-outlined text-xl">groups</span>
                    <span class="text-sm font-medium hidden lg:block">Patients</span>
                </a>

                <a href="<?= yii\helpers\Url::to(['/report/index']) ?>"
                    class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg">
                    <span class="material-symbols-outlined text-xl">assessment</span>
                    <span class="text-sm font-medium hidden lg:block">Reports</span>
                </a>

            </nav>

            <!-- FOOTER ACTIONS -->
            <div class="pt-4 border-t border-surface-container-high space-y-1">

                <a href="#"
                    class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg">
                    <span class="material-symbols-outlined text-xl">help</span>
                    <span class="text-sm font-medium hidden lg:block">Help Center</span>
                </a>

                <a href="<?= yii\helpers\Url::to(['/abstract/create']) ?>"
                    class="w-full mt-4 bg-primary text-white p-2.5 rounded-xl font-semibold text-sm hover:opacity-90 transition-opacity flex items-center justify-center">
                    <span class="material-symbols-outlined block lg:hidden">add</span>
                    <span class="hidden lg:block">New Abstract</span>
                </a>

            </div>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="ml-20 lg:ml-64 flex-1 p-4 md:p-8 bg-background overflow-x-hidden">

            <div class="max-w-7xl mx-auto">

                <!-- FLASH MESSAGES -->
                <!-- <div class="mb-6">
                    <?php Alert::widget() ?>
                </div> -->
                 <!-- Flash Alerts -->
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
            <?php
            // Map Yii flash types → Tailwind color tokens
            $alertConfig = match($type) { 
                'success' => ['bg' => 'bg-secondary-fixed',      'text' => 'text-on-secondary-fixed', 'icon' => 'check_circle',      'dot' => 'bg-primary'],
                'error',
                'danger'  => ['bg' => 'bg-error-container',      'text' => 'text-on-error-container', 'icon' => 'error',             'dot' => 'bg-error'],
                'warning' => ['bg' => 'bg-tertiary-fixed',        'text' => 'text-on-tertiary-fixed',  'icon' => 'warning',           'dot' => 'bg-tertiary'],
                'info'    => ['bg' => 'bg-primary-fixed',         'text' => 'text-on-primary-fixed',   'icon' => 'info',              'dot' => 'bg-primary'],
                default   => ['bg' => 'bg-surface-container-high','text' => 'text-on-surface-variant', 'icon' => 'notifications',     'dot' => 'bg-outline'],
            };
            $messages = (array) $messages;
            foreach ($messages as $message):
            ?>
            <div
                role="alert"
                class="flex items-start gap-4 mb-4 px-5 py-4 rounded-2xl <?= $alertConfig['bg'] ?> <?= $alertConfig['text'] ?> shadow-sm"
                x-data="{ show: true }"
            >
                <span class="material-symbols-outlined mt-0.5 flex-shrink-0"><?= $alertConfig['icon'] ?></span>
                <span class="flex-1 text-sm font-medium font-['Inter']"><?= Html::encode($message) ?></span>
                <!-- Dismiss button (purely CSS-driven; swap for JS if needed) -->
                <button
                    type="button"
                    class="ml-auto p-1 rounded-lg hover:bg-black/10 transition-colors flex-shrink-0"
                    onclick="this.closest('[role=alert]').remove()"
                    aria-label="Dismiss"
                >
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

         

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

                <!-- PAGE CONTENT -->
                <?= $content ?>

            </div>

        </main>

    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage(); ?>