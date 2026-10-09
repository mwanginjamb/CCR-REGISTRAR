<?php

namespace app\models;

use app\services\Lookup;
use Yii;

/**
 * This is the model class for table "tumour".
 *
 * @property int $id
 * @property int $patient_id
 * @property string|null $incident_date
 * @property int|null $basis_of_diagnosis
 * @property string|null $primary_site
 * @property int|null $laterality
 * @property int|null $histology
 * @property int|null $behaviour
 * @property int|null $grade
 * @property int|null $stage
 * @property string|null $t
 * @property string|null $n
 * @property string|null $m
 * @property int|null $full_tnm
 * @property int|null $metastasis
 * @property int|null $regional_nodes_involvement
 * @property int|null $localized_advanced
 * @property int|null $localized_limited
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Patient $patient
 */
class Tumour extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tumour';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            // explicit safe rule for all attributes
            [['basis_of_diagnosis', 'primary_site', 'histology', 'behaviour', 'grade', 'stage', 'metastasis', 'regional_nodes_involvement', 'localized_advanced', 'localized_limited', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],

            [['incident_date', 'basis_of_diagnosis', 'primary_site', 'histology', 'behaviour', 'grade', 'stage', 'metastasis', 'regional_nodes_involvement', 'localized_advanced', 'localized_limited', 'created_at', 'updated_at', 'created_by', 'updated_by', 'full_tnm'], 'default', 'value' => null],
            [['patient_id', 'incident_date', 'basis_of_diagnosis', 'primary_site'], 'required'],
            [['patient_id', 'basis_of_diagnosis', 'laterality', 'histology', 'behaviour', 'grade', 'stage', 'full_tnm', 'metastasis', 'regional_nodes_involvement', 'localized_advanced', 'localized_limited', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['incident_date'], 'safe'],
            [['primary_site', 't', 'n', 'm'], 'string', 'max' => 255],
            [['patient_id'], 'exist', 'skipOnError' => true, 'targetClass' => Patient::class, 'targetAttribute' => ['patient_id' => 'id']],
            ['incident_date', 'date', 'format' => 'php:Y-m-d'],
            [
                'incident_date',
                'compare',
                'compareValue' => date('Y-m-d'),
                'operator' => '<=',
                'type' => 'date',
                'message' => 'Incident date cannot be in the future.'
            ],

            ['laterality', 'validateLaterality'],
            ['histology', 'validateMorphology'],
            ['full_tnm', 'validateEssentialTNMFields'],

            [
                ['t', 'n', 'm'],
                'required',
                'when' => function ($model) {
                    return (int) $model->full_tnm === 1;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#tumour-full_tnm').val() == '1';
                }",
            ],

            [
                ['t', 'n', 'm'],
                'required',
                'when' => fn($model) =>
                    (int) $model->full_tnm === 1
            ],

            ['grade', 'validateGradeApplicability'],
            [
                'grade',
                'required',
                'when' => function ($model) {
                    return (int) $model->behaviour === 3; // malignant
                }
            ]

            // ['t', 'validateCompleteTnm'],
            // ['n', 'validateCompleteTnm'],
            // ['m', 'validateCompleteTnm'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'patient_id' => 'Patient ID',
            'incident_date' => 'Incident Date',
            'basis_of_diagnosis' => 'Basis Of Diagnosis',
            'primary_site' => 'Primary Site (Topography Code)',
            'laterality' => 'Laterality',
            'histology' => 'Histology (Morphology Code)',
            'behaviour' => 'Behaviour (Behavior Code)',
            'grade' => 'Grade (Grade Code)',
            'stage' => 'Stage (Stage Code)',
            't' => 'T',
            'n' => 'N',
            'm' => 'M',
            'full_tnm' => 'Full Tnm',
            'metastasis' => 'Metastasis',
            'regional_nodes_involvement' => 'Regional Nodes Involvement',
            'localized_advanced' => 'Localized Advanced',
            'localized_limited' => 'Localized Limited',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Patient]].
     *
     * @return \yii\db\ActiveQuery|\app\models\queries\PatientQuery
     */
    public function getPatient()
    {
        return $this->hasOne(Patient::class, ['id' => 'patient_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\queries\TumourQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\TumourQuery(get_called_class());
    }

    // Validate Laterality based on Topography Code
    public function validateLaterality($attribute, $params)
    {
        if (empty($this->primary_site)) {
            return;
        }
        $pattern = substr($this->primary_site, 0, 3) . '.*';

        $rule = IcdoSiteRule::find()
            ->where(['topography_pattern' => $pattern])
            ->one();

        if (!$rule) {
            return;
        }

        // Not applicable site check
        if (!$rule->laterality_allowed && $this->laterality != 4) {
            $this->addError($attribute, 'Laterality is not applicable for the selected primary site.');
        }

        // Required site check
        if ($rule->laterality_required && empty($this->laterality)) {
            $this->addError($attribute, 'Laterality is required for this topography code.');
        }


    }

    // Validate Morphology based on Topography Code
    public function validateMorphology($attribute)
    {
        if (
            empty($this->primary_site) ||
            empty($this->histology)
        ) {
            return;
        }

        $pattern = substr(
            $this->primary_site,
            0,
            3
        ) . '.*';

        $allowed = IcdoSiteMorphologyRule::find()
            ->where([
                'morphology_code' => $this->histology,
                'rule_type' => 'allowed'
            ])
            ->andWhere([
                'or',
                ['topography_pattern' => $this->primary_site],
                ['topography_pattern' => $pattern]
            ])
            ->exists();

        if (!$allowed) {

            $this->addError(
                $attribute,
                'Selected histology is not valid for the selected primary site / Topography.'
            );
        }
    }

    // Validate Ensential TNM Fields based on full TNM value
    public function validateEssentialTNMFields($attribute, $params)
    {
        if ((int) $this->full_tnm !== 1) {
            return;
        }

        $fields = [
            $this->metastasis,
            $this->regional_nodes_involvement,
            $this->localized_advanced,
            $this->localized_limited
        ];

        foreach ($fields as $field) {
            if (!empty($field)) {
                $this->addError(
                    $attribute,
                    'Essential TNM fields should not be used when Full TNM is available.'
                );
                break;
            }
        }
    }

    // Validate TNM fields completeness based on full TNM value
    public function validateCompleteTnm($attribute, $params)
    {
        if ((int) $this->full_tnm !== 1) {
            return;
        }

        $values = [
            $this->t,
            $this->n,
            $this->m
        ];

        $filled = count(array_filter($values));

        if ($filled > 0 && $filled < 3) {
            $this->addError(
                $attribute,
                'All TNM fields (T, N, M) must be filled when Full TNM is available.'
            );

        }
    }

    // Grade Applicability Validity

    public function validateGradeApplicability($attribute)
    {
        if (
            in_array(
                (int) $this->behaviour,
                [0, 1], // Benign and Uncertain behaviour codes
                true
            )
            &&
            !empty($this->grade)
        ) {

            $this->addError(
                $attribute,
                'Grade is not applicable for the selected behaviour.'
            );
        }
    }


    public static function getBasisOfDiagnosis()
    {
        return [
            1 => 'Clinical',
            2 => 'Histopathological',
            3 => 'Imaging',
            4 => 'Other'
        ];
    }

    // Get Possible Primary Sites
    public static function getPrimarySites()
    {
        return [
            1 => 'Breast',
            2 => 'Lung',
            3 => 'Prostate',
            4 => 'Colorectal',
            5 => 'Other'
        ];
    }

    // Get Laterality: Right, Left, Bilateral, Not Applicable,Unk
    public static function getLaterality()
    {
        return [
            1 => 'Right',
            2 => 'Left',
            3 => 'Bilateral',
            4 => 'Not Applicable',
            5 => 'Unknown'

        ];
    }

    // Get Histology: Carcinoma, Sarcoma, Lymphoma, Melanoma, Other
    public static function getHistology()
    {
        return [
            1 => 'Carcinoma',
            2 => 'Sarcoma',
            3 => 'Lymphoma',
            4 => 'Melanoma',
            5 => 'Other'
        ];
    }

    // Get Tumor Behavior: Benign, Malignant, In Situ, Uncertain
    public static function getBehaviour()
    {
        return Lookup::fieldOptions('behaviour');
    }

    // Get Grade: Well Differentiated, Moderately Differentiated, Poorly Differentiated, Undifferentiated, T-cells
    public static function getGrade()
    {
        return [
            1 => 'Well Differentiated',
            2 => 'Moderately Differentiated',
            3 => 'Poorly Differentiated',
            4 => 'Undifferentiated',
            5 => 'T-cells',
            6 => 'metastatic'
        ];
    }

    // Get Stage: In Situ, Stage I, Stage II, Stage III, Stage IV, Stage Unknown
    public static function getStage()
    {
        return [
            1 => 'In Situ',
            2 => 'Stage I',
            3 => 'Stage II',
            4 => 'Stage III',
            5 => 'Stage IV',
            6 => 'Stage Unknown'
        ];
    }

    // Get Metastasis: M- (absence of  regional/ distance metastasis), M+ (presence of regional/ distance metastasis)
    public static function getMetastasis()
    {
        return [
            1 => 'M-',
            2 => 'M+'
        ];
    }

    // Get Regional Nodes Involvement: N- (absence of regional nodes), N+ (presence of regional nodes)
    public static function getRegionalNodesInvolvement()
    {
        return [
            1 => 'N-',
            2 => 'N+'
        ];
    }

    // Get Localized Advanced: T3 Localized, T4 Advanced
    public static function getLocalizedAdvanced()
    {
        return [
            1 => 'T3 Localized',
            2 => 'T4 Advanced'
        ];
    }

    // Get Localized Limited: T1 Localized, T2 Limited
    public static function getLocalizedLimited()
    {
        return [
            1 => 'T1 Localized',
            2 => 'T2 Limited'
        ];
    }



    // before validate event

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if (!empty($this->histology)) {

            $morphology = IcdoMorphology::findOne([
                'code' => $this->histology
            ]);

            if ($morphology) {

                $this->behaviour = $morphology->behaviour;
            }
        }

        return true;
    }

}
