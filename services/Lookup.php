<?php

namespace app\services;

use app\models\RedcapDictionaryField;
use app\models\RedcapFieldReferenceSet;
use app\models\ReferenceOption;
use app\models\ReferenceSet;
use yii\helpers\ArrayHelper;

class Lookup
{
    public static function fieldOptions(string $fieldName): array
    {
        $field = RedcapDictionaryField::find()
            ->where(['field_name' => $fieldName])
            ->one();

        if (!$field) {
            return [];
        }

        $mapping = RedcapFieldReferenceSet::find()
            ->where(['field_id' => $field->id])
            ->one();

        if (!$mapping) {
            return [];
        }

        return ArrayHelper::map(
            ReferenceOption::find()
                ->where([
                    'reference_set_id' => $mapping->reference_set_id
                ])
                ->orderBy(['sort_order' => SORT_ASC])
                ->all(),
            'value',
            'label'
        );
    }
}