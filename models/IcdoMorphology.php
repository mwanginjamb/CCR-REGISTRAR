<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "icdo_morphology".
 *
 * @property int $id
 * @property string $redcap_value
 * @property string $code
 * @property string $description
 * @property string|null $behaviour_code
 * @property string|null $source_version
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class IcdoMorphology extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'icdo_morphology';
    }

    /**
     * created_at / updated_at are DATETIME columns (the importer writes them as
     * 'Y-m-d H:i:s'), so the timestamp value is a SQL expression, not time().
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'description', 'redcap_value', 'source_version'], 'trim'],
            [['behaviour_code', 'source_version'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => 1],
            [['redcap_value', 'code', 'description'], 'required'],
            ['code', 'match', 'pattern' => '/^\d{4}$/', 'message' => '{attribute} must be a 4-digit ICD-O morphology code, e.g. 8140.'],
            // Behaviour codes from the REDCap dictionary: 0 Benign, 1 Uncertain, 2 In Situ, 3 Malignant
            ['behaviour_code', 'in', 'range' => fn() => array_map('strval', array_keys(Tumour::getBehaviour()))],
            [['description'], 'string'],
            [['is_active'], 'boolean'],
            [['redcap_value'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 10],
            [['behaviour_code'], 'string', 'max' => 5],
            [['source_version'], 'string', 'max' => 50],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'redcap_value' => 'REDCap Value',
            'code' => 'Code',
            'description' => 'Description',
            'behaviour_code' => 'Behaviour',
            'source_version' => 'Source Version',
            'is_active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
