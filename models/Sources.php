<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sources".
 *
 * @property int $id
 * @property int|null $source_type
 * @property string|null $source_no
 * @property string|null $source_date
 * @property int|null $patient_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class Sources extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_type', 'source_no', 'source_date', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['source_type', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['source_date'], 'safe'],
            [['source_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_type' => 'Source Type',
            'source_no' => 'Source No',
            'source_date' => 'Source Date',
            'patient_id' => 'Patient ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\queries\SourcesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\SourcesQuery(get_called_class());
    }

    // Get Source Type Options
    public static function getSourceTypeOptions()
    {
        return [
            1 => 'Hospital',
            2 => 'Lab', 
            3 => 'Hospice',
        ];
    }

}
