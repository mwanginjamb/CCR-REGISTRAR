<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "icdo_site_rule".
 *
 * One rule per topography category (e.g. "C50.*"), read by the patient form and
 * Tumour::validateLaterality() to decide whether laterality applies to a site.
 *
 * @property int $id
 * @property string|null $topography_pattern
 * @property int|null $laterality_required
 * @property int|null $laterality_allowed
 * @property int|null $tnm_allowed
 * @property int|null $grade_allowed
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class IcdoSiteRule extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'icdo_site_rule';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['topography_pattern', 'trim'],
            ['topography_pattern', 'filter', 'filter' => 'strtoupper'],
            ['topography_pattern', 'required'],
            ['topography_pattern', 'match', 'pattern' => '/^C\d{2}\.\*$/', 'message' => '{attribute} must be a topography category such as C50.*'],
            [['laterality_required', 'laterality_allowed'], 'default', 'value' => 0],
            [['tnm_allowed', 'grade_allowed'], 'default', 'value' => null],
            [['laterality_required', 'laterality_allowed', 'tnm_allowed', 'grade_allowed'], 'boolean'],
            ['laterality_required', 'validateLateralityRequired'],
            [['topography_pattern'], 'string', 'max' => 50],
            [['topography_pattern'], 'unique'],
        ];
    }

    // Laterality cannot be required for a site where it is not allowed
    public function validateLateralityRequired($attribute)
    {
        if ($this->laterality_required && !$this->laterality_allowed) {
            $this->addError($attribute, 'Laterality can only be required when it is allowed.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topography_pattern' => 'Topography Category',
            'laterality_required' => 'Laterality Required',
            'laterality_allowed' => 'Laterality Allowed',
            'tnm_allowed' => 'TNM Allowed',
            'grade_allowed' => 'Grade Allowed',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\IcdoSiteRuleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\IcdoSiteRuleQuery(get_called_class());
    }

}
