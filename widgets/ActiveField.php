<?php

namespace app\widgets;

use app\library\OptionLabel;
use yii\helpers\ArrayHelper;

/**
 * Application ActiveField.
 *
 * Registered in config/web.php as the container definition for
 * yii\widgets\ActiveField, so every yii\widgets\ActiveForm uses it.
 *
 * dropDownList() and listBox() render option labels as "key - label" (see OptionLabel).
 * Opt out per field where the key means nothing to the user:
 *
 *   $form->field($m, 'treatment')->dropDownList($items, ['showKeys' => false])
 */
class ActiveField extends \yii\widgets\ActiveField
{
    public function dropDownList($items, $options = [])
    {
        return parent::dropDownList($this->formatItems($items, $options), $options);
    }

    public function listBox($items, $options = [])
    {
        return parent::listBox($this->formatItems($items, $options), $options);
    }

    private function formatItems(array $items, array &$options): array
    {
        $showKeys = ArrayHelper::remove($options, 'showKeys', true);

        return $showKeys ? OptionLabel::withKeys($items) : $items;
    }
}
