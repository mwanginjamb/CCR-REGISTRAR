<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "patient".
 *
 * @property int $id
 * @property string|null $full_name
 * @property string|null $national_id
 * @property string|null $telephone_no_patient
 * @property string|null $telephone_no_nok
 * @property int|null $age
 * @property string|null $date_of_birth
 * @property string|null $place_of_birth
 * @property int|null $ethnic_group
 * @property int|null $religion
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class Patient extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'patient';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_name', 'national_id', 'telephone_no_patient', 'telephone_no_nok', 'age', 'date_of_birth', 'place_of_birth', 'ethnic_group', 'religion', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['age', 'ethnic_group', 'religion', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['date_of_birth'], 'safe'],
            [['full_name', 'place_of_birth'], 'string', 'max' => 250],
            [['national_id', 'telephone_no_patient', 'telephone_no_nok'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'full_name' => Yii::t('app', 'Full Name'),
            'national_id' => Yii::t('app', 'National ID'),
            'telephone_no_patient' => Yii::t('app', 'Telephone No Patient'),
            'telephone_no_nok' => Yii::t('app', 'Telephone No Nok'),
            'age' => Yii::t('app', 'Age'),
            'date_of_birth' => Yii::t('app', 'Date Of Birth'),
            'place_of_birth' => Yii::t('app', 'Place Of Birth'),
            'ethnic_group' => Yii::t('app', 'Ethnic Group'),
            'religion' => Yii::t('app', 'Religion'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\queries\PatientQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\PatientQuery(get_called_class());
    }

    public static function getEthnicGroups()
    {
        return [
            1 => 'African',
            2 => 'Asian',
            3 => 'Caucasian',
            4 => 'Hispanic',
            5 => 'Other',
        ];
    }
    
    public static function getReligions()
    {
        return [
            1 => 'Christian',
            2 => 'Muslim',
            3 => 'Other',
        ];
    }

}
