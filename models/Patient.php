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
 * @property string|null $geo_lat
 * @property string|null $geo_lng
 * @property string|null $geo_accuracy
 * @property string|null $geo_captured
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

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
            ],
        ];
    }

    // calculate age base on date of birth on before save event
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->date_of_birth) {
                $this->age = date_diff(date_create($this->date_of_birth), date_create('now'))->y;
            }
            return true;
        }
        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            // Allow mass assignment by assigning an explicit safe rule for all attributes
           // [['full_name', 'national_id', 'telephone_no_patient', 'telephone_no_nok', 'age', 'date_of_birth', 'place_of_birth', 'ethnic_group', 'religion', 'created_at', 'updated_at', 'created_by', 'updated_by', 'geo_lat', 'geo_lng', 'geo_accuracy', 'geo_captured'], 'safe'],
                    


            [['full_name', 'national_id', 'telephone_no_patient', 'telephone_no_nok', 'age', 'date_of_birth', 'place_of_birth', 'ethnic_group', 'religion', 'created_at', 'updated_at', 'created_by', 'updated_by', 'geo_lat', 'geo_lng', 'geo_accuracy', 'geo_captured'], 'default', 'value' => null],
            [['age', 'ethnic_group', 'religion', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['date_of_birth'], 'safe'],
            [['full_name', 'place_of_birth'], 'string', 'max' => 250],
            [['national_id', 'telephone_no_patient', 'telephone_no_nok'], 'string', 'max' => 30],
            [['geo_lat', 'geo_lng'], 'number', 'min' => -90, 'max' => 90],
            [['geo_accuracy'], 'number', 'min' => 0],
            [['geo_captured'], 'safe'],
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
            'geo_lat' => Yii::t('app', 'Geo Lat'),
            'geo_lng' => Yii::t('app', 'Geo Lng'),
            'geo_accuracy' => Yii::t('app', 'Geo Accuracy'),
            'geo_captured' => Yii::t('app', 'Geo Captured'),
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

    // Kenya Ethnic Groups - 42 ethnic groups in Kenya
    public static function getEthnicGroups()
    {
        return [
        1 => 'Luo',
        2 => 'Kikuyu',
        3 => 'Kamba',
        4 => 'Luhya',
        5 => 'Kalenjin',
        6 => 'Meru',
        7 => 'Samburu',
        8 => 'Maasai',
        9 => 'Somali',
        10 => 'Taita',
        11 => 'Bajuni',
        12 => 'Giriama',
        13 => 'Rendille',
        14 => 'Teso',
        15 => 'Kuria',
        16 => 'Borana',
        17 => 'Digo',
        18 => 'Gusii',
        19 => 'Tachoni',
        20 => 'Others',
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
