<?php

use yii\helpers\Html;

/**
 * Cancel / Save buttons shared by the Settings forms.
 *
 * @var yii\web\View $this
 */
?>
<div class="flex items-center justify-end gap-3 mt-8">
    <?= Html::a('Cancel', ['index'], [
        'class' => 'px-6 py-3 rounded-xl bg-surface-container-high text-outline font-bold hover:bg-surface-dim transition-colors',
    ]) ?>
    <?= Html::submitButton(Yii::t('app', 'Save'), [
        'class' => 'px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-[#002d72] text-white font-bold hover:opacity-90 transition-opacity shadow-md',
    ]) ?>
</div>
