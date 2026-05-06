<?php

use app\library\AuthUi;

?>
<div id="treatment-template" class="hidden">

    <div class="treatment-item border p-4 rounded-lg bg-white">

        <!-- HEADER ROW (title + delete button aligned) -->
        <div class="flex items-center justify-between mb-3">

            <span class="text-sm font-semibold text-on-surface-variant">
                Treatment Entry
            </span>

            <button type="button"
                class="remove-treatment flex items-center justify-center w-8 h-8 rounded-md hover:bg-red-50 text-red-500 transition">
                ✕
            </button>

        </div>

        <!-- FORM ROW -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            <?= $form->field(new \app\models\Treatment(), "[__index__]treatment")
                ->dropDownList(\app\models\Treatment::getTreatment(), [
                    'class' => AuthUi::inputClass(),
                    'prompt' => 'Select Treatment'
                ]) ?>

            <?= $form->field(new \app\models\Treatment(), "[__index__]treatment_status")
                ->dropDownList(\app\models\Treatment::getTreatmentStatus(), [
                    'class' => AuthUi::inputClass(),
                    'prompt' => 'Select Status'
                ]) ?>

            <?= $form->field(new \app\models\Treatment(), "[__index__]treatment_date")
                ->textInput([
                    'type' => 'date',
                    'class' => AuthUi::inputClass()
                ]) ?>

        </div>

    </div>

</div>