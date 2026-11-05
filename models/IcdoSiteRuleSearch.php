<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IcdoSiteRuleSearch represents the model behind the search form of `app\models\IcdoSiteRule`.
 */
class IcdoSiteRuleSearch extends IcdoSiteRule
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['laterality_required', 'laterality_allowed', 'tnm_allowed', 'grade_allowed'], 'integer'],
            [['topography_pattern'], 'safe'],
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
    public function behaviors()
    {
        return [];
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
        $query = IcdoSiteRule::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 25],
            'sort' => ['defaultOrder' => ['topography_pattern' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'laterality_required' => $this->laterality_required,
            'laterality_allowed' => $this->laterality_allowed,
            'tnm_allowed' => $this->tnm_allowed,
            'grade_allowed' => $this->grade_allowed,
        ]);

        $query->andFilterWhere(['like', 'topography_pattern', $this->topography_pattern]);

        return $dataProvider;
    }
}
