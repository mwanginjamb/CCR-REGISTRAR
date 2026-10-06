<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "redcap_field_reference_set".
 *
 * @property int $field_id
 * @property int $reference_set_id
 *
 * @property RedcapDictionaryField $field
 * @property ReferenceSet $referenceSet
 */
class Redcap extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'redcap_field_reference_set';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['field_id', 'reference_set_id'], 'required'],
            [['field_id', 'reference_set_id'], 'integer'],
            [['field_id', 'reference_set_id'], 'unique', 'targetAttribute' => ['field_id', 'reference_set_id']],
            [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => RedcapDictionaryField::class, 'targetAttribute' => ['field_id' => 'id']],
            [['reference_set_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReferenceSet::class, 'targetAttribute' => ['reference_set_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'field_id' => 'Field ID',
            'reference_set_id' => 'Reference Set ID',
        ];
    }

    /**
     * Gets query for [[Field]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\RedcapDictionaryFieldQuery
     */
    public function getField()
    {
        return $this->hasOne(RedcapDictionaryField::class, ['id' => 'field_id']);
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
     * @return \app\models\query\RedcapQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\RedcapQuery(get_called_class());
    }

}
