<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "icdo_topography".
 *
 * @property int $id
 * @property string $redcap_value
 * @property string $code
 * @property string $description
 * @property string|null $source_version
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class IcdoTopography extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'icdo_topography';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_version'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => 1],
            [['redcap_value', 'code', 'description', 'created_at', 'updated_at'], 'required'],
            [['description'], 'string'],
            [['is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['redcap_value'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 10],
            [['source_version'], 'string', 'max' => 50],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'redcap_value' => 'Redcap Value',
            'code' => 'Code',
            'description' => 'Description',
            'source_version' => 'Source Version',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\IcdoTopographyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\IcdoTopographyQuery(get_called_class());
    }

}
