<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "icdo_site_rule".
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topography_pattern', 'laterality_required', 'laterality_allowed', 'tnm_allowed', 'grade_allowed', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['laterality_required', 'laterality_allowed', 'tnm_allowed', 'grade_allowed', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['topography_pattern'], 'string', 'max' => 50],
            [['topography_pattern'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topography_pattern' => 'Topography Pattern',
            'laterality_required' => 'Laterality Required',
            'laterality_allowed' => 'Laterality Allowed',
            'tnm_allowed' => 'Tnm Allowed',
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
