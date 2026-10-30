<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "source_reference".
 *
 * Setup list of known sources offered as "Source No" suggestions on the patient
 * form, grouped by source type (Sources::getSourceTypeOptions()).
 *
 * @property int $id
 * @property int $source_type
 * @property string $source_no
 * @property string $name
 * @property string|null $redcap_value
 * @property int $is_active
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class SourceReference extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'source_reference';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_type', 'source_no', 'name'], 'required'],
            [['source_no', 'name', 'redcap_value'], 'trim'],
            ['source_type', 'in', 'range' => array_keys(Sources::getSourceTypeOptions())],
            [['source_no', 'redcap_value'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 255],
            ['is_active', 'boolean'],
            ['is_active', 'default', 'value' => 1],
            [
                'source_no',
                'unique',
                'targetAttribute' => ['source_type', 'source_no'],
                'message' => 'This source number already exists for the selected source type.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'source_type' => Yii::t('app', 'Source Category'),
            'source_no' => Yii::t('app', 'Source No'),
            'name' => Yii::t('app', 'Name'),
            'redcap_value' => Yii::t('app', 'REDCap Value'),
            'is_active' => Yii::t('app', 'Active'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Active entries grouped by source type, for the form's suggestion lists.
     *
     * @return array source_type => [source_no => name]
     */
    public static function optionsByType(): array
    {
        $grouped = [];

        $rows = self::find()
            ->select(['source_type', 'source_no', 'name'])
            ->where(['is_active' => 1])
            ->orderBy(['source_type' => SORT_ASC, 'source_no' => SORT_ASC])
            ->asArray()
            ->all();

        // source_no is a string: "02" and "2" must not collapse, and "100" sorts after "99"
        usort($rows, static fn($a, $b) => [$a['source_type'], (int) $a['source_no'], $a['source_no']]
            <=> [$b['source_type'], (int) $b['source_no'], $b['source_no']]);

        foreach ($rows as $row) {
            $grouped[(int) $row['source_type']][(string) $row['source_no']] = $row['name'];
        }

        return $grouped;
    }

    /**
     * Name of a source entry, or null when the number is not in the setup list.
     */
    public static function nameFor($sourceType, $sourceNo, ?array $optionsByType = null): ?string
    {
        $optionsByType ??= self::optionsByType();

        return $optionsByType[(int) $sourceType][(string) $sourceNo] ?? null;
    }
}
