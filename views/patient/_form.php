<?php

use yii\helpers\Html;
use yii\web\AssetBundle;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */
/** @var yii\bootstrap5\ActiveForm $form */

?>

<div class="patient-form">

    <div class="max-w-7xl mx-auto">

        <!-- PAGE HEADER -->
        <div class="mb-8 md:mb-10 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">

            <div>

                <h1 class="text-2xl md:text-3xl font-extrabold text-primary tracking-tight">
                    REG-2024-KEM-8842
                </h1>

                <p class="text-on-surface-variant text-xs md:text-sm mt-1">
                    Manual Abstract Record • Clinical Research Unit
                </p>

            </div>

            <div class="flex items-center">

                <span
                    class="px-3 py-1 bg-secondary-container text-on-secondary-container rounded-full text-[10px] md:text-xs font-bold">
                    DRAFT MODE
                </span>

            </div>

        </div>

        <!-- FORM + STEPPER -->
        <div class="flex flex-col lg:flex-row gap-6 md:gap-8">

            <!-- LEFT SIDEBAR -->
            <?php echo $this->render('_form_steps'); ?>

            <!-- RIGHT CONTENT -->
            <div class="w-full lg:w-3/4">

                <?php $form = ActiveForm::begin([
                    'options' => [
                        'class' => 'space-y-6 md:space-y-8'
                    ]
                ]); ?>

                <!-- SECTION 1 -->
                <section id="patient-information"
                    class="form-section scroll-mt-28 bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)]">

                    <div class="flex items-center gap-2 mb-6 border-b border-surface-container-high pb-4">

                        <span class="material-symbols-outlined text-primary">
                            person
                        </span>

                        <h3 class="text-base md:text-lg font-bold text-primary">
                            Patient Information
                        </h3>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

                        <div class="md:col-span-2">

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Full Name
                            </label>

                            <input type="text" placeholder="e.g. John Doe"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                        <div>

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                National ID / Passport
                            </label>

                            <input type="text"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                        <div>

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Telephone Number
                            </label>

                            <input type="tel"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                        <div>

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Current Age
                            </label>

                            <input type="number"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                        <div>

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Religion
                            </label>

                            <select
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all">
                                <option>Christian</option>
                                <option>Muslim</option>
                                <option>Hindu</option>
                                <option>Other</option>
                            </select>

                        </div>

                    </div>

                </section>

                <!-- SECTION 2 -->
                <section id="tumour-details"
                    class="form-section scroll-mt-28 bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)]">

                    <div class="flex items-center gap-2 mb-6 border-b border-surface-container-high pb-4">

                        <span class="material-symbols-outlined text-primary">
                            biotech
                        </span>

                        <h3 class="text-base md:text-lg font-bold text-primary">
                            Tumour Details
                        </h3>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">

                        <div>

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Incidence Date
                            </label>

                            <input type="date"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                        <div>

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Primary Site
                            </label>

                            <input type="text"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                        <div class="md:col-span-2">

                            <label
                                class="block text-[10px] md:text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                                Histology
                            </label>

                            <input type="text"
                                class="w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base focus:ring-2 focus:ring-primary transition-all" />

                        </div>

                    </div>

                </section>

                <!-- SECTION 3 -->
                <section id="treatment-followup"
                    class="form-section scroll-mt-28 bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)]">

                    <div class="flex items-center gap-2 mb-6 border-b border-surface-container-high pb-4">

                        <span class="material-symbols-outlined text-primary">
                            medical_services
                        </span>

                        <h3 class="text-base md:text-lg font-bold text-primary">
                            Treatment & Follow-up
                        </h3>

                    </div>

                    <div class="space-y-4">

                        <div
                            class="p-4 rounded-xl border-2 border-surface-container-high bg-white flex items-center justify-between">

                            <span class="text-sm font-semibold">
                                Surgery Performed
                            </span>

                            <input type="checkbox"
                                class="w-5 h-5 rounded-md border-outline-variant text-primary focus:ring-primary" />

                        </div>

                        <div
                            class="p-4 rounded-xl border-2 border-surface-container-high bg-white flex items-center justify-between">

                            <span class="text-sm font-semibold">
                                Chemotherapy
                            </span>

                            <input type="checkbox"
                                class="w-5 h-5 rounded-md border-outline-variant text-primary focus:ring-primary" />

                        </div>

                    </div>

                </section>

                <!-- ACTIONS -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 pt-4 pb-20">

                    <button type="button"
                        class="px-6 py-3 rounded-xl text-primary font-bold hover:bg-surface-container transition-colors flex items-center justify-center sm:justify-start gap-2">

                        <span class="material-symbols-outlined">
                            drafts
                        </span>

                        Save as Draft

                    </button>

                    <div class="flex gap-4">

                        <button type="button"
                            class="flex-1 sm:flex-none px-6 md:px-8 py-3 rounded-xl bg-surface-container-high text-outline font-bold hover:bg-surface-dim transition-colors">
                            Back
                        </button>

                        <button type="submit"
                            class="flex-1 sm:flex-none px-6 md:px-10 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-container text-white font-bold shadow-[0_8px_24px_rgba(0,26,72,0.2)] hover:scale-105 transition-transform">
                            Finalize Abstract
                        </button>

                    </div>

                </div>

                <?php ActiveForm::end(); ?>

            </div>

        </div>

    </div>

</div>

<?php
// import form css
$this->registerCssFile('@web/css/formPatient.css');
// import form js
$this->registerJsFile('@web/js/form.js', ['position' => \yii\web\View::POS_END]);
?>


