<?php

namespace app\models;

use app\services\Lookup;
use Yii;

/**
 * This is the model class for table "treatment".
 *
 * @property int $id
 * @property string|null $treatment REDCap field name of the therapy (see TYPES)
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
     * First-course treatments on the CRF (item 21). Keys are the REDCap field names that
     * hold each therapy's status, so a stored row maps straight onto its export field;
     * labels are fallbacks for when the dictionary has not been imported.
     */
    public const TYPES = [
        'surgery' => 'Surgery',
        'chemotherapy' => 'Chemotherapy',
        'immuno' => 'Immuno',
        'radiotherapy' => 'Radiotherapy',
        'hormone_therapy' => 'Hormone therapy',
        'other_therapy' => 'Other therapy',
    ];

    /**
     * REDCap records "other therapy" as free text with no status field, so its status
     * uses the list the other non-surgical therapies share (1 No, 2 Yes, 9 Unknown).
     */
    private const SHARED_STATUS_FIELD = 'chemotherapy';


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

            // explicit safe rule for all attributes
            [['treatment', 'treatment_status', 'treatment_date', 'patient_id', 'concurrent_illness', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
            
            [['treatment', 'treatment_status', 'treatment_date', 'patient_id', 'concurrent_illness', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['treatment_status', 'patient_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['treatment_date'], 'safe'],
            [['concurrent_illness'], 'string'],
            [['treatment'], 'string', 'max' => 255],
            ['treatment', 'in', 'range' => array_keys(self::TYPES), 'message' => '{attribute} must be selected from the list.'],
            ['treatment_status', 'validateTreatmentStatus'],
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

    // Status must come from the selected therapy's own dictionary list
    public function validateTreatmentStatus($attribute)
    {
        if (!isset(self::getTreatmentStatus($this->treatment)[$this->$attribute])) {
            $this->addError($attribute, '{attribute} must be selected from the list.');
        }
    }

    // Treatment types, labelled from the REDCap dictionary: surgery, chemotherapy, immuno, ...
    public static function getTreatment()
    {
        $types = [];
        foreach (self::TYPES as $field => $fallback) {
            $types[$field] = Lookup::fieldLabel($field) ?? $fallback;
        }
        return $types;
    }

    // Get Treatment Status from the REDCap dictionary for one therapy.
    // Each therapy has its own status field: surgery codes Unknown as 99, the others as 9,
    // so the stored code is exactly what REDCap expects for that therapy.
    public static function getTreatmentStatus(?string $treatment = null)
    {
        $field = isset(self::TYPES[$treatment]) && $treatment !== 'other_therapy'
            ? $treatment
            : self::SHARED_STATUS_FIELD;

        return Lookup::fieldOptions($field);
    }

    /**
     * Status list per treatment type, for the form to swap a row's status options
     * when its treatment changes. Lists of [code, label] pairs keep dictionary order in JSON.
     *
     * @return array<string, array<int, array{0: string, 1: string}>>
     */
    public static function statusOptionsByType(): array
    {
        $byType = [];
        foreach (array_keys(self::TYPES) as $type) {
            foreach (self::getTreatmentStatus($type) as $code => $label) {
                $byType[$type][] = [(string) $code, $label];
            }
        }
        return $byType;
    }

}
