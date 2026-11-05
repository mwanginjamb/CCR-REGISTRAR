<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "icdo_site_morphology_rule".
 *
 * Links a topography (exact code "C50.9" or category "C50.*") to a morphology code.
 *   common  — listed first in the patient form's histology dropdown
 *   allowed — accepted combination
 * Combinations covered by neither raise a review warning on save (see Tumour::validateMorphology()).
 *
 * @property int $id
 * @property string $topography_pattern
 * @property string $morphology_code
 * @property string $rule_type
 * @property string|null $source_version
 * @property string|null $note
 *
 * @property IcdoMorphology|null $morphology
 */
class IcdoSiteMorphologyRule extends \yii\db\ActiveRecord
{
    public const RULE_TYPES = [
        'common' => 'Common',
        'allowed' => 'Allowed',
    ];

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
            [['topography_pattern', 'morphology_code', 'source_version', 'note'], 'trim'],
            ['topography_pattern', 'filter', 'filter' => 'strtoupper'],
            [['source_version', 'note'], 'default', 'value' => null],
            [['rule_type'], 'default', 'value' => 'allowed'],
            [['topography_pattern', 'morphology_code', 'rule_type'], 'required'],
            ['topography_pattern', 'match', 'pattern' => '/^C\d{2}\.(\d|\*)$/', 'message' => '{attribute} must be a topography code (C50.9) or category (C50.*).'],
            ['rule_type', 'in', 'range' => array_keys(self::RULE_TYPES)],
            ['morphology_code', 'exist', 'targetClass' => IcdoMorphology::class, 'targetAttribute' => 'code', 'message' => 'Unknown morphology code.'],
            [['note'], 'string'],
            [['topography_pattern', 'rule_type'], 'string', 'max' => 20],
            [['morphology_code'], 'string', 'max' => 10],
            [['source_version'], 'string', 'max' => 50],
            [
                'morphology_code',
                'unique',
                'targetAttribute' => ['topography_pattern', 'morphology_code', 'rule_type', 'source_version'],
                'message' => 'This rule already exists for the topography.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topography_pattern' => 'Topography',
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
