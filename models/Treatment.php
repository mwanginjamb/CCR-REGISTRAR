<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "treatment".
 *
 * @property int $id
 * @property string|null $treatment
 * @property int|null $treatment_status
 * @property string|null $treatment_date
 * @property int|null $patient_id
 * @property string|null $concurrent_illness
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class Treatment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'treatment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['treatment', 'treatment_status', 'treatment_date', 'patient_id', 'concurrent_illness', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['treatment_status', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['treatment_date'], 'safe'],
            [['concurrent_illness'], 'string'],
            [['treatment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'treatment' => 'Treatment',
            'treatment_status' => 'Treatment Status',
            'treatment_date' => 'Treatment Date',
            'patient_id' => 'Patient ID',
            'concurrent_illness' => 'Concurrent Illness',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\queries\TreatmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\TreatmentQuery(get_called_class());
    }

    // Get Treatment
    public static function getTreatment()
    {
        return [
            'surgery' => 'Surgery',
            'chemotherapy' => 'Chemotherapy',
            'immunotherapy' => 'Immunotherapy',
            'radiation' => 'Radiation',
            'other' => 'Other',
        ];
    }

    // Get Treatment Status
    public static function getTreatmentStatus()
    {
        return [
            1 => 'No',
            2 => 'Yes',
            3 => 'Unknown',
        ];
    }

}
