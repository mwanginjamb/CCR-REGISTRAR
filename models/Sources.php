<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sources".
 *
 * @property int $id
 * @property int|null $source_type
 * @property string|null $source_no
 * @property string|null $record_no
 * @property string|null $source_date
 * @property int|null $patient_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class Sources extends \yii\db\ActiveRecord
{
    public const TYPE_HOSPITAL = 1;
    public const TYPE_LAB = 2;
    public const TYPE_HOSPICE = 3;

    /**
     * REDCap has two source slots (n = 1, 2), each with its own hospital, lab and hospice
     * fields, rather than a source-type list. A source row exports to slot n as:
     *
     *  - name:   source_no as the field's code or text (null: the type has no name field);
     *            a hospital's code is its source_reference.redcap_value, or "101"
     *            (OTHERS (SPECIFY)) with the name in source_n_hosp_oth when unlisted
     *  - number: record_no, the patient's file / lab number
     *  - date:   source_date, shared by the slot as src_date_n
     *
     * Field names are as in the dictionary, including its irregular slot-2 names
     * (source_2_lab_2, src_date_3).
     */
    public const REDCAP_FIELDS = [
        self::TYPE_HOSPITAL => [
            'name' => ['source_1_hosp', 'source_2_hosp'],
            'number' => ['hosp_no_1', 'hosp_no_2'],
        ],
        self::TYPE_LAB => [
            'name' => ['source_1_lab', 'source_2_lab_2'],
            'number' => ['lab_no_1', 'lab_no_2'],
        ],
        self::TYPE_HOSPICE => [
            'name' => null,
            'number' => ['hospice_no_1', 'hospice_no_2'],
        ],
    ];

    public const REDCAP_DATE_FIELDS = ['src_date_1', 'src_date_3'];


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

            // explicit safe rule for all attributes
           [['source_type', 'source_no', 'record_no', 'source_date', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
            
            [['source_type', 'source_no', 'record_no', 'source_date', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['source_type', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['source_date'], 'safe'],
            [['source_no'], 'string', 'max' => 255],
            [['record_no'], 'trim'],
            [['record_no'], 'string', 'max' => 50],
            ['source_type', 'in', 'range' => array_keys(self::getSourceTypeOptions()), 'message' => '{attribute} must be selected from the list.'],
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
            'record_no' => 'Record No',
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

    // Source types on the CRF (items 23-25); see REDCAP_FIELDS for how each exports
    public static function getSourceTypeOptions()
    {
        return [
            self::TYPE_HOSPITAL => 'Hospital',
            self::TYPE_LAB => 'Lab',
            self::TYPE_HOSPICE => 'Hospice',
        ];
    }

}
