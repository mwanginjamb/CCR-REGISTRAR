<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "redcap_dictionary_field".
 *
 * @property int $id
 * @property int $import_run_id
 * @property string $field_name
 * @property string|null $form_name
 * @property string|null $section_header
 * @property string $field_type
 * @property string|null $field_label
 * @property string|null $choices_text
 * @property string|null $field_note
 * @property string|null $validation_type
 * @property string|null $validation_min
 * @property string|null $validation_max
 * @property int $is_identifier
 * @property string|null $branching_logic
 * @property int $is_required
 * @property string|null $field_annotation
 * @property int $row_number
 * @property string $row_hash
 * @property string $raw_row_json
 *
 * @property RedcapImportRun $importRun
 * @property RedcapFieldReferenceSet[] $redcapFieldReferenceSets
 * @property ReferenceSet[] $referenceSets
 */
class RedcapDictionaryField extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'redcap_dictionary_field';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['form_name', 'section_header', 'field_label', 'choices_text', 'field_note', 'validation_type', 'validation_min', 'validation_max', 'branching_logic', 'field_annotation'], 'default', 'value' => null],
            [['is_required'], 'default', 'value' => 0],
            [['import_run_id', 'field_name', 'field_type', 'row_number', 'row_hash', 'raw_row_json'], 'required'],
            [['import_run_id', 'is_identifier', 'is_required', 'row_number'], 'integer'],
            [['section_header', 'field_label', 'choices_text', 'field_note', 'branching_logic', 'field_annotation', 'raw_row_json'], 'string'],
            [['field_name', 'form_name'], 'string', 'max' => 191],
            [['field_type'], 'string', 'max' => 40],
            [['validation_type', 'validation_min', 'validation_max'], 'string', 'max' => 100],
            [['row_hash'], 'string', 'max' => 64],
            [['import_run_id', 'field_name'], 'unique', 'targetAttribute' => ['import_run_id', 'field_name']],
            [['import_run_id'], 'exist', 'skipOnError' => true, 'targetClass' => RedcapImportRun::class, 'targetAttribute' => ['import_run_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'import_run_id' => 'Import Run ID',
            'field_name' => 'Field Name',
            'form_name' => 'Form Name',
            'section_header' => 'Section Header',
            'field_type' => 'Field Type',
            'field_label' => 'Field Label',
            'choices_text' => 'Choices Text',
            'field_note' => 'Field Note',
            'validation_type' => 'Validation Type',
            'validation_min' => 'Validation Min',
            'validation_max' => 'Validation Max',
            'is_identifier' => 'Is Identifier',
            'branching_logic' => 'Branching Logic',
            'is_required' => 'Is Required',
            'field_annotation' => 'Field Annotation',
            'row_number' => 'Row Number',
            'row_hash' => 'Row Hash',
            'raw_row_json' => 'Raw Row Json',
        ];
    }

    /**
     * Gets query for [[ImportRun]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\RedcapImportRunQuery
     */
    public function getImportRun()
    {
        return $this->hasOne(RedcapImportRun::class, ['id' => 'import_run_id']);
    }

    /**
     * Gets query for [[RedcapFieldReferenceSets]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\RedcapFieldReferenceSetQuery
     */
    public function getRedcapFieldReferenceSets()
    {
        return $this->hasMany(RedcapFieldReferenceSet::class, ['field_id' => 'id']);
    }

    /**
     * Gets query for [[ReferenceSets]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\ReferenceSetQuery
     */
    public function getReferenceSets()
    {
        return $this->hasMany(ReferenceSet::class, ['id' => 'reference_set_id'])->viaTable('redcap_field_reference_set', ['field_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\RedcapDictionaryFieldQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\RedcapDictionaryFieldQuery(get_called_class());
    }

}
