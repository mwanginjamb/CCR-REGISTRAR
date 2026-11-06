<?php
/**
 * Guest Layout — OncoRegistry / Auth Shell
 *
 * Drop-in layout for all unauthenticated views (login, register, forgot-password, etc.)
 *
 * Configurable via $this->params in each view:
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  $this->params['card_title']     → Card heading (required)      │
 * │  $this->params['card_subtitle']  → Muted sub-heading (optional) │
 * │  $this->params['brand_icon']     → Material symbol name         │
 * │                                    default: 'clinical_notes'    │
 * │  $this->params['app_name']       → App display name             │
 * │                                    default: 'OncoRegistry'      │
 * │  $this->params['institution']    → Tagline below app name       │
 * │                                    default: 'Kenya Medical …'   │
 * └─────────────────────────────────────────────────────────────────┘
 *
 * Usage in a view file:
 * <?php
 *   $this->layout = 'guest';                          // point to this layout
 *   $this->title  = 'Login';
 *   $this->params['card_title']    = 'Researcher Login';
 *   $this->params['card_subtitle'] = 'Enter your credentials to access the registry.';
 * ?>
 */

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AuthAsset;
use yii\helpers\Html;

// Compiled Tailwind build (npm run build); see AuthAsset
AuthAsset::register($this);

// ── Layout-level defaults ────────────────────────────────────────────────────
$brandIcon = $this->params['brand_icon'] ?? 'clinical_notes';
$appName = $this->params['app_name'] ?? 'OncoRegistry';
$institution = $this->params['institution'] ?? 'Kenya Medical Research Institute';
$cardTitle = $this->params['card_title'] ?? $this->title ?? '';
$cardSubtitle = $this->params['card_subtitle'] ?? '';

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= Html::encode($this->title . ' — ' . $appName) ?></title>

    <?= $this->render('_pwaHead') ?>
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>

    <!-- Typefaces ─────────────────────────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <style>
        /* Icon rendering */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* Page-level ambient gradient */
        .guest-bg {
            background: linear-gradient(135deg, #f2f4f6 0%, #eceef0 100%);
        }

        /* CTA button gradient */
        .btn-gradient {
            background: linear-gradient(to bottom right, #001a48, #002d72);
        }

        /* Frost-glass card variant — available for use in $content views */
        .glass-panel {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(20px);
        }

        /* Input icon-group: icon colour shifts on field focus */
        .input-group:focus-within .input-icon {
            color: #001a48;
            /* --primary */
        }
    </style>
</head>

<body class="guest-bg bg-background font-body text-on-surface
             flex min-h-screen items-center justify-center
             p-6 md:p-12">

    <?php $this->beginBody() ?>

    <!-- ── Ambient depth glows (fixed, behind everything) ──────────────── -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden opacity-5" aria-hidden="true">
        <div class="absolute top-1/4 -left-20 w-96 h-96
                bg-primary rounded-full blur-[100px]"></div>
        <div class="absolute bottom-1/4 -right-20 w-80 h-80
                bg-secondary rounded-full blur-[80px]"></div>
    </div>

    <!-- ── Page shell ──────────────────────────────────────────────────── -->
    <main class="w-full max-w-md md:max-w-lg lg:max-w-md transition-all duration-300">

        <!-- Brand identity ──────────────────────────────────────────── -->
        <a href="<?= Html::encode(Yii::$app->homeUrl) ?>" title="Home"
           class="flex flex-col items-center mb-10 md:mb-12 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            <div class="w-16 h-16 md:w-20 md:h-20
                    bg-primary-container rounded-xl
                    flex items-center justify-center mb-4
                    shadow-[0_12px_32px_rgba(0,26,72,0.10)]">
                <span class="material-symbols-outlined text-white text-3xl md:text-4xl">
                    <?= Html::encode($brandIcon) ?>
                </span>
            </div>
            <h1 class="font-headline font-extrabold text-3xl md:text-4xl
                   text-primary tracking-tight">
                <?= Html::encode($appName) ?>
            </h1>
            <p class="font-label text-on-surface-variant text-sm md:text-base mt-1">
                <?= Html::encode($institution) ?>
            </p>
        </a>

        <!-- ── Auth card ───────────────────────────────────────────── -->
        <div class="bg-surface-container-lowest rounded-xl p-8 md:p-10
                shadow-[0_12px_32px_rgba(0,26,72,0.06)]
                relative overflow-hidden">

            <!-- Asymmetric decorative glow -->
            <div class="absolute top-0 right-0 w-32 h-32
                    bg-secondary-container/10
                    -mr-16 -mt-16 rounded-full blur-3xl" aria-hidden="true"></div>

            <!-- Card header — driven by view params -->
            <?php if ($cardTitle): ?>
                <header class="mb-8 md:mb-10">
                    <h2 class="font-headline font-bold text-xl md:text-2xl text-primary text-center">
                        <?= Html::encode($cardTitle) ?>
                    </h2>
                    <?php if ($cardSubtitle): ?>
                        <p class="font-body text-on-surface-variant text-sm md:text-base mt-1">
                            <?= Html::encode($cardSubtitle) ?>
                        </p>
                    <?php endif ?>
                </header>
            <?php endif ?>

            <!-- ── View content injected here ─────────────────────── -->
            <?= $content ?>
            <!-- ─────────────────────────────────────────────────────── -->

        </div>

        <!-- ── Footer / legal ──────────────────────────────────────── -->
        <footer class="mt-12 md:mt-16 text-center">
            <p class="font-label text-[10px] md:text-[11px] text-outline
                  uppercase tracking-[0.2em] leading-relaxed">
                Protected by Clinical-Grade Security<br />
                &copy; <?= date('Y') ?> <?= Html::encode($institution) ?>
            </p>
        </footer>

    </main>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>