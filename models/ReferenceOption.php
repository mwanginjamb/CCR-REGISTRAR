<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reference_option".
 *
 * @property int $id
 * @property int $reference_set_id
 * @property string $value
 * @property string $label
 * @property string|null $normalized_code
 * @property int $sort_order
 * @property int $is_active
 * @property string|null $metadata_json
 *
 * @property ReferenceSet $referenceSet
 */
class ReferenceOption extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reference_option';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['normalized_code', 'metadata_json'], 'default', 'value' => null],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['reference_set_id', 'value', 'label'], 'required'],
            [['reference_set_id', 'sort_order', 'is_active'], 'integer'],
            [['label', 'metadata_json'], 'string'],
            [['value', 'normalized_code'], 'string', 'max' => 100],
            [['reference_set_id', 'value'], 'unique', 'targetAttribute' => ['reference_set_id', 'value']],
            [['reference_set_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReferenceSet::class, 'targetAttribute' => ['reference_set_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reference_set_id' => 'Reference Set ID',
            'value' => 'Value',
            'label' => 'Label',
            'normalized_code' => 'Normalized Code',
            'sort_order' => 'Sort Order',
            'is_active' => 'Is Active',
            'metadata_json' => 'Metadata Json',
        ];
    }

    /**
     * Gets query for [[ReferenceSet]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\ReferenceSetQuery
     */
    public function getReferenceSet()
    {
        return $this->hasOne(ReferenceSet::class, ['id' => 'reference_set_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\ReferenceOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ReferenceOptionQuery(get_called_class());
    }

}
