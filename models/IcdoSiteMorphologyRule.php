<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "icdo_site_morphology_rule".
 *
 * @property int $id
 * @property string $topography_pattern
 * @property string $morphology_code
 * @property string $rule_type
 * @property string|null $source_version
 * @property string|null $note
 */
class IcdoSiteMorphologyRule extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'icdo_site_morphology_rule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_version', 'note'], 'default', 'value' => null],
            [['rule_type'], 'default', 'value' => 'allowed'],
            [['topography_pattern', 'morphology_code'], 'required'],
            [['note'], 'string'],
            [['topography_pattern', 'rule_type'], 'string', 'max' => 20],
            [['morphology_code'], 'string', 'max' => 10],
            [['source_version'], 'string', 'max' => 50],
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
            'morphology_code' => 'Morphology Code',
            'rule_type' => 'Rule Type',
            'source_version' => 'Source Version',
            'note' => 'Note',
        ];
    }


    public function getMorphology()
    {
        return $this->hasOne(IcdoMorphology::class, ['code' => 'morphology_code']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\IcdoSiteMorphologyRuleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\IcdoSiteMorphologyRuleQuery(get_called_class());
    }

}
