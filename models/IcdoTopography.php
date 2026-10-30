<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

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

    /**
     * Primary site options: one "NOS" (.9) code per category, e.g. C00.9.
     *
     * Some ICD-O categories have no .9 code (C14, C21, C22, C30, C38, C42, C48, C76);
     * their specific codes are listed directly so those sites stay selectable.
     *
     * @return array code => description
     */
    public static function primarySiteOptions(): array
    {
        $categoriesWithNos = self::find()
            ->select(new Expression('LEFT([[code]], 3)'))
            ->where(['is_active' => 1])
            ->andWhere(['like', 'code', '%.9', false]);

        return ArrayHelper::map(
            self::find()
                ->select(['code', 'description'])
                ->where(['is_active' => 1])
                ->andWhere([
                    'or',
                    ['like', 'code', '%.9', false],
                    ['not in', new Expression('LEFT([[code]], 3)'), $categoriesWithNos],
                ])
                ->orderBy(['code' => SORT_ASC])
                ->asArray()
                ->all(),
            'code',
            'description'
        );
    }

    /**
     * Sub-sites of a primary site category, e.g. C00.9 -> C00.0 ... C00.8.
     * Only a .9 primary site has sub-sites; a specific code already is one.
     *
     * @return array code => description
     */
    public static function subsiteOptions(?string $primarySite): array
    {
        $primarySite = strtoupper(trim((string) $primarySite));

        if (!preg_match('/^(C\d{2})\.9$/', $primarySite, $matches)) {
            return [];
        }

        return ArrayHelper::map(
            self::find()
                ->select(['code', 'description'])
                ->where(['is_active' => 1])
                ->andWhere(['like', 'code', $matches[1] . '.%', false])
                ->andWhere(['<>', 'code', $primarySite])
                ->orderBy(['code' => SORT_ASC])
                ->asArray()
                ->all(),
            'code',
            'description'
        );
    }

}
