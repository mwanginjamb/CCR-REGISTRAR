<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SourceReferenceSearch represents the model behind the search form of `app\models\SourceReference`.
 */
class SourceReferenceSearch extends SourceReference
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_type', 'is_active'], 'integer'],
            [['source_no', 'name', 'redcap_value'], 'safe'],
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
        // no timestamp/blameable behaviours on a search model
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
        $query = SourceReference::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 25],
            'sort' => [
                'defaultOrder' => ['source_type' => SORT_ASC, 'source_no' => SORT_ASC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'source_type' => $this->source_type,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['like', 'source_no', $this->source_no])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'redcap_value', $this->redcap_value]);

        return $dataProvider;
    }
}
