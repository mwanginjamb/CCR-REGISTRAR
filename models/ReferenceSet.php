<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reference_set".
 *
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property string $source_type
 * @property string $fingerprint
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property RedcapDictionaryField[] $fields
 * @property RedcapFieldReferenceSet[] $redcapFieldReferenceSets
 * @property ReferenceOption[] $referenceOptions
 */
class ReferenceSet extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reference_set';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['label'], 'default', 'value' => null],
            [['source_type'], 'default', 'value' => 'redcap'],
            [['is_active'], 'default', 'value' => 1],
            [['name', 'fingerprint', 'created_at', 'updated_at'], 'required'],
            [['is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 191],
            [['label'], 'string', 'max' => 500],
            [['source_type'], 'string', 'max' => 50],
            [['fingerprint'], 'string', 'max' => 64],
            [['fingerprint'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'label' => 'Label',
            'source_type' => 'Source Type',
            'fingerprint' => 'Fingerprint',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Fields]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\RedcapDictionaryFieldQuery
     */
    public function getFields()
    {
        return $this->hasMany(RedcapDictionaryField::class, ['id' => 'field_id'])->viaTable('redcap_field_reference_set', ['reference_set_id' => 'id']);
    }

    /**
     * Gets query for [[RedcapFieldReferenceSets]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\RedcapFieldReferenceSetQuery
     */
    public function getRedcapFieldReferenceSets()
    {
        return $this->hasMany(RedcapFieldReferenceSet::class, ['reference_set_id' => 'id']);
    }

    /**
     * Gets query for [[ReferenceOptions]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\ReferenceOptionQuery
     */
    public function getReferenceOptions()
    {
        return $this->hasMany(ReferenceOption::class, ['reference_set_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\ReferenceSetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ReferenceSetQuery(get_called_class());
    }

}
