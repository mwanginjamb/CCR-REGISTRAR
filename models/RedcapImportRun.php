<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "redcap_import_run".
 *
 * @property int $id
 * @property string $source_name
 * @property string $source_hash
 * @property string $status
 * @property int $row_count
 * @property int $field_count
 * @property int $option_count
 * @property int $warning_count
 * @property string|null $error_message
 * @property string $started_at
 * @property string|null $completed_at
 * @property int|null $created_by
 *
 * @property RedcapDictionaryField[] $redcapDictionaryFields
 */
class RedcapImportRun extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'redcap_import_run';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['error_message', 'completed_at', 'created_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['warning_count'], 'default', 'value' => 0],
            [['source_name', 'source_hash', 'started_at'], 'required'],
            [['row_count', 'field_count', 'option_count', 'warning_count', 'created_by'], 'integer'],
            [['error_message'], 'string'],
            [['started_at', 'completed_at'], 'safe'],
            [['source_name'], 'string', 'max' => 255],
            [['source_hash'], 'string', 'max' => 64],
            [['status'], 'string', 'max' => 20],
            [['source_hash'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_name' => 'Source Name',
            'source_hash' => 'Source Hash',
            'status' => 'Status',
            'row_count' => 'Row Count',
            'field_count' => 'Field Count',
            'option_count' => 'Option Count',
            'warning_count' => 'Warning Count',
            'error_message' => 'Error Message',
            'started_at' => 'Started At',
            'completed_at' => 'Completed At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[RedcapDictionaryFields]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\RedcapDictionaryFieldQuery
     */
    public function getRedcapDictionaryFields()
    {
        return $this->hasMany(RedcapDictionaryField::class, ['import_run_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\RedcapImportRunQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\RedcapImportRunQuery(get_called_class());
    }

}
