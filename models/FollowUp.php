<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "follow_up".
 *
 * @property int $id
 * @property int|null $present_status
 * @property string|null $cause_of_death
 * @property string|null $last_date_of_contact
 * @property string|null $remarks
 * @property int|null $patient_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class FollowUp extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'follow_up';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['present_status', 'cause_of_death', 'last_date_of_contact', 'remarks', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['present_status', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['cause_of_death', 'remarks'], 'string'],
            [['last_date_of_contact'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'present_status' => 'Present Status',
            'cause_of_death' => 'Cause Of Death',
            'last_date_of_contact' => 'Last Date Of Contact',
            'remarks' => 'Remarks',
            'patient_id' => 'Patient ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\queries\FollowUpQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\FollowUpQuery(get_called_class());
    }

}
