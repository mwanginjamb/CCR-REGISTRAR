<?php

namespace app\models;

use Yii;

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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['behaviour_code', 'source_version'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => 1],
            [['redcap_value', 'code', 'description', 'created_at', 'updated_at'], 'required'],
            [['description'], 'string'],
            [['is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'redcap_value' => 'Redcap Value',
            'code' => 'Code',
            'description' => 'Description',
            'behaviour_code' => 'Behaviour Code',
            'source_version' => 'Source Version',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
