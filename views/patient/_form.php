<?php

use yii\helpers\Html;
use yii\web\AssetBundle;
use yii\widgets\ActiveForm;
use app\library\AuthUi;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */
/** @var yii\bootstrap5\ActiveForm $form */

?>



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

        <!-- MOBILE STEPPER -->
        <?= $this->render('_form_steps_mobile'); ?>

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
                           <?=$form->field($model, 'full_name')->textInput(['placeholder' => 'Enter full name', 'class' => \app\library\AuthUi::inputClass()])?>
                        </div>

                        <div>
                            <?=$form->field($model, 'national_id')->textInput(['placeholder' => 'Enter national ID / Passport', 'class' => \app\library\AuthUi::inputClass()])?>
                        </div>

                        <div>

                            <?= $form->field($model, 'telephone_no_nok')->textInput(['placeholder' => 'Enter telephone number of next of kin', 'class' => \app\library\AuthUi::inputClass()]) ?>
                        </div>

                        <div>

                            <?= $form->field($model, 'date_of_birth')->textInput(['placeholder' => 'Enter date of birth', 'class' => \app\library\AuthUi::inputClass(), 'type' => 'date']) ?>
                        </div>

                        <div>

                            <?= $form->field($model, 'age')->textInput([ 'class' => \app\library\AuthUi::inputClass(), 'readonly' => true]) ?>
                        </div>
                        
                        <div>
                            <?= $form->field($model, 'place_of_birth')->textInput([ 'class' => \app\library\AuthUi::inputClass(), 'readonly' => true]) ?>
                        </div>

                        <div>
                            <?= $form->field($model, 'ethnic_group')->dropDownList(\app\models\Patient::getEthnicGroups(), [ 'class' => \app\library\AuthUi::inputClass(), 'readonly' => true]) ?>
                        </div>

                        <div>
                            <?= $form->field($model, 'religion')->dropDownList(\app\models\Patient::getReligions(), [ 'class' => \app\library\AuthUi::inputClass(), 'readonly' => true]) ?>
                        </div>


                    </div>

                </section>

               
<?php ActiveForm::end(); ?>
<?php $form = ActiveForm::begin(\app\library\AuthUi::formConfig('form-tumour')); ?>
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

                           <?= $form->field($modelTumour,'incident_date')->textInput(['class' => AuthUi::inputClass(), 'type' => 'date']) ?>
                        </div>

                        <div>

                           <?= $form->field($modelTumour,'basis_of_diagnosis')->dropDownList(\app\models\Tumour::getBasisOfDiagnosis(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Basis of Diagnosis']) ?>
                        </div>

                        <div>
                            <?= $form->field($modelTumour,'primary_site')->dropDownList(\app\models\Tumour::getPrimarySites(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Primary Site']) ?>
                        </div>

                        <!-- Laterality -->
                        <div>
                            <?= $form->field($modelTumour,'laterality')->dropDownList(\app\models\Tumour::getLaterality(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Laterality']) ?>
                        </div>

                        <!-- Histology -->
                        <div>
                            <?= $form->field($modelTumour,'histology')->dropDownList(\app\models\Tumour::getHistology(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Histology']) ?>
                        </div>
                        <!-- Behaviour -->
                        <div>
                            <?= $form->field($modelTumour,'behaviour')->dropDownList(\app\models\Tumour::getBehaviour(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Behaviour']) ?>
                        </div>

                        <!-- Grade -->
                        <div>
                            <?= $form->field($modelTumour,'grade')->dropDownList(\app\models\Tumour::getGrade(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Grade']) ?>
                        </div>

                        <!-- Stage -->
                        <div>
                            <?= $form->field($modelTumour,'stage')->dropDownList(\app\models\Tumour::getStage(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Stage']) ?>
                        </div>

                        <!-- Full TNM Available: Bolean -->

                        <div>
                            <?= $form->field($modelTumour,'full_tnm')->checkbox(AuthUi::checkboxConfig('Full TNM Available ?')) ?>
                        </div>

                        <!-- T value for TNM -->
                        <div>
                            <?= $form->field($modelTumour,'t')->textInput(['class' => AuthUi::inputClass(),'placeholder' => 'Enter T value']) ?>
                        </div>
                        
                        <!-- N value for TNM -->
                        <div>
                            <?= $form->field($modelTumour,'n')->textInput(['class' => AuthUi::inputClass(),'placeholder' => 'Enter N value']) ?>
                        </div>
                        
                        <!-- M value for TNM -->
                        <div>
                            <?= $form->field($modelTumour,'m')->textInput(['class' => AuthUi::inputClass(),'placeholder' => 'Enter M value']) ?>
                        </div>

                        <!-- Essential TNM Fields : FILLD in absence of full TNM -->
                        
                        <!-- Add a border and necessary text legend -->
                        <div class="border border-surface-container-high rounded-lg p-4 col-span-2 my-4" id="essential-tnm-fields">
                            <h3 class="text-lg font-semibold text-primary mb-4">Essential TNM Fields</h3>
                            <!-- Add your essential TNM fields here -->

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                                    <!-- metastasis -->
                                    <div>
                                        <?= $form->field($modelTumour,'metastasis')->dropDownList(\app\models\Tumour::getMetastasis(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Metastasis']) ?>
                                    </div>

                                    <!-- regional_nodes_involvement -->
                                    <div>
                                        <?= $form->field($modelTumour,'regional_nodes_involvement')->dropDownList(\app\models\Tumour::getRegionalNodesInvolvement(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Regional Nodes Involvement']) ?>
                                    </div>

                                    <!-- localized_advanced -->
                                    <div>
                                        <?= $form->field($modelTumour,'localized_advanced')->dropDownList(\app\models\Tumour::getLocalizedAdvanced(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Localized Advanced']) ?>
                                    </div>

                                    <!-- localized_limited -->
                                    <div>
                                        <?= $form->field($modelTumour,'localized_limited')->dropDownList(\app\models\Tumour::getLocalizedLimited(), ['class' => AuthUi::inputClass(),'prompt' => 'Select Localized Limited']) ?>
                                    </div>
                            
                                </div>

                        </div>

                    </div>

                </section> 

                <?php ActiveForm::end(); ?>

                <?php $form = ActiveForm::begin(\app\library\AuthUi::formConfig('form-treatment')); ?>

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

                <?php ActiveForm::end(); ?>

                

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

               

            </div>

        </div>

    </div>



<?php
// import form css
$this->registerCssFile('@web/css/formPatient.css');
// import form js
$this->registerJsFile('@web/js/form.js');

// add js for essential tnm fields
$this->registerJsFile('@web/js/essentialTnmFields.js');
?>


