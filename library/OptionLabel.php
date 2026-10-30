<?php

namespace app\library;

use Yii;

/**
 * Formats dropdown option labels as "key - label" so data entry staff see the
 * same codes printed on the paper CRF (e.g. "3 - Malignant").
 *
 * The format comes from params['dropdown.labelTemplate'] and defaults to
 * '{key} - {label}'.
 *
 * Usage:
 *   OptionLabel::withKeys([3 => 'Malignant'])   // [3 => '3 - Malignant']
 *   OptionLabel::label('C50.9', 'Breast, NOS')  // 'C50.9 - Breast, NOS'
 */
class OptionLabel
{
    public const DEFAULT_TEMPLATE = '{key} - {label}';

    public static function label($key, string $label): string
    {
        $template = Yii::$app->params['dropdown.labelTemplate'] ?? self::DEFAULT_TEMPLATE;

        return strtr($template, [
            '{key}' => (string) $key,
            '{label}' => $label,
        ]);
    }

    /**
     * Applies label() to every item. Nested arrays are treated as optgroups,
     * matching the structure Html::dropDownList() accepts.
     */
    public static function withKeys(array $items): array
    {
        $formatted = [];

        foreach ($items as $key => $label) {
            $formatted[$key] = is_array($label)
                ? self::withKeys($label)
                : self::label($key, (string) $label);
        }

        return $formatted;
    }
}
