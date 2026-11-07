<?php

namespace app\services;

use app\models\RedcapDictionaryField;
use app\models\RedcapFieldReferenceSet;
use app\models\ReferenceOption;
use yii\helpers\ArrayHelper;

/**
 * Reads coded option lists and field labels from the imported REDCap data dictionary,
 * so the app stores exactly the codes a REDCap export expects.
 *
 * Results are memoised per request: a patient form asks for the same lists many
 * times (dropdowns, validation rules, one status list per treatment row).
 */
class Lookup
{
    /** @var array<string, RedcapDictionaryField|null> */
    private static array $fields = [];

    /** @var array<string, array> */
    private static array $options = [];

    /**
     * Choice list of a dictionary field, as [code => label] in dictionary order.
     * Empty when the field is unknown or has no choices.
     */
    public static function fieldOptions(string $fieldName): array
    {
        if (array_key_exists($fieldName, self::$options)) {
            return self::$options[$fieldName];
        }

        $field = self::field($fieldName);

        $mapping = $field
            ? RedcapFieldReferenceSet::find()->where(['field_id' => $field->id])->one()
            : null;

        if (!$mapping) {
            return self::$options[$fieldName] = [];
        }

        return self::$options[$fieldName] = ArrayHelper::map(
            ReferenceOption::find()
                ->where([
                    'reference_set_id' => $mapping->reference_set_id,
                    'is_active' => 1,
                ])
                ->orderBy(['sort_order' => SORT_ASC])
                ->all(),
            'value',
            'label'
        );
    }

    /**
     * Label of a dictionary field (e.g. "Hormone therapy"), or null when unknown.
     */
    public static function fieldLabel(string $fieldName): ?string
    {
        $label = self::field($fieldName)?->field_label;

        return $label === null || trim($label) === '' ? null : trim($label);
    }

    /**
     * Forget memoised lookups, e.g. after importing a new dictionary in the same request.
     */
    public static function clearCache(): void
    {
        self::$fields = [];
        self::$options = [];
    }

    private static function field(string $fieldName): ?RedcapDictionaryField
    {
        if (!array_key_exists($fieldName, self::$fields)) {
            // Several dictionary imports can define the same field; use the newest completed one
            self::$fields[$fieldName] = RedcapDictionaryField::find()
                ->alias('f')
                ->innerJoin('{{%redcap_import_run}} r', 'r.id = f.import_run_id')
                ->where(['f.field_name' => $fieldName, 'r.status' => 'completed'])
                ->orderBy(['f.import_run_id' => SORT_DESC])
                ->one();
        }

        return self::$fields[$fieldName];
    }
}
