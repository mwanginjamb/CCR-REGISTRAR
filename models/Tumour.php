<?php

namespace app\models;

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
            [['incident_date', 'basis_of_diagnosis', 'primary_site', 'laterality', 'histology', 'behaviour', 'grade', 'stage', 't', 'n', 'm', 'full_tnm', 'metastasis', 'regional_nodes_involvement', 'localized_advanced', 'localized_limited', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['patient_id'], 'required'],
            [['patient_id', 'basis_of_diagnosis', 'laterality', 'histology', 'behaviour', 'grade', 'stage', 'full_tnm', 'metastasis', 'regional_nodes_involvement', 'localized_advanced', 'localized_limited', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['incident_date'], 'safe'],
            [['primary_site', 't', 'n', 'm'], 'string', 'max' => 255],
            [['patient_id'], 'exist', 'skipOnError' => true, 'targetClass' => Patient::class, 'targetAttribute' => ['patient_id' => 'id']],
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
            'primary_site' => 'Primary Site',
            'laterality' => 'Laterality',
            'histology' => 'Histology',
            'behaviour' => 'Behaviour',
            'grade' => 'Grade',
            'stage' => 'Stage',
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
        return [
            1 => 'Benign',
            2 => 'Malignant',
            3 => 'In Situ',
            4 => 'Uncertain'
        ];
    }

    // Get Grade: Well Differentiated, Moderately Differentiated, Poorly Differentiated, Undifferentiated, T-cells
    public static function getGrade()
    {
        return [
            1 => 'Well Differentiated',
            2 => 'Moderately Differentiated',
            3 => 'Poorly Differentiated',
            4 => 'Undifferentiated',
            5 => 'T-cells'
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


}
