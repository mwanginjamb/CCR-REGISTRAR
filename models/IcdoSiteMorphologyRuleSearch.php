<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IcdoSiteMorphologyRuleSearch represents the model behind the search form of `app\models\IcdoSiteMorphologyRule`.
 */
class IcdoSiteMorphologyRuleSearch extends IcdoSiteMorphologyRule
{
    /** Filter on the linked morphology's description */
    public $morphologyDescription;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topography_pattern', 'morphology_code', 'rule_type', 'source_version', 'note', 'morphologyDescription'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + ['morphologyDescription' => 'Morphology'];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = IcdoSiteMorphologyRule::find()
            ->alias('r')
            ->joinWith(['morphology m']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 25],
            'sort' => [
                'defaultOrder' => ['topography_pattern' => SORT_ASC, 'morphology_code' => SORT_ASC],
                'attributes' => [
                    'topography_pattern' => ['asc' => ['r.topography_pattern' => SORT_ASC], 'desc' => ['r.topography_pattern' => SORT_DESC]],
                    'morphology_code' => ['asc' => ['r.morphology_code' => SORT_ASC], 'desc' => ['r.morphology_code' => SORT_DESC]],
                    'morphologyDescription' => ['asc' => ['m.description' => SORT_ASC], 'desc' => ['m.description' => SORT_DESC]],
                    'rule_type' => ['asc' => ['r.rule_type' => SORT_ASC], 'desc' => ['r.rule_type' => SORT_DESC]],
                    // source_version exists on both tables, so qualify it
                    'source_version' => ['asc' => ['r.source_version' => SORT_ASC], 'desc' => ['r.source_version' => SORT_DESC]],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['r.rule_type' => $this->rule_type]);

        $query->andFilterWhere(['like', 'r.topography_pattern', $this->topography_pattern])
            ->andFilterWhere(['like', 'r.morphology_code', $this->morphology_code])
            ->andFilterWhere(['like', 'm.description', $this->morphologyDescription])
            ->andFilterWhere(['like', 'r.source_version', $this->source_version])
            ->andFilterWhere(['like', 'r.note', $this->note]);

        return $dataProvider;
    }
}
