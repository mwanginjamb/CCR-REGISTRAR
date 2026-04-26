<?php
use app\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="light">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>

    <?php $this->head() ?>
</head>

<body class="bg-background text-on-background min-h-screen flex flex-col">
<?php $this->beginBody() ?>

<!-- Top Navbar -->
<header
    class="bg-slate-50 flex justify-between items-center w-full px-4 md:px-6 py-3 h-16 fixed top-0 z-50 border-b border-surface-container-high">
    <div class="flex items-center gap-4 lg:gap-8">
        <span class="text-xl font-bold text-[#001a48] tracking-tight">
            OncoRegistry
        </span>
        <div class="relative hidden sm:block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                search
            </span>
            <input
                class="pl-10 pr-4 py-2 bg-surface-container border-none rounded-xl text-sm w-48 lg:w-64 focus:ring-2 focus:ring-primary"
                placeholder="Search registry..."
                type="text">
        </div>
    </div>

    <div class="flex items-center gap-2 md:gap-4">
        <button class="p-2 hover:bg-[#e0e3e5] rounded-full text-[#001a48]">
            <span class="material-symbols-outlined">notifications</span>
        </button>

        <button class="p-2 hover:bg-[#e0e3e5] rounded-full text-[#001a48] hidden sm:block">
            <span class="material-symbols-outlined">settings</span>
        </button>

        <div class="h-8 w-8 rounded-full bg-primary-fixed overflow-hidden ml-2">
            https://lh3.googleusercontent.com/aida-public/AB6AXuBvI0vRNcmqmeDLG0Tb_DpROZNAUFY5Y3N3noNLIUE1Y5MkHozpACU6oeqIy-2ZxkyeZf3r3KegzHmea5gTr_zGHUn3VmqbXOuiItsMmOpqTJCWP42vntdBsxzRibmfRKV9BWgZrMxqq2dBNcdQlu5ZQ0YglPEt2p7o8EkkVzfMrD1QyJaTWdFhHc3wwbwBGDPRUJffAuqbYwLK4VAk5VnBvFVUzz6QzFgEeKgK3SD42IHoBKSjOwhAEwHAR-uu-8GcUSPC-yGFhaI
        </div>
    </div>
</header>

<div class="flex pt-16 flex-1">

    <!-- Sidebar -->
    <aside
        class="bg-[#f2f4f6] h-[calc(100vh-64px)] w-20 lg:w-64 fixed left-0 flex flex-col py-6 px-3 lg:px-4 space-y-2 border-r border-surface-container-high z-40">

        <div class="mb-6 px-2 text-center lg:text-left">
            <h2 class="font-extrabold text-[#001a48] hidden lg:block">
                KEMRI Registry
            </h2>
            <span class="material-symbols-outlined text-primary block lg:hidden">
                clinical_notes
            </span>
            <p class="text-[10px] text-on-surface-variant hidden lg:block">
                Precision Data Entry
            </p>
        </div>

        <nav class="flex-1 space-y-1">
           <a class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg group"
                    href="#" title="Registry">
                    <span class="material-symbols-outlined text-xl" data-icon="clinical_notes">clinical_notes</span>
                    <span class="font-inter text-sm font-medium hidden lg:block">Registry</span>
                </a>
                <a class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 bg-white text-[#001a48] font-bold rounded-lg shadow-[0_4px_12px_rgba(0,26,72,0.04)] lg:translate-x-1 transition-transform"
                    href="#" title="Patients">
                    <span class="material-symbols-outlined text-xl" data-icon="groups">groups</span>
                    <span class="font-inter text-sm font-medium hidden lg:block">Patients</span>
                </a>
                <a class="flex items-center justify-center lg:justify-start gap-3 px-3 py-2 text-[#444651] hover:bg-[#eceef0] transition-colors rounded-lg group"
                    href="#" title="Reports">
                    <span class="material-symbols-outlined text-xl" data-icon="assessment">assessment</span>
                    <span class="font-inter text-sm font-medium hidden lg:block">Reports</span>
                </a>
        </nav>

        <div class="pt-4 border-t border-surface-container-high">
            <button class="w-full bg-primary text-white p-2.5 rounded-xl font-semibold">
                New Abstract
            </button>
        </div>
    </aside>

    <!-- Page Content -->
     <main class="ml-20 lg:ml-64 flex-1 p-4 md:p-8 bg-background">
    <?= $content ?>
</main>

</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>