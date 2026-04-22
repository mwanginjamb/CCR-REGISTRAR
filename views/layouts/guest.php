<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AuthAsset;

/** @var yii\web\View $this */
/** @var string $content */

AuthAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html class="light" lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?= Html::csrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <?php $this->head() ?>
</head>

<body class="
    bg-surface-container-low
    font-body
    text-on-surface
    min-h-screen
    flex
    items-center
    justify-center
    p-4
    md:p-8
    lg:p-12
    selection:bg-secondary-container
    selection:text-on-secondary-container
">

<?php $this->beginBody() ?>

<!-- Decorative Clinical Element -->
<div class="fixed top-8 right-8 hidden xl:block">
    <div class="bg-surface-variant/70 backdrop-blur-xl p-4 rounded-xl border border-white/20 clinical-shadow max-w-[200px]">

        <div class="flex items-center gap-3 mb-3">
            <div class="w-2 h-2 rounded-full bg-secondary"></div>

            <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                System Status
            </span>
        </div>

        <div class="space-y-2">
            <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden">
                <div class="h-full bg-secondary w-full"></div>
            </div>

            <p class="text-[11px] font-medium text-secondary">
                Registry nodes operational
            </p>
        </div>
    </div>
</div>

<!-- Decorative Left Element -->
<div class="fixed bottom-12 left-12 hidden xl:block opacity-20">
    <?= Html::img(
        '@web/images/auth-medical-visualization.jpg',
        [
            'class' => 'w-48 h-48 object-cover rounded-3xl',
            'alt' => 'Medical data visualization',
        ]
    ) ?>
</div>

<main class="w-full max-w-xl mx-auto">

    <!-- Brand Identity -->
    <div class="mb-8 md:mb-10 text-center">

        <div class="inline-flex items-center justify-center mb-4 md:mb-6">
            <div class="w-12 h-12 bg-primary-container rounded-xl flex items-center justify-center">

                <span class="material-symbols-outlined text-on-primary-container text-3xl">
                    clinical_notes
                </span>

            </div>
        </div>

        <h1 class="font-headline font-extrabold text-3xl md:text-4xl text-primary tracking-tight mb-2">
            <?= Html::encode(Yii::$app->name) ?>
        </h1>

        <p class="text-on-surface-variant font-medium text-base md:text-lg">
            National Precision Oncology Data Network
        </p>
    </div>

    <!-- Auth Card -->
    <div class="
        bg-surface-container-lowest
        clinical-shadow
        rounded-xl
        p-6
        md:p-10
        border-t-4
        border-primary
        w-full
    ">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="mb-6 rounded-lg bg-green-100 border border-green-200 text-green-800 px-4 py-3">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="mb-6 rounded-lg bg-red-100 border border-red-200 text-red-800 px-4 py-3">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?= $content ?>

    </div>

    <!-- Compliance Footer -->
    <div class="mt-8 md:mt-12 flex flex-col items-center gap-6">

        <div class="
            flex
            flex-wrap
            justify-center
            gap-x-6
            md:gap-x-8
            gap-y-4
            opacity-60
            grayscale
            hover:grayscale-0
            transition-all
            duration-500
        ">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-2xl">
                    verified_user
                </span>

                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    HIPAA Compliant
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-2xl">
                    security
                </span>

                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    256-bit Encrypted
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-2xl">
                    gavel
                </span>

                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                    KHRO Certified
                </span>
            </div>
        </div>

        <p class="text-outline text-[11px] text-center leading-relaxed max-w-sm px-4">
            By continuing, you agree to the Ministry of Health Data Privacy
            Protocols and Ethics Review Board guidelines.
        </p>

    </div>

</main>

<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>