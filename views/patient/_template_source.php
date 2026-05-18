<?php

use app\library\AuthUi;

?>
<div id="source-template" class="hidden">

    <div class="source-item border p-4 rounded-lg bg-white">

        <!-- HEADER ROW (title + delete button aligned) -->
        <div class="flex items-center justify-between mb-3">

            <span class="text-sm font-semibold text-on-surface-variant">
                Source Entry
            </span>

            <button type="button"
                class="remove-source flex items-center justify-center w-8 h-8 rounded-md hover:bg-red-50 text-red-500 transition">
                ✕
            </button>

        </div>

        <!-- FORM ROW -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?= $form->field(new \app\models\Sources(), "[__index__]source_type")->dropDownList(\app\models\Sources::getSourceTypeOptions(), ['class' => AuthUi::inputClass(), 'prompt' => 'Select Source Type'])->label(false) ?>
            <?= $form->field(new \app\models\Sources(), "[__index__]source_no")->textInput(['class' => AuthUi::inputClass(), 'placeholder' => 'Source No'])->label(false) ?>
            <?= $form->field(new \app\models\Sources(), "[__index__]source_date")->textInput(['class' => AuthUi::inputClass(), 'placeholder' => 'Source Date'])->label(false) ?>
        </div>

    </div>

</div>